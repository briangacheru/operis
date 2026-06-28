<?php
declare(strict_types=1);

/**
 * Production front controller — place this as public_html/index.php on cPanel.
 *
 * Directory layout on server:
 *   /home/yourusername/operis/   ← app source (this repo minus public/)
 *   /home/yourusername/public_html/index.php  ← this file
 */

// Adjust this path to match your cPanel username and app folder name.
define('APP_ROOT', '/home/yourusername/operis');

require_once APP_ROOT . '/app/Core/App.php';

(new App(APP_ROOT))->run();
