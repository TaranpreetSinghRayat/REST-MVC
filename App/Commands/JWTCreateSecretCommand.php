<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

class JWTCreateSecretCommand extends Command
{
    protected static $defaultName = 'token:secret';

    protected function configure()
    {
        $this->setDescription('Creates a new user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../'); // Adjusted path
        $dotenv->load();

        $secretKey = base64_encode(random_bytes(32));
        $output->writeln('Key Generated: ' . $secretKey);

        $helper = $this->getHelper('question');

        $updateENV = new Question('Would you like to update the .env file with secret (y/n): ');
        $updateENVans = $helper->ask($input, $output, $updateENV);

        if ($updateENVans == 'y' || $updateENVans == 'Y') {
            $envFilePath = (dirname(__DIR__)) . '/.env';
            $envFileContent = file_get_contents($envFilePath);
            // Check if the JWT_SECRET_KEY already exists
            if (strpos($envFileContent, 'JWT_SECRET_KEY') !== false) {
                $envFileContent = preg_replace('/^JWT_SECRET_KEY=.*/m', "JWT_SECRET_KEY='$secretKey'", $envFileContent);
            } else {
                $envFileContent .= "\nJWT_SECRET_KEY='$secretKey'";
            }
            file_put_contents($envFilePath, $envFileContent);
            $output->writeln('JWT_SECRET_KEY has been updated in the .env file.');
        } else {
            $output->writeln('Please copy and set JWT_SECRET_KEY=' . $secretKey);
        }

        return Command::SUCCESS;
    }
}
