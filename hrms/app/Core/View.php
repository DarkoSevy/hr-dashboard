<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    /** Render a view inside the main layout (unless $bare). */
    public static function render(string $view, array $data = [], bool $bare = false): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = APP_PATH . '/Views/' . $view . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            exit("View not found: {$view}");
        }
        if ($bare) {
            require $viewFile;
            return;
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require APP_PATH . '/Views/layouts/main.php';
    }
}
