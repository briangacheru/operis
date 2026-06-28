<?php
declare(strict_types=1);

/**
 * Application routes.
 * $router is injected by App::__construct() before this file is required.
 */

// -----------------------------------------------------------------------
// Auth
// -----------------------------------------------------------------------
$router->get('/login',           [AuthController::class, 'showLogin']);
$router->post('/login',          [AuthController::class, 'login']);
$router->post('/logout',         [AuthController::class, 'logout']);
$router->get('/register',        [AuthController::class, 'showRegister']);
$router->post('/register',       [AuthController::class, 'register']);
$router->get('/forgot-password', [AuthController::class, 'showForgot']);

// -----------------------------------------------------------------------
// Dashboard
// -----------------------------------------------------------------------
$router->get('/',          [TaskController::class, 'dashboard']);
$router->get('/dashboard', [TaskController::class, 'dashboard']);

// -----------------------------------------------------------------------
// Tasks
// -----------------------------------------------------------------------
$router->get('/tasks',                    [TaskController::class, 'index']);
$router->get('/tasks/create',             [TaskController::class, 'create']);
$router->post('/tasks',                   [TaskController::class, 'store']);
$router->get('/tasks/{id}',               [TaskController::class, 'show']);
$router->get('/tasks/{id}/edit',          [TaskController::class, 'edit']);
$router->post('/tasks/{id}/status',       [TaskController::class, 'updateStatus']);
$router->post('/tasks/{id}/delete',       [TaskController::class, 'destroy']);
$router->post('/tasks/{id}/acknowledge',  [TaskController::class, 'acknowledge']);

// -----------------------------------------------------------------------
// Profile
// -----------------------------------------------------------------------
$router->get('/profile',          [ProfileController::class, 'show']);
$router->get('/profile/edit',     [ProfileController::class, 'edit']);
$router->post('/profile/update',  [ProfileController::class, 'update']);
$router->post('/profile/password',[ProfileController::class, 'changePassword']);

// -----------------------------------------------------------------------
// Chat
// -----------------------------------------------------------------------
$router->get('/chat',                  [ChatController::class, 'index']);
$router->get('/chat/{adminId}',        [ChatController::class, 'conversation']);
$router->get('/chat/poll',             [ChatController::class, 'poll']);
$router->post('/chat/send',            [ChatController::class, 'send']);
$router->post('/chat/read',            [ChatController::class, 'markRead']);
