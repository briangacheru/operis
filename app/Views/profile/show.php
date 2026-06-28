<?php $pageTitle = 'Profile — Operis'; ?>
<div class="container py-4" style="max-width: 720px">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="mb-0">My Profile</h5>
        <a href="/profile/edit" class="btn btn-sm btn-outline-primary">Edit Profile</a>
    </div>

    <div class="row g-4">
        <div class="col-md-4 text-center">
            <?php if (!empty($user['Photo'])): ?>
            <img src="/<?= htmlspecialchars($user['Photo'], ENT_QUOTES) ?>"
                 class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover"
                 alt="Profile photo">
            <?php else: ?>
            <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:100px;height:100px">
                <span class="text-white fs-3 fw-bold">
                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                </span>
            </div>
            <?php endif; ?>
            <h6><?= htmlspecialchars($user['username'] ?? '', ENT_QUOTES) ?></h6>
            <p class="text-muted fs--1"><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?></p>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">Earnings</h6>
                    <div class="row text-center">
                        <div class="col">
                            <div class="fs-4 fw-bold text-success"><?= $stats['completed'] ?></div>
                            <div class="text-muted fs--2">Completed</div>
                        </div>
                        <div class="col">
                            <div class="fs-6 fw-bold text-primary">Ksh <?= number_format($stats['paid'], 2) ?></div>
                            <div class="text-muted fs--2">Paid</div>
                        </div>
                        <div class="col">
                            <div class="fs-6 fw-bold text-warning">Ksh <?= number_format($stats['unpaid'], 2) ?></div>
                            <div class="text-muted fs--2">Pending</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Details</h6>
                    <dl class="row mb-0">
                        <dt class="col-4 text-muted fs--2">Contact</dt>
                        <dd class="col-8"><?= htmlspecialchars($user['contact'] ?? '—', ENT_QUOTES) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Change password -->
    <div class="card mt-4">
        <div class="card-header"><h6 class="mb-0">Change Password</h6></div>
        <div class="card-body">
            <form method="POST" action="/profile/password" style="max-width:400px">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">

                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="8">
                </div>
                <button class="btn btn-warning btn-sm" type="submit">Change Password</button>
            </form>
        </div>
    </div>
</div>
