<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
function navActive(string $path): string {
    global $currentUri;
    return str_starts_with((string) $currentUri, $path) ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-3">
    <a class="navbar-brand fw-bold" href="/dashboard">Operis</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto">
            <li class="nav-item">
                <a class="nav-link <?= navActive('/dashboard') ?>" href="/dashboard">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= navActive('/tasks') ?>" href="/tasks">Tasks</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= navActive('/chat') ?>" href="/chat">Messages</a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
                <a class="nav-link <?= navActive('/profile') ?>" href="/profile">Profile</a>
            </li>
            <li class="nav-item">
                <form method="POST" action="/logout" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
                    <button type="submit" class="btn btn-link nav-link">Sign out</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
