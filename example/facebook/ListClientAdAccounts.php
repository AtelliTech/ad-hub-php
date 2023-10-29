<?php

namespace Example;

use AtelliTech\AdHub\Facebook\FacebookServiceBuilder;
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
	'apiVersion' => $_ENV['API_VERSION']
];

$builder = new FacebookServiceBuilder($config['clientId'], $config['clientSecret'], $config['apiVersion']);
$service = $builder->create([
		'accessToken' => $_ENV['ACCESS_TOKEN']
	]);

$after = null;
$businesses = [];
while(1) {
    if ($after) {
        $params = ['after' => $after];
    } else {
        $params = [];
    }

    $result = $service->listAccessibleBusinesses($params);
    if (isset($result['error'])) {
        echo $result['error']['message'];
        exit;
    }

    $businesses = array_merge($businesses, $result['data']);
    $paging = $result['paging'] ?? null;
    if ($paging) {
        $cursors = $paging['cursors'] ?? null;
        if ($cursors) {
            $after = $cursors['after'] ?? null;
        }
    }

    if (count($result['data']) < 25) {
        break;
    }
}

foreach ($businesses as $business) {
    $result = $service->listClientAdAccounts($business['id'], ['fields'=>'id,account_id,name,currency,account_status,adspixels{id,name}']);
    if (isset($result['error'])) {
        echo $result['error']['message'];
        exit;
    }

    var_dump($result);
    exit;
}