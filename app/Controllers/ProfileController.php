<?php
declare(strict_types=1);

class ProfileController extends Controller
{
    private UserService    $service;
    private UserRepository $repo;

    public function __construct()
    {
        parent::__construct();
        $this->service = new UserService();
        $this->repo    = new UserRepository();
    }

    // GET /profile
    public function show(Request $req, Response $res): never
    {
        $email = $this->auth();
        $user  = $this->repo->getProfile($email);

        if (!$user) {
            $this->abort(404, 'Profile not found.');
        }

        $taskRepo = new TaskRepository();
        $stats    = [
            'completed' => $taskRepo->countByWriter($email, 'Completed'),
            'paid'      => $taskRepo->sumEarnings($email, true),
            'unpaid'    => $taskRepo->sumEarnings($email, false),
        ];

        $this->view('profile.show', [
            'user'    => $user,
            'stats'   => $stats,
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    // GET /profile/edit
    public function edit(Request $req, Response $res): never
    {
        $email = $this->auth();
        $user  = $this->repo->getProfile($email);

        $this->view('profile.edit', [
            'user'    => $user,
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    // POST /profile/update
    public function update(Request $req, Response $res): never
    {
        $email = $this->auth();

        try {
            $this->service->updateWriterProfile($email, $req->all());
            $this->flashSuccess('Profile updated.');
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
        }

        $this->redirect('/profile');
    }

    // POST /profile/password
    public function changePassword(Request $req, Response $res): never
    {
        $email = $this->auth();

        try {
            $this->service->changeWriterPassword(
                $email,
                $req->input('current_password', ''),
                $req->input('new_password', ''),
                $req->input('confirm_password', '')
            );
            $this->flashSuccess('Password changed successfully.');
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
        }

        $this->redirect('/profile');
    }
}
