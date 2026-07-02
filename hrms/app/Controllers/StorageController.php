<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Streams uploaded files to authenticated users. Files live in
 * storage/uploads (outside the public web root) so they can never be
 * executed or fetched anonymously.
 */
class StorageController extends Controller
{
    public function stream(array $segments): void
    {
        $relative = implode('/', $segments);
        $base = realpath($GLOBALS['app_config']['uploads']['path']);
        $file = realpath($base . '/' . $relative);

        // Path-traversal guard: the resolved file must stay inside uploads/.
        if (!$base || !$file || !str_starts_with($file, $base . DIRECTORY_SEPARATOR) || !is_file($file)) {
            http_response_code(404);
            exit('File not found.');
        }

        $mime = match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'pdf'          => 'application/pdf',
            'jpg', 'jpeg'  => 'image/jpeg',
            'png'          => 'image/png',
            'doc'          => 'application/msword',
            'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default        => 'application/octet-stream',
        };
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    }
}
