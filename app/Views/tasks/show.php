<?php $pageTitle = htmlspecialchars($task['topic'] ?? 'Task', ENT_QUOTES) . ' — Operis'; ?>
<div class="container py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="/tasks" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
        <h5 class="mb-0"><?= htmlspecialchars($task['topic'], ENT_QUOTES) ?></h5>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-subtitle text-muted mb-2">Description</h6>
                    <p><?= nl2br(htmlspecialchars($task['description'] ?? '', ENT_QUOTES)) ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-muted fs--2">Status</dt>
                        <dd><?= htmlspecialchars($task['status'], ENT_QUOTES) ?></dd>

                        <dt class="text-muted fs--2">Pages</dt>
                        <dd><?= $task['pages'] ?></dd>

                        <dt class="text-muted fs--2">CPP</dt>
                        <dd>Ksh <?= number_format((float) $task['CPP'], 2) ?></dd>

                        <dt class="text-muted fs--2">Total Value</dt>
                        <dd class="fw-bold">Ksh <?= number_format((float) $task['CPP'] * (int) $task['pages'], 2) ?></dd>

                        <dt class="text-muted fs--2">Due Date</dt>
                        <dd><?= date('M j, Y H:i', strtotime($task['due_date'])) ?></dd>

                        <dt class="text-muted fs--2">Created</dt>
                        <dd><?= date('M j, Y', strtotime($task['create_date'])) ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Status update form -->
            <?php
            $transitions = [
                'In Progress' => ['Submitted'],
                'In Revision' => ['Submitted'],
                'Submitted'   => [],
                'Completed'   => [],
                'Cancelled'   => [],
                'Draft'       => ['In Progress'],
            ];
            $next = $transitions[$task['status']] ?? [];
            if ($next):
            ?>
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">Update Status</h6>
                    <form method="POST" action="/tasks/<?= $task['id'] ?>/status">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
                        <select name="status" class="form-select form-select-sm mb-2">
                            <?php foreach ($next as $s): ?>
                            <option value="<?= $s ?>"><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-primary w-100">Update</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
