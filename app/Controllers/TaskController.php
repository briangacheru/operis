<?php
declare(strict_types=1);

class TaskController extends Controller
{
    private TaskService    $service;
    private TaskRepository $repo;

    public function __construct()
    {
        parent::__construct();
        $this->service = new TaskService();
        $this->repo    = new TaskRepository();
    }

    // GET /dashboard
    public function dashboard(Request $req, Response $res): never
    {
        $email   = $this->auth();
        $summary = $this->service->getDashboardSummary($email);
        $today   = $this->repo->getTodayDue($email);
        $overdue = $this->repo->getOverdue($email);

        $this->view('tasks.dashboard', [
            'summary'   => $summary,
            'todayDue'  => $today,
            'overdue'   => $overdue,
            'error'     => $this->getFlash('error'),
            'success'   => $this->getFlash('success'),
        ]);
    }

    // GET /tasks?status=
    public function index(Request $req, Response $res): never
    {
        $email  = $this->auth();
        $status = $req->query('status', '');
        $tasks  = $this->service->getTasksForWriter($email, $status);

        $this->view('tasks.index', [
            'tasks'   => $tasks,
            'status'  => $status,
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    // GET /tasks/{id}
    public function show(Request $req, Response $res): never
    {
        $email = $this->auth();
        $id    = (int) $req->param('id');
        $task  = $this->service->getTaskForWriter($email, $id);

        if (!$task) {
            $this->abort(404, 'Task not found.');
        }

        $this->view('tasks.show', ['task' => $task]);
    }

    // GET /tasks/create
    public function create(Request $req, Response $res): never
    {
        $this->auth();
        $this->view('tasks.create', [
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    // POST /tasks
    public function store(Request $req, Response $res): never
    {
        $email = $this->auth();

        try {
            $id = $this->service->createTask($req->all(), $email);
            $this->flashSuccess('Task created successfully.');
            $this->redirect("/tasks/$id");
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            $this->redirect('/tasks/create');
        }
    }

    // GET /tasks/{id}/edit
    public function edit(Request $req, Response $res): never
    {
        $email = $this->auth();
        $id    = (int) $req->param('id');
        $task  = $this->service->getTaskForWriter($email, $id);

        if (!$task) {
            $this->abort(404, 'Task not found.');
        }

        $this->view('tasks.edit', [
            'task'    => $task,
            'error'   => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
        ]);
    }

    // POST /tasks/{id}/status   (HTML form override via _method=PATCH)
    public function updateStatus(Request $req, Response $res): never
    {
        $email  = $this->auth();
        $id     = (int) $req->param('id');
        $status = $req->input('status', '');

        try {
            $this->service->updateStatus($id, $status, $email);
            $this->flashSuccess('Status updated.');
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
        }

        $this->redirect("/tasks/$id");
    }

    // POST /tasks/{id}/delete
    public function destroy(Request $req, Response $res): never
    {
        $email = $this->auth();
        $id    = (int) $req->param('id');
        $task  = $this->service->getTaskForWriter($email, $id);

        if (!$task) {
            $this->abort(404, 'Task not found.');
        }

        $this->repo->softDelete($id);
        $this->flashSuccess('Task removed.');
        $this->redirect('/tasks');
    }

    // POST /tasks/{id}/acknowledge
    public function acknowledge(Request $req, Response $res): never
    {
        $this->auth();
        $id = (int) $req->param('id');
        $this->service->acknowledgeTask($id);

        if ($req->isAjax()) {
            $this->json(['message' => 'Acknowledged.']);
        }
        $this->redirect('/dashboard');
    }
}
