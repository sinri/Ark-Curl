<?php
require_once __DIR__ . '/../vendor/autoload.php';

$psr18 = (new \sinri\ark\io\curl\test\implement\GuzzleCurl(new \sinri\ark\core\ArkLogger()));

$request = new \GuzzleHttp\Psr7\ServerRequest(
    'GET',
    'https://www.leqee.com0'
);

$response = $psr18->sendRequest($request);
echo $response->getStatusCode() . PHP_EOL;
echo json_encode($response->getHeaders()) . PHP_EOL;
echo $response->getBody() . PHP_EOL;