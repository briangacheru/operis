<?php $pageTitle = 'Create Account — Operis'; ?>
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-sm-10 col-md-8 col-lg-5 px-xxl-2">
        <div class="card">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-5">
                    <h5 class="fs-4 fw-bolder">Create Account</h5>
                </div>

                <form method="POST" action="/register">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">

                    <div class="mb-3">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-control" id="username" type="text" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES) ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email address</label>
                        <input class="form-control" id="email" type="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" type="password" name="password" required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="contact">Phone (optional)</label>
                        <input class="form-control" id="contact" type="tel" name="contact"
                               value="<?= htmlspecialchars($_POST['contact'] ?? '', ENT_QUOTES) ?>">
                    </div>

                    <div class="d-grid mb-3">
                        <button class="btn btn-primary" type="submit">Create Account</button>
                    </div>

                    <div class="text-center">
                        <a href="/login" class="fs--1">Already have an account? Sign in</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
