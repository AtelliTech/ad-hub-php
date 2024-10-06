<?php

namespace Example;

use AtelliTech\Ads\Facebook\FacebookService;
use Dotenv\Dotenv;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__.'/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
    'clientId' => $_ENV['CLIENT_ID'],
    'clientSecret' => $_ENV['CLIENT_SECRET'],
    'version' => $_ENV['API_VERSION'],
    'accessToken' => $_ENV['ACCESS_TOKEN'],
];

$service = FacebookService::create($config);
$result = $service->listAccessibleBusinesses();
var_dump($result);
