<?php

namespace Example;

use AtelliTech\AdHub\GoogleAds\GoogleAdsServiceBuilder;
use Dotenv\Dotenv;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
	'clientId' => $_ENV['GOOGLE_CLIENT_ID'],
	'clientSecret' => $_ENV['GOOGLE_CLIENT_SECRET'],
	'developToken' => $_ENV['GOOGLE_DEVELOP_TOKEN'],
	'apiVersion' => $_ENV['GOOGLE_API_VERSION'],
	'customerId' => $_ENV['GOOGLE_CUSTOMER_ID'],
	'refreshToken' => $_ENV['GOOGLE_REFRESH_TOKEN'],
];

$googleAdsServiceBuilder = new GoogleAdsServiceBuilder($config['clientId'], $config['clientSecret'], $config['developToken'], $config['apiVersion']);
$service = $googleAdsServiceBuilder->create([
		'customerId' => $config['customerId'],
		'refreshToken' => $config['refreshToken']
	]);

$rows = $service->listAccessibleCustomers();
if ($rows === false) {
    var_dump($service->getCustomError());
    exit;
}

foreach($rows as $r) {
    echo "\nStart list account by customerId: {$r['id']}\n";
    $iterator = $service->listCustomers($r['id']);
    if ($iterator === false) {
        echo "\nError: {$r['id']}, {$service->getCustomError()->toString()}";
    } else {
        foreach($iterator as $row) {
            echo sprintf("\nAccount: %s, ID: %s", $row->getCustomer()->getDescriptiveName(), $row->getCustomer()->getId());
        }
    }
}