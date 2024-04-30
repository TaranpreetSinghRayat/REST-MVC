<?php

namespace App\Core;

use AltoRouter;
use App\Core\Interfaces\RequestInterface;
use App\Core\Interfaces\ResponseInterface;
use App\Core\Interfaces\MiddlewareInterface;
use Symfony\Component\Yaml\Yaml;

class Core
{
    protected $request;
    protected $response;
    protected $middlewareStack = [];
    protected $router;
    protected $controller;
    protected $method;
    protected $params;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
        // Initialize AltoRouter
        $router = new AltoRouter();
        // Define your routes here
        require_once APP_ROOT . '/Routes/Routes.php';
        // Match the current request
        $match = $router->match();
        if ($match) {
            // If a route is matched, set the controller and method to be called
            list($controller, $method) = explode('@', $match['target']);
            $this->controller = $controller;
            $this->method = $method;
            $this->params = $match['params'];

            // Check if the controller and method exist
            if (class_exists($this->controller) && method_exists($this->controller, $this->method)) {
                if (is_callable(array(new $this->controller, $this->method))) {
                    call_user_func_array(
                        array(new $this->controller, $this->method),
                        array($match['params'])
                    );
                } else {
                    echo "The method {$this->method} is not defined in {$this->controller}";
                }
            } else {
                // If the controller or method does not exist, set a default response
                $this->response->setStatusCode(404);
                $this->response->setContent('404 Not Found');
            }
        } else {
            // If no route is matched, set a default response
            $this->response->setStatusCode(404);
            $this->response->setContent('404 Not Found');
        }
    }

    public function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewareStack[] = $middleware;
    }

    public function handleRequest()
    {
        $request = $this->request;
        $response = $this->response;

        $next = function ($request, $response) {
            // This is the final handler that gets called if no middleware handles the request
            return $response;
        };

        foreach ($this->middlewareStack as $middleware) {
            $next = function ($request, $response) use ($middleware, $next) {
                return $middleware->process($request, $response, $next);
            };
        }

        // Call the first middleware in the stack
        $response = $next($request, $response);

        // Check if the response has content before sending it
        if ($response->getContent()) {
            // Determine the output format based on the OUTPUT environment variable
            $outputType = $_ENV['OUTPUT'] ?? 'json';

            // Convert the response content to the appropriate format
            switch ($outputType) {
                case 'xml':
                    // Convert to XML using sabre/xml
                    header('Content-Type: application/xml');
                    echo $response->getContent();
                    break;
                case 'json':
                    header('Content-Type: application/json');
                    echo $response->getContent();
                    break;
                case 'yaml':
                    // Convert to YAML using symfony/yaml
                    $yamlContent = Yaml::dump($response->getContent());
                    header('Content-Type: application/x-yaml');
                    echo $yamlContent;
                    break;
                default:
                    // Default to JSON
                    header('Content-Type: application/json');
                    echo $response->getContent();
                    break;
            }
        }
    }
}
