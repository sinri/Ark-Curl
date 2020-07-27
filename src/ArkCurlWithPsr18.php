<?php


namespace sinri\ark\io\curl;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use sinri\ark\core\ArkLogger;
use function GuzzleHttp\Psr7\parse_response;

class ArkCurlWithPsr18 implements ClientInterface
{
    protected $logger;

    public function __construct(ArkLogger $logger)
    {
        $this->logger = $logger;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $curl = (new ArkCurl($this->logger))
            ->prepareToRequestURL($request->getMethod(), $request->getUri()->__toString())
            ->setCURLOption(CURLOPT_HEADER, 1);

        $headers = $request->getHeaders();
        foreach ($headers as $headerName => $headerValueList) {
            foreach ($headerValueList as $value) {
                $curl->setHeader($headerName, $value);
            }
        }

        $curl->setPostContent($request->getBody()->__toString());

        $contentType = $request->getHeaderLine('content-type');
        $result = $curl->execute((stripos($contentType, 'application/json') === 0));

        return parse_response($result);
    }
}