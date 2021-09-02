<?php

use sinri\ark\io\curl\ArkCurl;

require_once __DIR__ . '/../vendor/autoload.php';

$x = new ArkCurl();
$response = $x->prepareToRequestURL('GET', 'http://www.stats.gov.cn/tjsj/tjbz/tjyqhdmhcxhfdm/2020/33/3301.html')
    ->setAcceptEncoding()
    ->execute();
var_dump($response);

$response = $x->prepareToRequestURL('GET', 'https://sinri.cc/frontend/index.html')
    ->setAcceptEncoding()
    ->execute();
var_dump($response);