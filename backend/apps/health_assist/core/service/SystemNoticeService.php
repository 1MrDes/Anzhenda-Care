<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\SubscribeMessageTemplate;
use apps\health_assist\core\model\SystemNotice;
use apps\health_assist\core\model\UserPlatform;
use apps\health_assist\core\model\UserSystemNotice;
use vm\com\BaseService;
use vm\com\logic\WechatMiniAppLogic;

class SystemNoticeService extends BaseService
{
    /**
     * @return UserSystemNoticeService
     */
    private function getUserSystemNoticeService()
    {
        return service('UserSystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return UserPlatformService
     */
    private function getUserPlatformService()
    {
        return service('UserPlatform', SERVICE_NAMESPACE);
    }

    /**
     * @return SiteConfigService
     */
    private function getSiteConfigService()
    {
        return service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return SubscribeMessageTemplateService
     */
    private function getSubscribeMessageTemplateService()
    {
        return service('SubscribeMessageTemplate', SERVICE_NAMESPACE);
    }

    /**
     * @return WechatMiniAppLogic
     */
    private function getWxMiniLogic()
    {
        static $logic;
        if($logic !== null) {
            return $logic;
        }
        $siteConfigService = $this->getSiteConfigService();
        $logic = logic('WechatMiniApp', '\vm\com\logic\\');
        $logic->init([
            'app_id' => $siteConfigService->getValueByCode('weapp_app_id'),
            'app_secret' => $siteConfigService->getValueByCode('weapp_app_secret'),
            'app_token' => $siteConfigService->getValueByCode('weapp_app_token'),
            'encode_aeskey' => $siteConfigService->getValueByCode('weapp_app_encoding_aeskey'),
        ]);
        return $logic;
    }

    /**
     * @return SystemNotice
     */
    protected function getModel()
    {
        return new SystemNotice();
    }

    public function create(array $data)
    {
        $data['publish_time'] = time();
        return parent::create($data);
    }

    public function onPushSingleNotice()
    {
        $params = [
            'type' => SystemNotice::TYPE_SINGLE,
            'status' => SystemNotice::STATUS_WAIT_PULL
        ];
        $res = $this->pageListByParams($params, 30, ['page' => 1], ['id' => 'ASC']);
        if(!empty($res['data'])) {
            $wxMiniLogic = $this->getWxMiniLogic();
            $userSystemNoticeService = $this->getUserSystemNoticeService();
            $userPlatformService = $this->getUserPlatformService();
            $subscribeMessageTemplateService = $this->getSubscribeMessageTemplateService();
            $weappId = $this->getSiteConfigService()->getValueByCode('weapp_app_id');
            $wechatConfig = config('wechat');
            $msgTemplateId = $wechatConfig['mp_msg_templates']['job_progress_notice'];
            $weappTemplateId = $wechatConfig['msg_templates']['job_notice'];
            $logFile = DOC_PATH . 'runtime/log/daemon/'.date('Ymd').'.log';
            foreach ($res['data'] as $item) {
                $userSystemNoticeService->create([
                    'system_notice_id' => $item['id'],
                    'recipient_id' => $item['recipient_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                $this->updateByPk([
                    'id' => $item['id'],
                    'status' => SystemNotice::STATUS_PULLED
                ]);
                $urls = json_decode($item['url'], true);
                // 发送小程序消息
                try {
                    $userPlatform = $userPlatformService->info([
                        'user_id' => $item['recipient_id'],
                        'platform' => UserPlatform::PLATFORM_WX_MINI
                    ]);
                    if($userPlatform) {
                        $msg = [
                            "touser" => $userPlatform['open_id'],
                            "template_id" => $weappTemplateId,
                            "page" => $urls['weapp'],
                            'data' => [
                                'thing7' => [
                                    'value' => $item['title']
                                ],
                                'thing12' => [
                                    'value' => $item['content']
                                ],
                                'thing11' => [
                                    'value' => 'N/A'
                                ]
                            ]
                        ];
                        if($wxMiniLogic->sendSubscribeMessage($msg)) {
                            $subscribeMessageTemplateService->deleteEarliestTemplateIdByOpenid($userPlatform['open_id'], $weappTemplateId, SubscribeMessageTemplate::TYPE_ONCE);
                        } else {
                            writeLog('errcode:' . $wxMiniLogic->getErrCode() . '  errmsg:' . $wxMiniLogic->getErrMsg(), $logFile);
                        }
                    }
                } catch (\Exception $e) {
                    $log = 'FILE:' . $e->getFile() . "\r\n" . 'LINE:' . $e->getLine(). "\r\n" . 'MSG:' . $e->getMessage();
                    writeLog($log, $logFile);
                }
                //TODO: 发送app消息
                try {

                } catch (\Exception $e) {
                    $log = 'FILE:' . $e->getFile() . "\r\n" . 'LINE:' . $e->getLine(). "\r\n" . 'MSG:' . $e->getMessage();
                    writeLog($log, $logFile);
                }
            }
        }
    }
}