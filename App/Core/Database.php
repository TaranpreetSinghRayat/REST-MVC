<?php

namespace App\Core;

use Doctrine\DBAL\DriverManager;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Database
{
    private static $connection;
    private static $logger;

    public static function getConnection()
    {
        if (self::$connection) {
            return self::$connection;
        }

        // Initialize logger without database connection
        self::$logger = new Logger();

        // Get database driver from .env
        $databaseDriver = $_ENV['DB_DRIVER'];

        self::$logger->log('INFO', "Attempting to connect to database using driver: " . $databaseDriver);

        try {
            if ($databaseDriver === 'mysql') {
                self::$connection = self::connectToMySQL();
            } elseif ($databaseDriver === 'sqlite') {
                self::$connection = self::connectToSQLite();
            } else {
                throw new \Exception("Invalid database driver specified in .env file.");
            }

            if (self::$connection) {
                self::$logger->log('INFO', "Database connected successfully.");
                // Set database connection for logger
                self::$logger->setDbConnection(self::$connection);
            } else {
                throw new \Exception("Failed to connect to the database.");
            }
        } catch (\Exception $e) {
            // Initialize Whoops error handler
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();

            // Display a friendly error message
            throw new \Exception("App is unable to communicate with the database. Please check your database configuration.");
        }

        return self::$connection;
    }

    private static function connectToMySQL()
    {
        $connectionParams = [
            'dbname' => $_ENV['DB'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['PASSWORD'],
            'host' => $_ENV['HOST'],
            'driver' => 'pdo_mysql',
        ];

        try {
            $connection = DriverManager::getConnection($connectionParams);
            self::$logger->log('INFO', "MySQL connection established successfully.");
            return $connection;
        } catch (\Exception $e) {
            self::$logger->log('ERROR', "Failed to connect to MySQL database: " . $e->getMessage());
            return false;
        }
    }

    private static function connectToSQLite()
    {
        // Ensure the database directory exists
        $databaseDir = __DIR__ . '/../../database';
        if (!file_exists($databaseDir)) {
            mkdir($databaseDir, 0777, true);
        }

        $dbPath = $databaseDir . '/sqlite.db';

        $connectionParams = [
            'path' => $dbPath,
            'driver' => 'pdo_sqlite',
        ];

        try {
            $connection = DriverManager::getConnection($connectionParams);
            self::$logger->log('INFO', "SQLite connection established successfully.");
            return $connection;
        } catch (\Exception $e) {
            self::$logger->log('ERROR', "Failed to connect to SQLite database: " . $e->getMessage());
            return false;
        }
    }

    public static function lastInsertId()
    {
        $connection = self::getConnection();
        if ($connection) {
            return $connection->lastInsertId();
        }
        return null;
    }


    public static function queryBuilder()
    {
        $connection = self::getConnection();
        if ($connection) {
            return $connection->createQueryBuilder();
        }
        return null;
    }
}
