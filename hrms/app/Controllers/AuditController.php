<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/** Read-only audit trail with CSV export. */
class AuditController extends Controller
{
    public function index(): void
    {
        Auth::require('audit.view');
        $logs = Database::fetchAll(
            'SELECT al.*, u.username FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT 500'
        );
        $this->view('audit/index', ['logs' => $logs]);
    }

    public function export(): void
    {
        Auth::require('audit.view');
        $logs = Database::fetchAll(
            'SELECT al.created_at, u.username, al.action, al.entity, al.entity_id,
                    al.old_values, al.new_values, al.ip_address, al.user_agent
             FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC LIMIT 10000'
        );
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=audit_logs_' . date('Ymd') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['When', 'User', 'Action', 'Entity', 'Entity ID', 'Old Values', 'New Values', 'IP', 'Device/Browser'], ",", '"', "\\");
        foreach ($logs as $row) {
            fputcsv($out, $row, ",", '"', "\\");
        }
        fclose($out);
        exit;
    }
}
