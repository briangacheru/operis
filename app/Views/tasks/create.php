<?php $pageTitle = 'New Task — Operis'; ?>
<div class="container py-4" style="max-width: 640px">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="/tasks" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
        <h5 class="mb-0">New Task</h5>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="/tasks">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">

                <div class="mb-3">
                    <label class="form-label" for="topic">Topic <span class="text-danger">*</span></label>
                    <input class="form-control" id="topic" type="text" name="topic"
                           value="<?= htmlspecialchars($_POST['topic'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES) ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label" for="pages">Pages <span class="text-danger">*</span></label>
                        <input class="form-control" id="pages" type="number" name="pages" min="1"
                               value="<?= htmlspecialchars($_POST['pages'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="CPP">CPP (Ksh) <span class="text-danger">*</span></label>
                        <input class="form-control" id="CPP" type="number" name="CPP" step="0.01" min="0"
                               value="<?= htmlspecialchars($_POST['CPP'] ?? '', ENT_QUOTES) ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="due_date">Due Date <span class="text-danger">*</span></label>
                    <input class="form-control" id="due_date" type="datetime-local" name="due_date"
                           value="<?= htmlspecialchars($_POST['due_date'] ?? '', ENT_QUOTES) ?>" required>
                </div>

                <div class="d-grid">
                    <button class="btn btn-primary" type="submit">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
