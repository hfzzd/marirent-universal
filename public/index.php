<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../vendor/framework/framework/src/Illuminate/Foundation/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
