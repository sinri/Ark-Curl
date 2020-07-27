<?php


namespace sinri\ark\io\curl;


use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class ArkCurlClientException
 * @package sinri\ark\io\curl
 * @since 2.1
 */
class ArkCurlClientException extends \Exception implements ClientExceptionInterface
{

}