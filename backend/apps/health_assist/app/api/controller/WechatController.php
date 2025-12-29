<?php


namespace apps\health_assist\app\api\controller;


use think\facade\Log;
use apps\health_assist\app\Request;
use vm\com\logic\WechatMiniAppLogic;

class WechatController extends BaseHealthAssistApiController
{
    /**
     * @var WechatMiniAppLogic
     */
    protected $wxMiniLogic;

    protected function init()
    {
        parent::init();
        $siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
        $this->wxMiniLogic = logic('WechatMiniApp', '\vm\com\logic\\');
        $this->wxMiniLogic->init([
            'app_id' => $siteConfigService->getValueByCode('weapp_app_id'),
            'app_secret' => $siteConfigService->getValueByCode('weapp_app_secret'),
            'app_token' => $siteConfigService->getValueByCode('weapp_app_token'),
            'encode_aeskey' => $siteConfigService->getValueByCode('weapp_app_encoding_aeskey'),
        ]);
    }

    public function decode_scene(Request $request)
    {
        $scene = $request->param('scene', '');
        $data = $this->wxMiniLogic->decodeScene($scene);
        return $this->success($data);
    }

    public function feed(Request $request)
    {
        $appid = $request->route('appid');
        $params = $request->param();
        $postData = @file_get_contents('php://input');
        $postData = xmlToArray($postData);
        Log::info('params:' . json_encode($params));
        Log::info('postData:' . json_encode($postData));

        $timestamp = $params['timestamp'];
        $nonce = $params['nonce'];
        $signature = $params['signature'];

        if($this->wxMiniLogic->checkSignature($signature, $nonce, $timestamp)) {
            if(isset($params['echostr'])) {
                $result = $params['echostr'];
            } else {
                $encryptType = $params['encrypt_type'];
                $msgSignature = $params['msg_signature'];
                $openid = $params['openid'];
                $msg = $this->wxMiniLogic->receiveMsgFeed($postData, $timestamp, $nonce, $encryptType, $msgSignature);
                $result = $this->msgHanlder($msg);
            }
        } else {
            $result = 'fail';
        }

        if (ob_get_level() == 0) {
            ob_start();
        }
        ob_implicit_flush(true);
        ob_clean();
        header("Content-type: text/plain");
        #log_msg(headers_list());
        echo($result);
        ob_flush();
        flush();
        ob_end_flush();
        die();
    }

    private function msgHanlder(array $msg)
    {
        $result = 'success';
        if($msg['MsgType'] == 'event') {    // 事件通知

        } else if($msg['MsgType'] == 'text') {  // 文本消息
            $ToUserName = $msg['ToUserName'];
            $FromUserName = $msg['FromUserName'];
            $content = $msg['Content'];
            $result = arrayToXml([  // 转发到微信官方客服工具
                    'ToUserName' => $FromUserName,
                    'FromUserName' => $ToUserName,
                    'CreateTime' => time(),
                    'MsgType' => 'transfer_customer_service'
                ]);
        }
        return $result;
    }
}