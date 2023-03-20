<?php

use sinri\ark\io\curl\ArkCurl;
use sinri\ark\io\curl\ArkMultiCurl;


require_once __DIR__ . '/../vendor/autoload.php';

$startTime = microtime(true);
$arkMultiCurl = new ArkMultiCurl();
$x = new ArkCurl();
$arkCurl = $x->prepareToRequestURL('GET', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding();
$arkMultiCurl->addCurl($arkCurl);

$curlList = [];
for ($i = 0; $i < 10; $i++) {
    $curlList[] = (new ArkCurl())->prepareToRequestURL('POST', 'https://www.php.net/manual/zh/function.curl-multi-exec.php')
        ->setAcceptEncoding();
}

$arkMultiCurl->addCurlList($curlList);

$resultList = $arkMultiCurl->execute();

var_dump($resultList['resultList']);

$endTime = microtime(true);
echo sprintf("use time: %.3f s" . PHP_EOL, $endTime - $startTime);