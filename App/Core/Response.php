<?php

namespace App\Core;

use App\Core\Interfaces\ResponseInterface;
use Laminas\Json\Json;
use Sabre\Xml\Service;
use Symfony\Component\Yaml\Yaml;

class Response implements ResponseInterface
{
    protected $statusCode;
    protected $headers;
    protected $content;

    public function __construct(int $statusCode = 200, array $headers = [], string $content = '')
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->content = $content;
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }


    public function setContent($content): void
    {
        // Determine the output format based on the OUTPUT environment variable
        $format = $_ENV['OUTPUT'] ?? 'json';

        switch ($format) {
            case 'xml':
                //@Todo
                if (is_array($content) || is_object($content)) {
                    $xmlService = new Service();
                    $finalArray = [];
                    // Set the output options to exclude the XML declaration (if not needed)
                    $xmlService->outputOptions = LIBXML_NOXMLDECL;
                    // Manually add the XML declaration at the start
                    $finalXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

                    var_dump($content);
                    foreach ($content as $k => $v) {
                        foreach ($v as $key => $value) {
                            // Print the key and value
                            $itemArray[$key] = $value;
                            $xml = $xmlService->write('root-' . $k, $itemArray);
                        }
                        $finalXml .= $xml;
                    }
                    var_dump($finalXml);
                    $this->content = $finalXml;
                } else {
                    $this->content = $xmlService->write('{' . $_ENV['URL_ROOT'] . '}response', $content);
                }
                break;
            case 'yaml':
                // Convert the content to YAML using symfony/yaml
                $this->content = Yaml::dump($content);
                break;
            case 'json':
            default:
                // Convert the content to JSON using laminas/laminas-json
                $this->content = json_encode($content);
                break;
        }
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
