<?php
/**
 * Created by PhpStorm.
 * User: zrincet
 * Date: 2023/3/20
 * Time: 15:47
 */

namespace sinri\ark\io\curl;


use sinri\ark\core\ArkLogger;

/**
 * @since 2.2.0
 */
class ArkMultiCurl
{
    /**
     * @var ArkCurl[]
     */
    protected $arkCurlList;
    /**
     * @var ArkLogger|null
     */
    protected $logger;

    /**
     * ArkCurl constructor.
     * @param ArkLogger|null $logger @since 2.1.3
     */
    public function __construct(ArkLogger $logger = null)
    {
        if ($logger === null) $logger = ArkLogger::makeSilentLogger();
        $this->logger = $logger;
    }

    /**
     * @param ArkCurl $arkCurl
     * @since 2.1.3
     */
    public function addCurl(ArkCurl $arkCurl)
    {
        $this->arkCurlList[] = $arkCurl;
    }


    /**
     * @return array
     * @since 2.1.3
     */
    public function execute(): array
    {
        $this->logger->info("MultiCurl Execute, " . count($this->arkCurlList) . " curl(s)");
        $mh = curl_multi_init();
        $curlList = [];
        foreach ($this->arkCurlList as $arkCurl) {
            $arkCurl->configureCurlInstance();
            $ch = $arkCurl->getCurlInstance();
            curl_multi_add_handle($mh, $ch);
            $curlList[] = $ch;
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $resultList = [];
        foreach ($curlList as $index => $curl) {
            $result = curl_multi_getcontent($curl);
            $arkCurl = $this->arkCurlList[$index];
            $arkCurl->forMultiExecuteFinish($curl, $result);
            $resultList[] = $result;
            curl_multi_remove_handle($mh, $curl);
        }
        curl_multi_close($mh);
        $this->logger->info("MultiCurl Execute Finished, " . count($this->arkCurlList) . " curl(s)");
        return array(
            'resultList' => $resultList,
            'arkCurlList' => $this->arkCurlList
        );
    }

}