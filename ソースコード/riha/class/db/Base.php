<?php
/** データベースに関するクラス */
class Base
{
    const DB_NAME = "riha";
    const DB_HOST = "localhost";
    const DB_USER = "root";
    const DB_PASS = '';
    const DSN = 'mysql:dbname=' . self::DB_NAME . ';host=' . self::DB_HOST . ';charset=utf8';

    private static $pdo;
/**
 * PDOクラスのインスタンスを生成して返却
 * @return void
 */
    public static function getInstance()
    {
        if (!isset(self::$pdo)) {
            self::$pdo = new \PDO(self::DSN, self::DB_USER, self::DB_PASS);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }
}
