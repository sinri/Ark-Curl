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
    private $needParseHeader;

    public function __construct()
    {
        $this->logger = ArkLogger::makeSilentLogger();
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
     * @since 1.2 For HEAD, add HEADER fetch
     * @return mixed
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * @since 1.2 For HEAD, add HEADER fetch
     * @return array
     */
    public function getResponseMeta()
    {
        return $this->responseMeta;
    }

    /**
     * @since 1.2 For HEAD, add HEADER fetch
     * @return int
     */
    public function getResponseCode()
    {
        return $this->responseMeta['http_code'];
    }

    /**
     * @param ArkLogger $logger
     */
    public function setLogger(ArkLogger $logger)
    {
        $this->logger = $logger;
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
     * @param $headerName
     * @param $headerValue
     * @return $this
     */
    public function setHeader($headerName, $headerValue)
    {
        $this->headerList[$headerName] = $headerValue;
        return $this;
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
     * @param bool $takePostDataAsJson
     * @return string|bool
     */
    public function execute($takePostDataAsJson = false)
    {
        $this->needParseHeader = false;
        $this->responseMeta = null;
        $this->responseHeaders = null;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        $use_body = in_array($this->method, ["POST", "PUT"]);
        if ($use_body) {
            curl_setopt($ch, CURLOPT_POST, 1);

            if ($takePostDataAsJson) {
                $this->headerList['Content-Type'] = 'application/json';
                $this->postData = json_encode($this->postData);
            } else {
                // if postData is raw string, leave it simply original
                if (!is_scalar($this->postData)) {
                    $this->postData = http_build_query($this->postData);
                }
            }

            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
        }

        $query_string = http_build_query($this->queryList);
        if (!empty($query_string)) {
            $this->url .= "?" . $query_string;
        }
        curl_setopt($ch, CURLOPT_URL, $this->url);

        if (!empty($this->headerList)) {
            $headers = [];
            foreach ($this->headerList as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (!empty($this->cookieList)) {
            curl_setopt($ch, CURLOPT_COOKIE, implode(';', $this->cookieList));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $this->logger->info(
            "CURL-{$this->method}-Request",
            ["URL" => $this->url, "HEADER" => $this->headerList, "BODY" => $this->postData]
        );

        // @since 1.1 For HEAD, the default is no body, you can override in option list
        if ($this->method === 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
            // @since 1.2 For HEAD, add HEADER fetch
            curl_setopt($ch, CURLOPT_HEADER, true);
            $this->needParseHeader = true;
        }

        // inject options
        if (!empty($this->optionList)) {
            foreach ($this->optionList as $option => $value) {
                curl_setopt($ch, $option, $value);
                // @since 1.2 For HEAD, add HEADER fetch
                if ($option === CURLOPT_HEADER && $value === true) {
                    $this->needParseHeader = true;
                }
            }
        }

        $response = curl_exec($ch);

        // @since 1.2 For HEAD, add HEADER fetch
        $this->responseMeta = curl_getinfo($ch);
        if ($this->needParseHeader) {
            $lines = preg_split("/[\r\n]+/", $response);
            $this->responseHeaders = [];
            foreach ($lines as $line) {
                if (preg_match('/([^:]+): (.*)$/', $line, $matches)) {
                    $this->responseHeaders[$matches[1]] = $matches[2];
                }
            }
        }

        $this->logger->info("CURL-{$this->method}-Response", [$response]);

        curl_close($ch);

        $this->resetParameters();

        return $response;
    }
}