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
        return $this->response;
    }
}
