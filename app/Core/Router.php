<?php
declare(strict_types=1);

/**
 * Router — maps URL patterns to controller actions.
 *
 * Patterns support named segments:  /tasks/{id}
 * Optional trailing slash is always stripped before matching.
 *
 * Usage:
 *   $router = new Router();
 *   $router->get('/tasks',       [TaskController::class, 'index']);
 *   $router->post('/tasks',      [TaskController::class, 'store']);
 *   $router->get('/tasks/{id}',  [TaskController::class, 'show']);
 *   $router->dispatch($request, $response);
 */
class Router
{
    /** @var array<string, array<array{pattern:string, regex:string, keys:string[], handler:callable|array}>> */
    private array $routes = [];

    // -----------------------------------------------------------------------
    // Registration helpers
    // -----------------------------------------------------------------------

    public function get(string $pattern, array|callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array|callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, array|callable $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function patch(string $pattern, array|callable $handler): void
    {
        $this->add('PATCH', $pattern, $handler);
    }

    public function delete(string $pattern, array|callable $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    /** Register GET + POST under the same pattern (useful for form pages). */
    public function any(string $pattern, array|callable $handler): void
    {
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $m) {
            $this->add($m, $pattern, $handler);
        }
    }

    // -----------------------------------------------------------------------
    // Dispatch
    // -----------------------------------------------------------------------

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $uri    = rtrim($request->uri(), '/') ?: '/';

        // Support _method override (HTML forms can't send PATCH/DELETE)
        if ($method === 'POST' && $request->input('_method')) {
            $method = strtoupper($request->input('_method'));
        }

        $candidates = array_merge(
            $this->routes[$method] ?? [],
            $this->routes['ANY']   ?? []
        );

        foreach ($candidates as $route) {
            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            // Extract named captures
            $params = array_filter(
                $matches,
                fn($k) => !is_int($k),
                ARRAY_FILTER_USE_KEY
            );
            $request->setParams($params);

            $this->call($route['handler'], $request, $response);
            return;
        }

        // No route matched
        $response->abort(404, "No route for $method $uri");
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    private function add(string $method, string $pattern, array|callable $handler): void
    {
        [$regex, $keys] = $this->compile($pattern);
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'regex'   => $regex,
            'keys'    => $keys,
            'handler' => $handler,
        ];
    }

    /** Convert '/tasks/{id}' → a named-capture regex. */
    private function compile(string $pattern): array
    {
        $keys  = [];
        $regex = preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$keys) {
            $keys[] = $m[1];
            return "(?P<{$m[1]}>[^/]+)";
        }, $pattern);

        $regex = '#^' . $regex . '$#';
        return [$regex, $keys];
    }

    private function call(array|callable $handler, Request $request, Response $response): void
    {
        if (is_callable($handler)) {
            $handler($request, $response);
            return;
        }

        [$class, $method] = $handler;

        if (!class_exists($class)) {
            throw new RuntimeException("Controller class not found: $class");
        }

        $controller = new $class();
        if (!method_exists($controller, $method)) {
            throw new RuntimeException("Method $class::$method() not found");
        }

        $controller->$method($request, $response);
    }
}
