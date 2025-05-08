<?php

namespace galastri\modules;

use galastri\modules\database\MysqlDatabase;
use galastri\modules\database\SqliteDatabase;

final class Database
{
    private function __construct() {}

    public static function createMysql(?string $dbConfigName = null): MysqlDatabase
    {
        return new MysqlDatabase($dbConfigName);
    }

    public static function createSqlite(?string $dbConfigName = null): SqliteDatabase
    {
        return new SqliteDatabase($dbConfigName);
    }
}
