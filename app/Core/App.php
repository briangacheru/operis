<?php
declare(strict_types=1);

/**
 * App — bootstraps the MVC stack and dispatches the request.
 *
 * Usage in public/index.php:
 *   require_once '../app/Core/App.php';
 *   (new App())->run();
 */
class App
{
    private Router   $router;
    private Request  $request;
    private Response $response;

    public function __construct()
    {
        $root = dirname(__DIR__, 2);  // project root

        // 1. Config + DB
        require_once $root . '/config.php';
        require_once $root . '/db.php';           // sets $con + registers Database singleton

        // 2. Core classes
        require_once __DIR__ . '/Request.php';
        require_once __DIR__ . '/Response.php';
        require_once __DIR__ . '/Router.php';
        require_once __DIR__ . '/Controller.php';

        // 3. Shared helpers + repositories + services
        require_once $root . '/shared-functions.php';
        require_once $root . '/includes/Validator.php';
        require_once $root . '/includes/Repository/BaseRepository.php';
        require_once $root . '/includes/Repository/UserRepository.php';
        require_once $root . '/includes/Repository/AdminRepository.php';
        require_once $root . '/includes/Repository/TaskRepository.php';
        require_once $root . '/includes/Repository/ChatRepository.php';
        require_once $root . '/includes/Repository/OverdraftRepository.php';
        require_once $root . '/includes/Service/UserService.php';
        require_once $root . '/includes/Service/TaskService.php';

        // 4. Controllers
        $controllers = glob(__DIR__ . '/../Controllers/*.php');
        foreach ($controllers as $file) {
            require_once $file;
        }

        // 5. Bootstrap
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->request  = new Request();
        $this->response = new Response();
        $this->router   = new Router();

        // 6. Register routes
        require_once $root . '/routes.php';
    }

    public function run(): void
    {
        $this->router->dispatch($this->request, $this->response);
    }

    public function router(): Router   { return $this->router; }
    public function request(): Request { return $this->request; }
}
