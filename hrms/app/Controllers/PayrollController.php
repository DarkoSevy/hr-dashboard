<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Payroll preparation: generate monthly entries from employee data with
 * Rwandan PAYE brackets and RSSB contributions, allow adjustments, and
 * export CSV for Finance / QuickBooks.
 */
class PayrollController extends Controller
{
    /** Monthly PAYE brackets (RWF): 0% to 60k, 10% to 100k, 20% to 200k, 30% above. */
    private const PAYE_BRACKETS = [
        [0,      60000,  0.00],
        [60000,  100000, 0.10],
        [100000, 200000, 0.20],
        [200000, PHP_FLOAT_MAX, 0.30],
    ];

    public function index(): void
    {
        Auth::require('payroll.view');
        $periods = Database::fetchAll(
            'SELECT pp.*, COUNT(pe.id) AS entries, COALESCE(SUM(pe.net_salary),0) AS total_net
             FROM payroll_periods pp
             LEFT JOIN payroll_entries pe ON pe.period_id = pp.id
             GROUP BY pp.id ORDER BY pp.start_date DESC'
        );
        $this->view('payroll/index', ['periods' => $periods]);
    }

    public function show(int $id): void
    {
        Auth::require('payroll.view');
        $period = Database::fetch('SELECT * FROM payroll_periods WHERE id = ?', [$id]);
        if (!$period) {
            redirect('payroll');
        }
        $entries = Database::fetchAll(
            "SELECT pe.*, CONCAT(e.first_name,' ',e.last_name) AS employee_name,
                    e.employee_no, e.rssb_number, e.bank_name, e.bank_account
             FROM payroll_entries pe JOIN employees e ON e.id = pe.employee_id
             WHERE pe.period_id = ? ORDER BY e.last_name", [$id]
        );
        $this->view('payroll/show', ['period' => $period, 'entries' => $entries]);
    }

    /** Create a period for the given month and generate entries for active staff. */
    public function generate(): void
    {
        Auth::require('payroll.manage');
        $month = preg_match('/^\d{4}-\d{2}$/', $_POST['month'] ?? '')
            ? $_POST['month'] : date('Y-m');
        $existing = Database::fetch('SELECT id FROM payroll_periods WHERE name = ?', [$month]);
        if ($existing) {
            flash('error', "Payroll period {$month} already exists.");
            redirect('payroll');
        }
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $periodId = Database::insert('payroll_periods', [
            'name' => $month, 'start_date' => $start, 'end_date' => $end, 'status' => 'processing',
        ]);

        $rssbEmp = (float) (Database::scalar("SELECT value FROM settings WHERE `key`='rssb_employee_rate'") ?: 0.06);
        $rssbEr  = (float) (Database::scalar("SELECT value FROM settings WHERE `key`='rssb_employer_rate'") ?: 0.08);

        $employees = Database::fetchAll("SELECT id, basic_salary FROM employees WHERE status='active'");
        foreach ($employees as $emp) {
            $basic = (float) $emp['basic_salary'];
            $gross = $basic; // allowances are added per-entry afterwards
            $rssb = round($basic * $rssbEmp, 2);
            $paye = $this->paye($gross);
            Database::insert('payroll_entries', [
                'period_id' => $periodId,
                'employee_id' => $emp['id'],
                'basic_salary' => $basic,
                'gross_salary' => $gross,
                'rssb_employee' => $rssb,
                'rssb_employer' => round($basic * $rssbEr, 2),
                'paye' => $paye,
                'net_salary' => round($gross - $rssb - $paye, 2),
            ]);
        }
        Audit::log('create', 'payroll_periods', (string) $periodId, null, ['month' => $month]);
        flash('success', "Payroll {$month} generated for " . count($employees) . ' employees.');
        redirect('payroll/' . $periodId);
    }

    /** Update one entry's allowances/deductions and recompute totals. */
    public function update(int $id): void
    {
        Auth::require('payroll.manage');
        $entry = Database::fetch(
            'SELECT pe.* FROM payroll_entries pe
             JOIN payroll_periods pp ON pp.id = pe.period_id
             WHERE pe.id = ? AND pp.status IN ("open","processing")', [$id]
        );
        if (!$entry) {
            flash('error', 'Entry not found or period is locked.');
            redirect('payroll');
        }
        $numeric = fn(string $k): float => max(0, (float) ($_POST[$k] ?? $entry[$k]));
        $data = [
            'transport_allowance' => $numeric('transport_allowance'),
            'telephone_allowance' => $numeric('telephone_allowance'),
            'per_diem'   => $numeric('per_diem'),
            'overtime'   => $numeric('overtime'),
            'bonus'      => $numeric('bonus'),
            'commission' => $numeric('commission'),
            'loan_deduction'    => $numeric('loan_deduction'),
            'advance_deduction' => $numeric('advance_deduction'),
            'other_deduction'   => $numeric('other_deduction'),
        ];
        $gross = (float) $entry['basic_salary'] + $data['transport_allowance']
            + $data['telephone_allowance'] + $data['per_diem'] + $data['overtime']
            + $data['bonus'] + $data['commission'];
        $rssbRate = (float) (Database::scalar("SELECT value FROM settings WHERE `key`='rssb_employee_rate'") ?: 0.06);
        $rssb = round((float) $entry['basic_salary'] * $rssbRate, 2);
        $paye = $this->paye($gross);
        $data += [
            'gross_salary' => round($gross, 2),
            'rssb_employee' => $rssb,
            'paye' => $paye,
            'net_salary' => round($gross - $rssb - $paye
                - $data['loan_deduction'] - $data['advance_deduction'] - $data['other_deduction'], 2),
        ];
        Database::update('payroll_entries', $data, $id);
        Audit::log('update', 'payroll_entries', (string) $id, $entry, $data);
        flash('success', 'Payroll entry updated.');
        redirect('payroll/' . $entry['period_id']);
    }

    public function lock(int $id): void
    {
        Auth::require('payroll.manage');
        Database::update('payroll_periods', ['status' => 'locked'], $id);
        Audit::log('update', 'payroll_periods', (string) $id, null, ['status' => 'locked']);
        flash('success', 'Payroll period locked.');
        redirect('payroll/' . $id);
    }

    /** CSV export (QuickBooks/Excel friendly). */
    public function export(int $id): void
    {
        Auth::require('payroll.manage');
        $period = Database::fetch('SELECT * FROM payroll_periods WHERE id = ?', [$id]);
        if (!$period) {
            redirect('payroll');
        }
        $entries = Database::fetchAll(
            "SELECT e.employee_no, CONCAT(e.first_name,' ',e.last_name) AS name,
                    e.rssb_number, e.tin_number, e.bank_name, e.bank_account, pe.*
             FROM payroll_entries pe JOIN employees e ON e.id = pe.employee_id
             WHERE pe.period_id = ? ORDER BY e.last_name", [$id]
        );
        Database::update('payroll_periods', ['status' => 'exported', 'exported_at' => date('Y-m-d H:i:s')], $id);
        Audit::log('export', 'payroll_periods', (string) $id);

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=payroll_{$period['name']}.csv");
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Employee No', 'Name', 'RSSB', 'TIN', 'Bank', 'Account',
            'Basic', 'Transport', 'Telephone', 'Per Diem', 'Overtime', 'Bonus', 'Commission',
            'Gross', 'RSSB Employee', 'RSSB Employer', 'PAYE', 'Loans', 'Advance', 'Other', 'Net'], ',', '"', '\\');
        foreach ($entries as $r) {
            fputcsv($out, [$r['employee_no'], $r['name'], $r['rssb_number'], $r['tin_number'],
                $r['bank_name'], $r['bank_account'], $r['basic_salary'],
                $r['transport_allowance'], $r['telephone_allowance'], $r['per_diem'],
                $r['overtime'], $r['bonus'], $r['commission'], $r['gross_salary'],
                $r['rssb_employee'], $r['rssb_employer'], $r['paye'],
                $r['loan_deduction'], $r['advance_deduction'], $r['other_deduction'], $r['net_salary']], ',', '"', '\\');
        }
        fclose($out);
        exit;
    }

    private function paye(float $gross): float
    {
        $tax = 0.0;
        foreach (self::PAYE_BRACKETS as [$from, $to, $rate]) {
            if ($gross > $from) {
                $tax += (min($gross, $to) - $from) * $rate;
            }
        }
        return round($tax, 2);
    }
}
