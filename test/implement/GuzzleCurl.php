<?php


namespace sinri\ark\io\curl\test\implement;


use Psr\Http\Message\ResponseInterface;
use sinri\ark\io\curl\ArkCurlWithPsr18;
use function GuzzleHttp\Psr7\parse_response;

class GuzzleCurl extends ArkCurlWithPsr18
{

    public function parseTextToResponse($text): ResponseInterface
    {
        return parse_response($text);
    }
}