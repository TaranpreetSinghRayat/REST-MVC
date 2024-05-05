<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'user:create';

    protected function configure()
    {
        $this->setDescription('Creates a new user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); // Adjusted path
        $dotenv->load();

        $helper = $this->getHelper('question');

        $usernameQuestion = new Question('Please enter the username: ');
        $username = $helper->ask($input, $output, $usernameQuestion);

        $emailQuestion = new Question('Please enter the email: ');
        $email = $helper->ask($input, $output, $emailQuestion);

        $passwordQuestion = new Question('Please enter the password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Hash the password (for demonstration, we'll use a simple hash)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Set up connection parameters based on the DB_DRIVER environment variable
        $connectionParams = [];

        if ($_ENV['DB_DRIVER'] === 'pdo_mysql') {
            $connectionParams = [
                'driver' => 'pdo_mysql', // Specify the driver
                'host' => $_ENV['HOST'],
                'dbname' => $_ENV['DB'],
                'user' => $_ENV['DB_USER'],
                'password' => $_ENV['PASSWORD'],
            ];
        } elseif ($_ENV['DB_DRIVER'] === 'pdo_sqlite') {
            $databaseDir = __DIR__ . '/../../database';
            $connectionParams = [
                'driver' => 'pdo_sqlite', // Specify the driver
                'path' => $databaseDir
            ];
        }

        // Connect to the database using Doctrine DBAL
        $conn = \App\Core\Database::getConnection();

        // Check if the users table exists, if not, create it
        $schemaManager = $conn->createSchemaManager();
        if (!$schemaManager->tablesExist(['users'])) {
            $table = new \Doctrine\DBAL\Schema\Table('users');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('username', 'string', ['length' => 255]);
            $table->addColumn('email', 'string', ['length' => 255]);
            $table->addColumn('password', 'string', ['length' => 255]);
            $table->addColumn('auth_token', 'string', ['length' => 255, 'notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['username']);
            $table->addUniqueIndex(['email']);

            $schemaManager->createTable($table);
            $output->writeln('<info>users table created.</info>');
        }

        // Insert the new user into the database using the query builder
        $conn->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
        ]);

        // Generate an access token for the new user
        $userId = $conn->lastInsertId();
        $authToken = hash('sha256', $userId . time() . rand()); // Assuming generateAccessToken is a defined function

        // Update the user with the auth token using the query builder
        $conn->update('users', [
            'auth_token' => $authToken,
        ], [
            'id' => $userId,
        ]);

        $output->writeln('User created successfully. Token: ' . $authToken);

        return Command::SUCCESS;
    }
}
