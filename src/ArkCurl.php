<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/9/7
 * Time: 00:07
 */

namespace sinri\ark\io\curl;


use sinri\ark\core\ArkLogger;

class ArkCurl
{
    protected $method;
    protected $url;
    protected $queryList;
    protected $postData;
    protected $headerList;
    protected $cookieList;
    protected $logger;
    protected $optionList;
    protected $responseMeta;
    protected $responseHeaders;
    protected $errorNo;
    protected $errorMessage;
    private $needParseHeader;
    private $takePostDataAsJson = false;

    /**
     * ArkCurl constructor.
     * @param null|ArkLogger $logger @since 2.0.2
     */
    public function __construct($logger = null)
    {
        if ($logger === null) $logger = ArkLogger::makeSilentLogger();
        $this->logger = $logger;
        $this->needParseHeader = false;
        $this->responseMeta = null;
        $this->responseHeaders = null;
        $this->resetParameters();
    }

    protected function resetParameters()
    {
        $this->method = "GET";
        $this->url = "";
        $this->queryList = [];
        $this->postData = "";
        $this->headerList = [];
        $this->cookieList = [];
        $this->optionList = [];
    }

    /**
     * @return int
     * @since 2.0.3
     */
    public function getErrorNo()
    {
        return $this->errorNo;
    }

    /**
     * @return string
     * @since 2.0.3
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     * @since 1.2 For HEAD, add HEADER fetch
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * @return array
     * @since 1.2 For HEAD, add HEADER fetch
     */
    public function getResponseMeta()
    {
        return $this->responseMeta;
    }

    /**
     * @return int
     * @since 1.2 For HEAD, add HEADER fetch
     */
    public function getResponseCode()
    {
        return $this->responseMeta['http_code'];
    }

    /**
     * @param ArkLogger $logger
     * @return ArkCurl @since 2.0.2
     */
    public function setLogger(ArkLogger $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param int $option definition of CURL OPTION cluster
     * @param mixed $value
     * @return ArkCurl @since 1.3 this method could be chained
     */
    public function setCURLOption($option, $value)
    {
        $this->optionList[$option] = $value;
        return $this;
    }

    /**
     * @param $method
     * @param $url
     * @return $this
     */
    public function prepareToRequestURL($method, $url)
    {
        $this->method = $method;
        $this->url = $url;
        $this->curlInstance = curl_init();
        return $this;
    }

    /**
     * @param $queryName
     * @param $queryValue
     * @return $this
     */
    public function setQueryField($queryName, $queryValue)
    {
        $this->queryList[$queryName] = $queryValue;
        return $this;
    }

    /**
     * @param array|string $data
     * @return $this
     */
    public function setPostContent($data)
    {
        $this->postData = $data;
        return $this;
    }

    /**
     * @param $fieldName
     * @param $fieldValue
     * @return $this
     */
    public function setPostFormField($fieldName, $fieldValue)
    {
        if (!is_array($this->postData)) {
            $this->postData = [];
        }
        $this->postData[$fieldName] = $fieldValue;
        return $this;
    }

    /**
     * @param string $headerName
     * @param string $headerValue
     * @return $this
     */
    public function setHeader($headerName, $headerValue)
    {
        $this->headerList[$headerName] = $headerValue;
        if (strtolower($headerName) === 'content-type') {
            if (stripos($headerValue, 'application/json') === 0) {
                $this->takePostDataAsJson = true;
            }
        }
        return $this;
    }

    /**
     * A syntax sugar FOR json application
     * @return $this
     * @since 2.1
     */
    public function setContentTypeAsJsonInHeader()
    {
        return $this->setHeader('Content-Type', 'application/json');
    }

    /**
     * @param string $value
     * @return $this
     * @since 2.1.2
     *
     * @see https://www.php.net/manual/zh/function.curl-setopt.php#:~:text=Daemon%E5%A5%97%E6%8E%A5%E5%AD%97%E3%80%82-,CURLOPT_ENCODING,-HTTP%E8%AF%B7%E6%B1%82%E5%A4%B4
     * About CURLOPT_ENCODING:
     *  Set the value of HTTP Header `Accept-Encoding`, let the response be uncompressed.
     *  Supported value: "identity", "deflate", "gzip".
     *  If "", send all supported.
     *  Added since cURL 7.10.
     */
    public function setAcceptEncoding($value = '')
    {
        return $this->setCURLOption(CURLOPT_ENCODING, $value);
    }

    /**
     * @param $cookieName
     * @param $cookieValue
     * @return $this
     */
    public function setCookie($cookieName, $cookieValue)
    {
        $this->cookieList[] = urlencode($cookieName) . "=" . urlencode($cookieValue);
        return $this;
    }

    /**
     * @var false|resource as of PHP 8, resource would be \CurlHandle
     */
    private $curlInstance;

    public function configureCurlInstance()
    {
        $this->errorNo = 0;
        $this->errorMessage = '';

        $this->needParseHeader = false;
        $this->responseMeta = null;
        $this->responseHeaders = null;

        curl_setopt($this->curlInstance, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curlInstance, CURLOPT_CUSTOMREQUEST, $this->method);
        $use_body = in_array($this->method, ["POST", "PUT"]);
        if ($use_body) {
            curl_setopt($this->curlInstance, CURLOPT_POST, 1);
            if ($this->takePostDataAsJson) {
                $this->postData = json_encode($this->postData);
            } else {
                // if postData is raw string, leave it simply original
                if (!is_scalar($this->postData)) {
                    $this->postData = http_build_query($this->postData);
                }
            }

            curl_setopt($this->curlInstance, CURLOPT_POSTFIELDS, $this->postData);
        }

        $query_string = http_build_query($this->queryList);
        if (!empty($query_string)) {
            $this->url .= "?" . $query_string;
        }
        curl_setopt($this->curlInstance, CURLOPT_URL, $this->url);

        if (!empty($this->headerList)) {
            $headers = [];
            foreach ($this->headerList as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($this->curlInstance, CURLOPT_HTTPHEADER, $headers);
        }
        if (!empty($this->cookieList)) {
            curl_setopt($this->curlInstance, CURLOPT_COOKIE, implode(';', $this->cookieList));
        }
        curl_setopt($this->curlInstance, CURLOPT_SSL_VERIFYPEER, false);

        $this->logger->info(
            "CURL-{$this->method}-Request",
            ["URL" => $this->url, "HEADER" => $this->headerList, "BODY" => $this->postData]
        );

        // @since 1.1 For HEAD, the default is no body, you can override in option list
        // @since 2.1 refine processing
        if ($this->method === 'HEAD') {
            $this->setCURLOption(CURLOPT_HEADER, true)
                ->setCURLOption(CURLOPT_NOBODY, true);
        }

        // inject options
        if (!empty($this->optionList)) {

            // since 2.1.2, default add support for accept encoding all, but
            $this->setAcceptEncoding();

            foreach ($this->optionList as $option => $value) {
                curl_setopt($this->curlInstance, $option, $value);
                // @since 1.2 For HEAD, add HEADER fetch
                if ($option === CURLOPT_HEADER && $value === true) {
                    $this->needParseHeader = true;
                }
            }
        }
        return $this;
    }

    protected function call()
    {
        $response = curl_exec($this->curlInstance);

        $this->forMultiExecuteFinish($this, $this->curlInstance, $response);

        curl_close($this->curlInstance);

        return $response;
    }

    public static function forMultiExecuteFinish($arkCurl, $curl, $response)
    {
        // @since 1.2 For HEAD, add HEADER fetch
        $arkCurl->responseMeta = curl_getinfo($curl);
        if ($arkCurl->needParseHeader) {
            $lines = preg_split("/[\r\n]+/", $response);
            $arkCurl->responseHeaders = [];
            foreach ($lines as $line) {
                if (preg_match('/([^:]+): (.*)$/', $line, $matches)) {
                    $arkCurl->responseHeaders[$matches[1]] = $matches[2];
                }
            }
        }

        if ($response === false) {
            $arkCurl->logger->warning("CURL-{$arkCurl->method}-Response", ['response' => $response]);
        } elseif ($response === true) {
            $arkCurl->logger->info("CURL-{$arkCurl->method}-Response", ['response' => $response]);
        } else {
            $arkCurl->logger->info("CURL-{$arkCurl->method}-Response as following: " . PHP_EOL . $response);
        }

        $arkCurl->errorNo = curl_errno($arkCurl->curlInstance);
        $arkCurl->errorMessage = curl_error($arkCurl->curlInstance);

        $arkCurl->resetParameters();
    }

    /**
     * @return string|bool
     * @since changed in 2.2.0
     */
    public function execute()
    {
        return $this->configureCurlInstance()->call();
    }

    /**
     * @return false|resource
     */
    public function getCurlInstance()
    {
        return $this->curlInstance;
    }
}