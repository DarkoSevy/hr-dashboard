<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class NotificationController extends Controller
{
    public function index(): void
    {
        $notifications = Database::fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100',
            [Auth::id()]
        );
        Database::query('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [Auth::id()]);
        $this->view('notifications/index', ['notifications' => $notifications]);
    }
}
