<?php

use sinri\ark\io\curl\ArkCurl;
use sinri\ark\io\curl\ArkMultiCurl;


require_once __DIR__ . '/../vendor/autoload.php';

$startTime = microtime(true);
$arkMultiCurl = new ArkMultiCurl();
$x = new ArkCurl();
$arkCurl = $x->prepareToRequestURL('GET', 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2020/33/3301.html')
    ->setAcceptEncoding();
$arkMultiCurl->addCurl($arkCurl);
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$arkMultiCurl->addCurl((new ArkCurl())->prepareToRequestURL('POST', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding());
$resultList = $arkMultiCurl->execute();

var_dump($resultList['resultList']);

$endTime = microtime(true);
echo sprintf("use time: %.3f s".PHP_EOL, $endTime - $startTime);