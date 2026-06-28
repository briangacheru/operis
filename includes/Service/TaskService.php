<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/Repository/TaskRepository.php';
require_once dirname(__DIR__) . '/Validator.php';

/**
 * TaskService — business logic for task lifecycle management.
 */
class TaskService
{
    private TaskRepository $tasks;

    public function __construct(?TaskRepository $tasks = null)
    {
        $this->tasks = $tasks ?? new TaskRepository();
    }

    // -----------------------------------------------------------------------
    // Read
    // -----------------------------------------------------------------------

    public function getTasksForWriter(string $email, string $status = ''): array
    {
        return $this->tasks->findByWriter($email, $status);
    }

    public function getTaskById(int $id): ?array
    {
        return $this->tasks->findById($id);
    }

    /** Verifies the task belongs to $email before returning it. */
    public function getTaskForWriter(string $email, int $id): ?array
    {
        return $this->tasks->findByWriterAndId($email, $id);
    }

    public function getDashboardSummary(string $email): array
    {
        return [
            'total'     => $this->tasks->countByWriter($email),
            'progress'  => $this->tasks->countByWriter($email, 'In Progress'),
            'submitted' => $this->tasks->countByWriter($email, 'Submitted'),
            'completed' => $this->tasks->countByWriter($email, 'Completed'),
            'cancelled' => $this->tasks->countByWriter($email, '', true),
            'unpaid'    => $this->tasks->countByWriter($email, 'Completed'),
            'overdue'   => count($this->tasks->getOverdue($email)),
            'paid_total'   => $this->tasks->sumEarnings($email, true),
            'unpaid_total' => $this->tasks->sumEarnings($email, false),
        ];
    }

    public function getUnacknowledgedTasks(string $email): array
    {
        return $this->tasks->getUnacknowledged($email);
    }

    public function getOverdueTasks(string $email): array
    {
        return $this->tasks->getOverdue($email);
    }

    // -----------------------------------------------------------------------
    // Write
    // -----------------------------------------------------------------------

    /** @throws InvalidArgumentException */
    public function createTask(array $input, string $writerEmail): int
    {
        $v = (new Validator($input))
            ->required(['topic', 'pages', 'CPP', 'due_date'])
            ->positiveNumber('pages')
            ->positiveNumber('CPP')
            ->date('due_date');

        if ($v->fails()) {
            throw new InvalidArgumentException(implode(' ', $v->allErrors()));
        }

        return $this->tasks->create([
            'topic'       => trim($input['topic']),
            'description' => trim($input['description'] ?? ''),
            'pages'       => (int) $input['pages'],
            'CPP'         => (float) $input['CPP'],
            'due_date'    => $input['due_date'],
            'status'      => $input['status'] ?? 'In Progress',
            'email'       => $writerEmail,
        ]);
    }

    /** @throws InvalidArgumentException */
    public function updateStatus(int $id, string $status, string $writerEmail): void
    {
        $allowed = ['Draft', 'In Progress', 'In Revision', 'Submitted', 'Completed', 'Cancelled'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException("Invalid status: $status");
        }

        $task = $this->tasks->findByWriterAndId($writerEmail, $id);
        if (!$task) {
            throw new InvalidArgumentException('Task not found or access denied.');
        }

        $this->tasks->updateStatus($id, $status);
    }

    public function acknowledgeTask(int $id): void
    {
        $this->tasks->markAcknowledged($id);
    }
}
