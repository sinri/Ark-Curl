<?php


namespace sinri\ark\io\curl;


use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use sinri\ark\core\ArkLogger;

/**
 * Class ArkCurlWithPsr18
 * @package sinri\ark\io\curl
 * @since 2.1
 */
abstract class ArkCurlWithPsr18 implements ClientInterface
{
    protected $logger;

    public function __construct(ArkLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws ArkCurlClientException If an error happens while processing the request.
     */
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

        if ($result === false) {
            throw new ArkCurlClientException($curl->getErrorMessage(), $curl->getErrorNo());
        }

        return $this->parseTextToResponse($result);
    }

    /**
     * @param string $text
     * @return ResponseInterface
     */
    abstract public function parseTextToResponse($text): ResponseInterface;
}