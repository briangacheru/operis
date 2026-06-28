<?php
declare(strict_types=1);

/**
 * Controller — base class for all MVC controllers.
 *
 * Provides:
 *  - $this->response  (Response instance)
 *  - $this->auth()    (returns session email or aborts 401)
 *  - $this->validate() (returns Validator, throws on failure for JSON callers)
 *  - $this->view()    shortcut for render
 *  - $this->json()    shortcut for JSON response
 *  - $this->redirect() shortcut
 *  - Flash message helpers
 */
abstract class Controller
{
    protected Response $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    // -----------------------------------------------------------------------
    // Auth
    // -----------------------------------------------------------------------

    /** Returns the authenticated writer's email or aborts/redirects. */
    protected function auth(bool $redirect = true): string
    {
        if (empty($_SESSION['sessionWriter'])) {
            if ($redirect) {
                $this->response->redirect('/login');
            }
            $this->response->jsonError('Unauthenticated', 401);
        }
        return $_SESSION['sessionWriter'];
    }

    protected function adminAuth(bool $redirect = true): string
    {
        if (empty($_SESSION['userSession'])) {
            if ($redirect) {
                $this->response->redirect('/sudo/login');
            }
            $this->response->jsonError('Unauthenticated', 401);
        }
        return $_SESSION['userSession'];
    }

    // -----------------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------------

    /**
     * Validate $data and return the Validator.
     * If the request is JSON/AJAX and validation fails, sends a 422 response automatically.
     */
    protected function validate(Request $request, array $rules): Validator
    {
        $v = new Validator($request->all());
        foreach ($rules as $rule) {
            $rule($v);
        }
        return $v;
    }

    // -----------------------------------------------------------------------
    // Response shortcuts
    // -----------------------------------------------------------------------

    protected function view(string $view, array $data = [], string $layout = 'main'): never
    {
        $this->response->render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        $this->response->json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): never
    {
        $this->response->redirect($url, $status);
    }

    protected function abort(int $code, string $msg = ''): never
    {
        $this->response->abort($code, $msg);
    }

    // -----------------------------------------------------------------------
    // Flash messages (stored in session, consumed once)
    // -----------------------------------------------------------------------

    protected function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    protected function flashError(string $message): void   { $this->flash('error', $message); }
    protected function flashSuccess(string $message): void { $this->flash('success', $message); }

    protected function getFlash(string $key): ?string
    {
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
