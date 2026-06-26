<?php
/**
 * sudo/includes/bootstrap.php — admin app initialisation.
 *
 * Loads the shared root bootstrap (Database, Auth, Helpers) then adds
 * admin-specific classes. Include this one file at the top of every
 * admin (sudo/) page instead of the scattered require/include calls.
 *
 *   require_once __DIR__ . '/includes/bootstrap.php';
 *
 * After this runs, globals available:
 *   $db   — Database singleton
 *   $con  — MySQLi connection  (backward compat)
 *   $dbh  — PDO connection     (backward compat)
 *   AdminAuth, AdminHelpers classes
 *   All root Helpers functions
 */

// Shared bootstrap (Database, Auth, Helpers, $con, $dbh).
require_once __DIR__ . '/../../includes/bootstrap.php';

// Admin-specific classes.
require_once __DIR__ . '/AdminAuth.php';
require_once __DIR__ . '/AdminHelpers.php';
