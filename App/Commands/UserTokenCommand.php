<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Core\JwtHandler;
use App\Core\Database;
use Symfony\Component\Console\Question\Question;


class UserTokenCommand extends Command
{
    protected static $defaultName = 'user:token';

    protected function configure()
    {
        $this->setDescription('Generate and print a user token based on the middleware type.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check the middleware type
        $middlewareType = $this->getMiddlewareType();
        // Ask for user identifier (ID, email, or username)
        $helper = $this->getHelper('question');
        $question = new Question('Please enter the user ID, email, or username: ');
        $identifier = $helper->ask($input, $output, $question);

        if ($middlewareType === 'jwt') {
            if (!isset($_ENV['JWT_SECRET_KEY'])) {
                // Output a message to run the console command
                $output->writeln("JWT_SECRET_KEY is not set. Please run the console command: php console token:secret");
                $output->writeln("Then try again.");
                return Command::FAILURE; // Exit the command with a failure status
            }


            // Generate token from JwtHandler
            $jwtHandler = new JwtHandler($_ENV['JWT_SECRET_KEY'], $_ENV['URL_ROOT'], ['api']); // Adjust the instantiation if necessary
            $token = $jwtHandler->generateToken(['user' => $identifier]);
            $output->writeln("Token: $token");
        } elseif ($middlewareType === 'bearer') {
            // Fetch auth_token and output
            $token = $this->fetchAuthTokenFromDb($identifier);
            $output->writeln("Token: $token");
        } else {
            $output->writeln("Unknown middleware type.");
        }

        return Command::SUCCESS;
    }

    private function getMiddlewareType()
    {
        // Retrieve the middleware type from the environment variable
        $middlewareType = $_ENV['MIDDLEWARE_TYPE'];

        // Check if the middleware type is set
        if ($middlewareType === false) {

            throw new \Exception('Middleware type is not set.');
        }

        return $middlewareType;
    }

    private function fetchAuthTokenFromDb($identifier)
    {
        $db = Database::getConnection();
        $queryBuilder = $db->createQueryBuilder();

        $column = is_numeric($identifier) ? 'id' : (filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username');

        // Example query to fetch a user's token
        $queryBuilder->select('auth_token')
            ->from('users')
            ->where("$column = :identifier")
            ->setParameter('identifier', $identifier);

        $result = $queryBuilder->executeQuery()->fetchAssociative();

        return $result['auth_token'];
    }
}
