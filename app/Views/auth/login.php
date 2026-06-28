<?php $pageTitle = 'Login — Operis'; ?>
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="col-sm-10 col-md-8 col-lg-5 col-xl-5 px-xxl-2">
        <div class="card">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-5">
                    <h5 class="fs-4 fw-bolder">Sign In</h5>
                    <p class="text-muted">Enter your credentials to continue</p>
                </div>

                <form method="POST" action="/login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">

                    <div class="mb-3">
                        <label class="form-label" for="email">Email address</label>
                        <input class="form-control" id="email" type="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
                               required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" type="password" name="password" required>
                    </div>

                    <div class="d-grid mb-3">
                        <button class="btn btn-primary" type="submit">Sign In</button>
                    </div>

                    <div class="text-center">
                        <a href="/forgot-password" class="fs--1">Forgot password?</a>
                        &nbsp;·&nbsp;
                        <a href="/register" class="fs--1">Create account</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
