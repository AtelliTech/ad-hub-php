<?php

namespace Example;

use AtelliTech\AdHub\GoogleAds\GoogleAdsServiceBuilder;
use Dotenv\Dotenv;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
	'clientId' => $_ENV['CLIENT_ID'],
	'clientSecret' => $_ENV['CLIENT_SECRET'],
	'developToken' => $_ENV['DEVELOP_TOKEN'],
	'apiVersion' => $_ENV['API_VERSION'],
	'customerId' => $_ENV['CUSTOMER_ID'],
	'refreshToken' => $_ENV['REFRESH_TOKEN'],
];

$googleAdsServiceBuilder = new GoogleAdsServiceBuilder($config['clientId'], $config['clientSecret'], $config['developToken'], $config['apiVersion']);
$service = $googleAdsServiceBuilder->create([
		'customerId' => $config['customerId'],
		'refreshToken' => $config['refreshToken']
	]);

$rows = $service->listAccessibleCustomers();
foreach($rows as $r) {
    $custId = $r['id'];
    $resourceName = $r['resource_name'];
    $service = $googleAdsServiceBuilder->create([
        'customerId' => $custId,
        'refreshToken' => $config['refreshToken']
    ]);

    try {
        $customer = $service->getCustomer($custId);
    } catch (\Throwable $e) {
        echo "\nGet Customer $custId, Error: " . $e->getMessage() . "\n";
        continue;
    }

    echo sprintf("\nCustomer: %s, ID: %s", $customer->getDescriptiveName(), $customer->getId());
    $stream = $service->listCustomerClients($custId);

    // list customer clients by stream
    foreach($stream->iterateAllElements() as $r) {
        echo sprintf("\n\tCustomer($custId)'s Client: %s, ID: %s", $r->getCustomerClient()->getDescriptiveName(), $r->getCustomerClient()->getId());
    }
}