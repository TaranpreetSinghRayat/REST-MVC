<?php

namespace App\Core;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\TableNotFoundException;

class Logger
{
    private $logFilePath;
    private $dbConnection;
    private $logLevels = [
        'DEBUG' => 1,
        'INFO' => 2,
        'WARN' => 3,
        'ERROR' => 4,
        'FATAL' => 5,
    ];
    private $currentLogLevel = 'DEBUG';
    private $logTableName = 'app_logs'; // Adjust the table name as needed

    public function __construct($logFileName = 'app.log', $logLevel = 'DEBUG', $dbConnection = null)
    {
        $this->currentLogLevel = $logLevel;
        $this->dbConnection = $dbConnection;

        if ($this->dbConnection) {
            $this->ensureLogTableExists();
        } else {
            $logDir = __DIR__ . '/../../logger';
            if (!file_exists($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $this->logFilePath = $logDir . '/' . $logFileName;
        }
    }

    public function log($level, $message, $context = [])
    {
        if ($this->logLevels[$level] < $this->logLevels[$this->currentLogLevel]) {
            return; // Skip logging if the message level is lower than the current log level
        }

        if ($this->dbConnection) {
            $this->logToDatabase($level, $message, $context);
        } else {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => $level,
                'message' => $message,
                'context' => $context,
            ];
            $logMessage = json_encode($logEntry) . PHP_EOL;
            file_put_contents($this->logFilePath, $logMessage, FILE_APPEND);
        }
    }

    private function logToDatabase($level, $message, $context)
    {
        $queryBuilder = $this->dbConnection->createQueryBuilder();
        $queryBuilder->insert($this->logTableName)
            ->values([
                'level' => ':level',
                'message' => ':message',
                'context' => ':context',
                'created_at' => ':created_at',
            ])
            ->setParameters([
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

        $queryBuilder->execute();
    }

    private function ensureLogTableExists()
    {
        try {
            $schemaManager = $this->dbConnection->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            if (!in_array($this->logTableName, $tables)) {
                // Table does not exist, create it
                $this->createLogTable();
            }
        } catch (\Exception $e) {
            // Handle the exception, e.g., log the error or throw a new exception
            $this->log('ERROR', "Failed to check for log table existence: " . $e->getMessage());
        }
    }

    private function createLogTable()
    {
        $schemaManager = $this->dbConnection->createSchemaManager();
        $table = new \Doctrine\DBAL\Schema\Table($this->logTableName);
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('level', 'string', ['length' => 10]);
        $table->addColumn('message', 'text');
        $table->addColumn('context', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->setPrimaryKey(['id']);

        $schemaManager->createTable($table);
    }

    public function setLogLevel($level)
    {
        if (array_key_exists($level, $this->logLevels)) {
            $this->currentLogLevel = $level;
        } else {
            throw new \InvalidArgumentException("Invalid log level: {$level}");
        }
    }

    public function setDbConnection(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
        $this->ensureLogTableExists();
    }

    public function setLogFilePath($logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }
}
