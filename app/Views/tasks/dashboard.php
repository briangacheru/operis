<?php $pageTitle = 'Dashboard — Operis'; ?>
<div class="container-fluid px-3 py-4">

    <!-- Summary cards -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['label' => 'All Tasks',    'key' => 'total',     'color' => 'primary',  'href' => '/tasks'],
            ['label' => 'In Progress',  'key' => 'progress',  'color' => 'info',     'href' => '/tasks?status=In+Progress'],
            ['label' => 'Submitted',    'key' => 'submitted', 'color' => 'warning',  'href' => '/tasks?status=Submitted'],
            ['label' => 'Completed',    'key' => 'completed', 'color' => 'success',  'href' => '/tasks?status=Completed'],
            ['label' => 'Cancelled',    'key' => 'cancelled', 'color' => 'danger',   'href' => '/tasks?status=Cancelled'],
            ['label' => 'Overdue',      'key' => 'overdue',   'color' => 'secondary','href' => '/tasks?status=In+Progress'],
        ];
        foreach ($cards as $card):
        ?>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= $card['href'] ?>" class="text-decoration-none">
                <div class="card border-top border-<?= $card['color'] ?> border-3">
                    <div class="card-body">
                        <h6 class="text-muted fs--2 mb-1"><?= $card['label'] ?></h6>
                        <h4 class="mb-0 text-<?= $card['color'] ?>"><?= $summary[$card['key']] ?? 0 ?></h4>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Earnings -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted fs--2">Total Paid</h6>
                    <h4 class="text-success">Ksh <?= number_format($summary['paid_total'] ?? 0, 2) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted fs--2">Total Unpaid</h6>
                    <h4 class="text-warning">Ksh <?= number_format($summary['unpaid_total'] ?? 0, 2) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Due today -->
    <?php if (!empty($todayDue)): ?>
    <div class="card mb-4">
        <div class="card-header bg-warning bg-opacity-10">
            <h6 class="mb-0">Due Today (<?= count($todayDue) ?>)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Topic</th><th>Pages</th><th>Status</th><th>Due</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($todayDue as $task): ?>
                    <tr>
                        <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['topic'], ENT_QUOTES) ?></a></td>
                        <td><?= $task['pages'] ?></td>
                        <td><span class="badge bg-info"><?= $task['status'] ?></span></td>
                        <td><?= date('H:i', strtotime($task['due_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Overdue -->
    <?php if (!empty($overdue)): ?>
    <div class="card">
        <div class="card-header bg-danger bg-opacity-10">
            <h6 class="mb-0 text-danger">Overdue Tasks (<?= count($overdue) ?>)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Topic</th><th>Pages</th><th>Due Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($overdue as $task): ?>
                    <tr>
                        <td><a href="/tasks/<?= $task['id'] ?>"><?= htmlspecialchars($task['topic'], ENT_QUOTES) ?></a></td>
                        <td><?= $task['pages'] ?></td>
                        <td class="text-danger"><?= date('M j, Y H:i', strtotime($task['due_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>
