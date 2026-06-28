<?php
declare(strict_types=1);

/**
 * Front controller — all MVC requests go through here.
 *
 * Apache/Nginx: point the DocumentRoot at this public/ directory and
 * add a rewrite rule so unknown paths hit index.php.
 *
 * For XAMPP without moving DocumentRoot, add the public/.htaccess rewrite
 * and access the MVC app at /public/ (or configure a VirtualHost).
 */

require_once dirname(__DIR__) . '/app/Core/App.php';

(new App())->run();
