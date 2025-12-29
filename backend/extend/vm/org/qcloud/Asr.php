<?php


namespace vm\org\qcloud;

use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Asr\V20190614\AsrClient;
use TencentCloud\Asr\V20190614\Models\CreateRecTaskRequest;
use TencentCloud\Asr\V20190614\Models\DescribeTaskStatusRequest;
use think\Exception;
use think\facade\Log;

class Asr extends QcloudBase
{
    private $endpoint = 'asr.tencentcloudapi.com';

    /**
     * 创建录音文件识别任务
     * @param string $scene                 --对话场景
     * @param string $callbackUrl           --供腾讯云回调url
     * @param string $recUrl                --录音文件下载url
     * @return integer                      --任务ID，可通过此ID在轮询接口获取识别状态与结果。注意：数据类型为uint64
     * @throws Exception
     */
    public function createRecTask($scene, $recUrl, $callbackUrl)
    {
        try {
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint($this->endpoint);

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new AsrClient($this->cred, "", $clientProfile);

            $req = new CreateRecTaskRequest();

            $params = array(
                'EngineModelType' => $scene,
                'ChannelNum' => 1,
                'ResTextFormat' => 2,
                "SourceType" => 0,
                "CallbackUrl" => $callbackUrl,
                'Url' => $recUrl
            );
            $req->fromJsonString(json_encode($params));
            $resp = $client->CreateRecTask($req);

            return $resp->getData()->getTaskId();
        } catch (TencentCloudSDKException $e) {
            Log::error("FILE:" . $e->getFile() . "\r\nLINE:" . $e->getLine() . "\r\nCODE:" . $e->getErrorCode() . "\r\nMSG:" . $e->getMessage());
            throw new Exception('发生错误');
        }
    }

    /**
     * 查询任务状态
     * @param integer $taskId            --任务ID
     * @return array
     * @throws Exception
     */
    public function queryTaskStatus($taskId)
    {
        try {
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint($this->endpoint);

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new AsrClient($this->cred, "", $clientProfile);

            $req = new DescribeTaskStatusRequest();

            $params = array(
                "TaskId" => $taskId
            );
            $req->fromJsonString(json_encode($params));

            $resp = $client->DescribeTaskStatus($req);
            $taskStatus = $resp->getData();
            $retData = [
                'status' => $taskStatus->StatusStr,   //任务状态，waiting：任务等待，doing：任务执行中，success：任务成功，failed：任务失败。
                'duration' => $taskStatus->AudioDuration,
                'words' => ''
            ];
            if($taskStatus->StatusStr == 'success') {
                foreach ($taskStatus->ResultDetail as $item) {
                    if(!empty($item->FinalSentence)) {
                        $retData['words'] .= $item->FinalSentence . "\n";
                    }
                }
            }
            return $retData;
        } catch (TencentCloudSDKException $e) {
            Log::error("FILE:" . $e->getFile() . "\r\nLINE:" . $e->getLine() . "\r\nCODE:" . $e->getErrorCode() . "\r\nMSG:" . $e->getMessage());
            throw new Exception('发生错误');
        }
    }
}