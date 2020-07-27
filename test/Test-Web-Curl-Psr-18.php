<?php

use GuzzleHttp\Psr7\ServerRequest;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\curl\test\implement\GuzzleCurl;

require_once __DIR__ . '/../vendor/autoload.php';

$psr18 = (new GuzzleCurl(new ArkLogger()));

$request = new ServerRequest(
    'GET',
    'https://www.leqee.com0'
);

$response = $psr18->sendRequest($request);
echo $response->getStatusCode() . PHP_EOL;
echo json_encode($response->getHeaders()) . PHP_EOL;
echo $response->getBody() . PHP_EOL;