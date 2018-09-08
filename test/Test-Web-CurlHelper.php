<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 22:50
 */

use sinri\ark\io\curl\ArkCurl;

require_once __DIR__ . '/../vendor/autoload.php';
//require_once __DIR__ . '/../autoload.php';

$x = new ArkCurl();
$logger = new \sinri\ark\core\ArkLogger(__DIR__ . '/log', 'curl');
$x->setLogger($logger);
$response = $x->prepareToRequestURL("GET", "https://sinri.cc")
    ->execute();
echo "CODE:" . $x->getResponseCode() . PHP_EOL;
print_r($x->getResponseMeta());
$response = $x->prepareToRequestURL("HEAD", "https://sinri.cc")
    ->execute();
echo "CODE:" . $x->getResponseCode() . PHP_EOL;
print_r($x->getResponseMeta());
print_r($x->getResponseHeaders());
$response = $x->prepareToRequestURL("POST", "https://sinri.cc")
    ->setPostContent(["a" => "b", "c" => "d"])
    ->execute(true);
echo "CODE:" . $x->getResponseCode() . PHP_EOL;
print_r($x->getResponseMeta());