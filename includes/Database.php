<?php
declare(strict_types=1);

/**
 * Database — singleton accessors for MySQLi and PDO.
 *
 * Existing code that still uses the procedural $con variable is unaffected;
 * new code (Repositories, Services, API) should call Database::getMySQLi()
 * or Database::getPDO() instead of relying on a global.
 */
class Database
{
    private static ?mysqli $mysqli = null;
    private static ?PDO    $pdo   = null;

    private function __construct() {}
    private function __clone() {}

    public static function getMySQLi(): mysqli
    {
        if (self::$mysqli === null) {
            self::$mysqli = self::createMySQLi();
        }
        return self::$mysqli;
    }

    public static function getPDO(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::createPDO();
        }
        return self::$pdo;
    }

    /**
     * Allow db.php to register the already-created connection so both paths
     * share the same underlying socket.
     */
    public static function setMySQLi(mysqli $con): void
    {
        self::$mysqli = $con;
    }

    private static function createMySQLi(): mysqli
    {
        $root = dirname(__DIR__);
        if (!function_exists('env')) {
            require_once $root . '/config.php';
        }
        $con = new mysqli(env('DB_HOST'), env('DB_USER'), env('DB_PASS'), env('DB_NAME'));
        if ($con->connect_error) {
            throw new RuntimeException('MySQLi connection failed: ' . $con->connect_error);
        }
        $con->set_charset('utf8mb4');
        return $con;
    }

    private static function createPDO(): PDO
    {
        $root = dirname(__DIR__);
        if (!function_exists('env')) {
            require_once $root . '/config.php';
        }
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST'),
            env('DB_NAME')
        );
        return new PDO($dsn, env('DB_USER'), env('DB_PASS'), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
}
