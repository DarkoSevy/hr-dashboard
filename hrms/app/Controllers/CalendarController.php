<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/** Month calendar: public holidays, approved leave, training sessions. */
class CalendarController extends Controller
{
    public function index(): void
    {
        Auth::require('leaves.request');

        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $_GET['month'] ?? '')
            ? $_GET['month'] : date('Y-m');
        $start = $month . '-01';
        $end = date('Y-m-t', strtotime($start));

        $events = [];
        $add = function (string $date, string $type, string $labelText) use (&$events): void {
            $events[$date][] = ['type' => $type, 'label' => $labelText];
        };

        foreach (Database::fetchAll(
            'SELECT date, name FROM holidays WHERE date BETWEEN ? AND ?', [$start, $end]
        ) as $h) {
            $add($h['date'], 'holiday', $h['name']);
        }

        // Approved leave overlapping the month, expanded per day.
        foreach (Database::fetchAll(
            "SELECT lr.start_date, lr.end_date, lt.name AS type,
                    CONCAT(e.first_name,' ',SUBSTRING(e.last_name,1,1),'.') AS who
             FROM leave_requests lr
             JOIN employees e ON e.id = lr.employee_id
             JOIN leave_types lt ON lt.id = lr.leave_type_id
             WHERE lr.status = 'approved' AND lr.start_date <= ? AND lr.end_date >= ?",
            [$end, $start]
        ) as $l) {
            $from = max($l['start_date'], $start);
            $to = min($l['end_date'], $end);
            for ($d = $from; $d <= $to; $d = date('Y-m-d', strtotime($d . ' +1 day'))) {
                $add($d, 'leave', "{$l['who']} — {$l['type']}");
            }
        }

        foreach (Database::fetchAll(
            "SELECT ts.start_date, tc.name FROM training_sessions ts
             JOIN training_courses tc ON tc.id = ts.course_id
             WHERE ts.status IN ('planned','ongoing') AND ts.start_date BETWEEN ? AND ?",
            [$start, $end]
        ) as $t) {
            $add($t['start_date'], 'training', $t['name']);
        }

        $this->view('calendar/index', [
            'month'  => $month,
            'events' => $events,
            'prev'   => date('Y-m', strtotime($start . ' -1 month')),
            'next'   => date('Y-m', strtotime($start . ' +1 month')),
        ]);
    }
}
