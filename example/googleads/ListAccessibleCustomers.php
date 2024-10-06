<?php

namespace Example;

use AtelliTech\Ads\GoogleAds\GoogleAdsService;
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
    'developToken' => $_ENV['DEVELOP_TOKEN'],
    'customerId' => $_ENV['CUSTOMER_ID'],
    'refreshToken' => $_ENV['REFRESH_TOKEN'],
];

$service = GoogleAdsService::create($config);

$rows = $service->listAccessibleCustomers();
var_dump($rows);
