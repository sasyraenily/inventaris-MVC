<?php
declare(strict_types=1);

class Database
{
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'db_inventaris';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    public static function connect(): PDO
    {
        $dsn = 'mysql:host=' . self::DB_HOST . ';dbname=' . self::DB_NAME . ';charset=utf8mb4';
        return new PDO($dsn, self::DB_USER, self::DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
}
