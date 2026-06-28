<?php $pageTitle = 'Tasks — Operis'; ?>
<div class="container-fluid px-3 py-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0"><?= $status ? htmlspecialchars($status, ENT_QUOTES) : 'All' ?> Tasks</h5>
        <a href="/tasks/create" class="btn btn-sm btn-primary">+ New Task</a>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="alert alert-info">No tasks found.</div>
    <?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Topic</th>
                        <th>Pages</th>
                        <th>CPP (Ksh)</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td>
                            <a href="/tasks/<?= $task['id'] ?>" class="fw-semibold text-decoration-none">
                                <?= htmlspecialchars($task['topic'], ENT_QUOTES) ?>
                            </a>
                        </td>
                        <td><?= $task['pages'] ?></td>
                        <td><?= number_format((float) $task['CPP'], 2) ?></td>
                        <td><?= date('M j, Y', strtotime($task['due_date'])) ?></td>
                        <td>
                            <?php
                            $badgeClass = match($task['status']) {
                                'In Progress' => 'bg-info',
                                'Submitted'   => 'bg-warning text-dark',
                                'Completed'   => 'bg-success',
                                'In Revision' => 'bg-secondary',
                                'Cancelled'   => 'bg-danger',
                                default       => 'bg-light text-dark',
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $task['status'] ?></span>
                        </td>
                        <td class="text-end">
                            <a href="/tasks/<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>
