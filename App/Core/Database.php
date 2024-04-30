<?php

namespace App\Core;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;

class Database
{
    private static $instance = null;

    private $dbh, $error;

    private function __construct()
    {
        try {
            $connectionParams = [
                'dbname' => $_ENV['DB'],
                'user' => $_ENV['DB_USER'],
                'password' => $_ENV['PASSWORD'],
                'host' => $_ENV['HOST'],
                'driver' => 'pdo_mysql',
            ];
            $this->dbh = DriverManager::getConnection($connectionParams);
        } catch (\Exception $e) {
            $this->error[] = $e->getMessage();
        }
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function connection()
    {
        return $this->dbh;
    }

    public function queryBuilder()
    {
        return $this->dbh->createQueryBuilder();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
}
