<?php
declare(strict_types=1);

class AuthController extends Controller
{
    private UserService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UserService();
    }

    // GET /login
    public function showLogin(Request $req, Response $res): never
    {
        if (!empty($_SESSION['sessionWriter'])) {
            $this->redirect('/dashboard');
        }
        $this->view('auth.login', [
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ], 'guest');
    }

    // POST /login
    public function login(Request $req, Response $res): never
    {
        $email    = trim($req->input('email', ''));
        $password = $req->input('password', '');

        try {
            $user = $this->service->authenticateWriter($email, $password);
            session_regenerate_id(true);
            $_SESSION['sessionWriter'] = $user['email'];
            $this->redirect('/dashboard');
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            $this->redirect('/login');
        }
    }

    // POST /logout
    public function logout(Request $req, Response $res): never
    {
        if (!empty($_SESSION['sessionWriter'])) {
            $this->service->logoutWriter($_SESSION['sessionWriter']);
        } else {
            session_destroy();
        }
        $this->redirect('/login');
    }

    // GET /register
    public function showRegister(Request $req, Response $res): never
    {
        if (!empty($_SESSION['sessionWriter'])) {
            $this->redirect('/dashboard');
        }
        $this->view('auth.register', [
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ], 'guest');
    }

    // POST /register
    public function register(Request $req, Response $res): never
    {
        try {
            $this->service->registerWriter($req->all());
            $this->flashSuccess('Account created. Please log in.');
            $this->redirect('/login');
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            $this->redirect('/register');
        }
    }

    // GET /forgot-password
    public function showForgot(Request $req, Response $res): never
    {
        $this->view('auth.forgot', [
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ], 'guest');
    }
}
