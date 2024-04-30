<?php

namespace App\Core;

use \GuzzleHttp;

class Controller
{

    public function __construct()
    {
    }

    public function model($model)
    {
        $model = ucfirst($model);
        $load = "\App\Models\\{$model}";
        return new $load;
    }
}
