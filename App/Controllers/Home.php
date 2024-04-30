<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class Home extends \App\Core\Controller
{
    private $request, $response;

    public function __construct()
    {
        global $request, $response;
        $this->request = $request;
        $this->response = $response;
    }

    public function index()
    {
        $data = "Hello, world!";
        $this->response->setContent($data);

        return $this->response;
    }

    public function test()
    {
        $records = [];
        for ($i = 1; $i <= 300000; $i++) {
            $records[] = [
                'id' => $i,
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'created_at' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
            ];
        }
        $this->response->setContent($records);
    }
}
