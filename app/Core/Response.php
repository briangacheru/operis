<?php
declare(strict_types=1);

/**
 * Response — helpers for sending HTTP responses and rendering views.
 */
class Response
{
    private int    $status  = 200;
    private array  $headers = [];
    private string $viewRoot;

    public function __construct()
    {
        // Views live at app/Views/
        $this->viewRoot = dirname(__DIR__) . '/Views';
    }

    public function status(int $code): static
    {
        $this->status = $code;
        return $this;
    }

    public function header(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        return $this;
    }

    // -----------------------------------------------------------------------
    // JSON
    // -----------------------------------------------------------------------

    public function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        foreach ($this->headers as $k => $v) {
            header("$k: $v");
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function jsonError(string $message, int $status = 400): never
    {
        $this->json(['error' => $message], $status);
    }

    public function jsonSuccess(string $message, array $extra = []): never
    {
        $this->json(array_merge(['message' => $message], $extra));
    }

    // -----------------------------------------------------------------------
    // Views
    // -----------------------------------------------------------------------

    /**
     * Render a view template and return the output as a string.
     *
     * @param string               $view   Dot-notation path, e.g. 'tasks.index'
     * @param array<string, mixed> $data   Variables extracted into the view scope
     */
    public function render(string $view, array $data = [], string $layout = 'main'): never
    {
        $file = $this->viewRoot . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            $this->abort(500, "View not found: $view");
        }

        // Capture view output
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        $content = ob_get_clean();

        // Wrap in layout
        $layoutFile = $this->viewRoot . "/layouts/$layout.php";
        if ($layout && file_exists($layoutFile)) {
            http_response_code($this->status);
            foreach ($this->headers as $k => $v) {
                header("$k: $v");
            }
            include $layoutFile;   // layout uses $content
            exit;
        }

        http_response_code($this->status);
        foreach ($this->headers as $k => $v) {
            header("$k: $v");
        }
        echo $content;
        exit;
    }

    // -----------------------------------------------------------------------
    // Redirects
    // -----------------------------------------------------------------------

    public function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header("Location: $url");
        exit;
    }

    public function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
        $this->redirect($ref);
    }

    // -----------------------------------------------------------------------
    // Errors
    // -----------------------------------------------------------------------

    public function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        $errorView = $this->viewRoot . "/errors/$code.php";
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo "<h1>$code</h1><p>" . htmlspecialchars($message) . "</p>";
        }
        exit;
    }
}
