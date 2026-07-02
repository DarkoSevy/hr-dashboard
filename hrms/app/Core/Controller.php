<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /** Pull only the given keys from POST, trimming strings; '' becomes NULL. */
    protected function input(array $keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $_POST)) {
                continue;
            }
            $value = is_string($_POST[$key]) ? trim($_POST[$key]) : $_POST[$key];
            $data[$key] = ($value === '') ? null : $value;
        }
        return $data;
    }

    /**
     * Store an uploaded file under storage/uploads/<dir>/ with a random name.
     * Returns the relative path, or null when no file was sent.
     * Throws RuntimeException on validation failure.
     */
    protected function storeUpload(string $field, string $dir): ?string
    {
        if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $file = $_FILES[$field];
        $cfg = $GLOBALS['app_config']['uploads'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (error ' . $file['error'] . ').');
        }
        if ($file['size'] > $cfg['max_size']) {
            throw new \RuntimeException('File exceeds the 5 MB limit.');
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $cfg['allowed_types'], true)) {
            throw new \RuntimeException('File type .' . $ext . ' is not allowed.');
        }
        $target = $cfg['path'] . '/' . $dir;
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $target . '/' . $name)) {
            throw new \RuntimeException('Could not store the uploaded file.');
        }
        return $dir . '/' . $name;
    }
}
