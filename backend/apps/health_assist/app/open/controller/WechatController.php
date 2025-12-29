<?php

namespace apps\health_assist\app\open\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\model\PayTrade;
use apps\health_assist\core\model\SubscribeMessageTemplate;
use apps\health_assist\core\service\PayTradeService;
use apps\health_assist\core\service\SiteConfigService;
use apps\health_assist\core\service\SubscribeMessageTemplateService;
use apps\health_assist\core\service\UserPlatformService;
use apps\health_assist\core\service\UserVipLevelOrderService;
use think\Exception;
use think\facade\Log;
use vm\com\logic\PartnerPayLogic;
use vm\com\logic\WechatMiniAppLogic;
use vm\org\queue\QueueFactory;

class WechatController extends BaseHealthAssistOpenController
{
    /**
     * @var WechatMiniAppLogic
     */
    protected $wxMiniLogic;

    /**
     * @var SiteConfigService
     */
    private $siteConfigService;

    /**
     * @var SubscribeMessageTemplateService
     */
    private $subscribeMessageTemplateService = null;

    protected function init()
    {
        parent::init();
        $this->siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
        $weapps = $this->siteConfigService->getValueByCode('weapps');
        $weappId = request()->param('__appid', '');
        if(empty($weappId)) {
            $weappId = request()->route('appid', '');
            if(empty($weappId)) {
                $weappId = request()->param('appid', '');
            }
        }
        $this->wxMiniLogic = logic('WechatMiniApp', '\vm\com\logic\\');
        $this->wxMiniLogic->init([
            'app_id' => $weapps[$weappId]['weapp_app_id'],
            'app_secret' =>$weapps[$weappId]['weapp_app_secret'],
            'app_token' => $weapps[$weappId]['weapp_app_token'],
            'encode_aeskey' => $weapps[$weappId]['weapp_app_encoding_aeskey']
        ]);
    }

    /**
     * @return UserPlatformService
     */
    private function getUserPlatformService()
    {
        return service('UserPlatform', SERVICE_NAMESPACE);
    }

    /**
     * @return SubscribeMessageTemplateService
     */
    private function getSubscribeMessageTemplateService()
    {
        if($this->subscribeMessageTemplateService !== null) {
            return $this->subscribeMessageTemplateService;
        }
        $this->subscribeMessageTemplateService = service('SubscribeMessageTemplate', SERVICE_NAMESPACE);
        return $this->subscribeMessageTemplateService;
    }

    public function decode_scene(Request $request)
    {
        $scene = $request->param('scene', '');
        $data = $this->wxMiniLogic->decodeScene($scene);
        return $this->success($data);
    }

    public function gen_url_scheme(Request $request)
    {
        $path = $request->param('path', '');
        $query = $request->param('query', '');
        $link = $this->wxMiniLogic->generateUrlScheme($path, $query);
        if($link === false) {
            throw new Exception('生成失败');
        }
        return $this->success(['link' => $link]);
    }

    public function pre_fetch_data(Request $request)
    {
        $appid = $request->param('appid', '');
        $code = $request->param('code', '');
        $timestamp = $request->param('timestamp', 0);
        if(empty($appid) || empty($code)) {
            throw new Exception('非法请求');
        }
        $ret = $this->wxMiniLogic->getOpenIdByCode($code);
        $openid = $ret['openid'];
        $unionid = isset($ret['unionid']) ? $ret['unionid'] : '';
        $sessionKey = $ret['session_key'];

        $keys = config('keys');
        $key = $keys['default']['miniapp'];
        return json([
            'openid' => $openid,
            'unionid' => $unionid,
            'api_key' => $key,
            'upload_api_key' => md5(uniqid() . microtime())
        ]);
    }

    public function feed(Request $request)
    {
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

        return response($result, 200, ['Content-type' => 'text/plain']);
    }

    private function msgHanlder(array $msg)
    {
        cache(WechatMiniAppLogic::WX_MINI_USER_LAST_SESSION_CACHE_PREFIX . md5($msg['FromUserName']), time(), 3600*48);
        $result = 'success';
        if($msg['MsgType'] == 'event') {    // 事件通知
            $FromUserName = $msg['FromUserName'];
            $userPlatform = $this->getUserPlatformService()->getByOpenid($FromUserName);
            $userId = 0;
            if($userPlatform && $userPlatform['user_id'] > 0) {
                $userId = $userPlatform['user_id'];
            }
            // 订阅消息事件推送
            if($msg['Event'] == 'subscribe_msg_popup_event') {
                if(isset($msg['SubscribeMsgPopupEvent']['List']['TemplateId'])) { // 订阅单个模板
                    if($msg['SubscribeMsgPopupEvent']['List']['SubscribeStatusString'] == 'accept') {
                        $this->getSubscribeMessageTemplateService()->create([
                            'user_id' => $userId,
                            'openid' => $FromUserName,
                            'type' => SubscribeMessageTemplate::TYPE_ONCE,
                            'template_id' => $msg['SubscribeMsgPopupEvent']['List']['TemplateId'],
                            'add_time' => time()
                        ]);
                    } else if($msg['SubscribeMsgPopupEvent']['List']['SubscribeStatusString'] == 'reject') {
//                        $this->getSubscribeMessageTemplateService()->delete([
//                            'openid' => $FromUserName,
//                            'template_id' => $msg['SubscribeMsgPopupEvent']['List']['TemplateId'],
//                        ]);
                    }
                } else { // 订阅多个模板
                    foreach ($msg['SubscribeMsgPopupEvent']['List'] as $item) {
                        if($item['SubscribeStatusString'] == 'accept') {
                            $this->getSubscribeMessageTemplateService()->create([
                                'user_id' => $userId,
                                'openid' => $FromUserName,
                                'type' => SubscribeMessageTemplate::TYPE_ONCE,
                                'template_id' => $item['TemplateId'],
                                'add_time' => time()
                            ]);
                        } else if($item['SubscribeStatusString'] == 'reject') {
//                            $this->getSubscribeMessageTemplateService()->delete([
//                                'openid' => $FromUserName,
//                                'template_id' => $item['TemplateId'],
//                            ]);
                        }
                    }
                }
            } else if($msg['Event'] == 'subscribe_msg_change_event') {
                if(isset($msg['SubscribeMsgChangeEvent']['List']['TemplateId'])) { // 订阅单个模板
                    if($msg['SubscribeMsgChangeEvent']['List']['SubscribeStatusString'] == 'accept') {
                        $this->getSubscribeMessageTemplateService()->create([
                            'user_id' => $userId,
                            'openid' => $FromUserName,
                            'type' => SubscribeMessageTemplate::TYPE_ONCE,
                            'template_id' => $msg['SubscribeMsgChangeEvent']['List']['TemplateId'],
                            'add_time' => time()
                        ]);
                    } else if($msg['SubscribeMsgChangeEvent']['List']['SubscribeStatusString'] == 'reject') {
                        $this->getSubscribeMessageTemplateService()->delete([
                            'openid' => $FromUserName,
                            'template_id' => $msg['SubscribeMsgChangeEvent']['List']['TemplateId'],
                        ]);
                    }
                } else { // 订阅多个模板
                    foreach ($msg['SubscribeMsgChangeEvent']['List'] as $item) {
                        if($item['SubscribeStatusString'] == 'accept') {
                            $this->getSubscribeMessageTemplateService()->create([
                                'user_id' => $userId,
                                'openid' => $FromUserName,
                                'type' => SubscribeMessageTemplate::TYPE_ONCE,
                                'template_id' => $item['TemplateId'],
                                'add_time' => time()
                            ]);
                        } else if($item['SubscribeStatusString'] == 'reject') {
                            $this->getSubscribeMessageTemplateService()->delete([
                                'openid' => $FromUserName,
                                'template_id' => $item['TemplateId'],
                            ]);
                        }
                    }
                }
            }
        } else if($msg['MsgType'] == 'text') {  // 文本消息
            $ToUserName = $msg['ToUserName'];
            $FromUserName = $msg['FromUserName'];
            $content = $msg['Content'];
            $result = arrayToXml([  // 转发到微信官方客服工具
                'ToUserName' => $FromUserName,
                'FromUserName' => $ToUserName,
                'CreateTime' => $msg['CreateTime'],
                'MsgType' => 'transfer_customer_service'
            ]);
        } else if($msg['MsgType'] == 'miniprogrampage') {   // 发送小程序卡片
            $FromUserName = $msg['FromUserName'];
            $weapps = $this->siteConfigService->getValueByCode('weapps');
            $weappConfig = $weapps[$this->wxMiniLogic->getAppId()];
            if($weappConfig['audit_status'] == 'success') {  // 已通过微信审核
                $userPlatform = $this->getUserPlatformService()->getByOpenid($FromUserName);
                if(!empty($userPlatform)) {
                    // 购买vip会员
                    $pattern = '/pages\/index\/index\?from=invite\&fromuid=([0-9]+)\&vip_level=([0-9]+)/is';
                    preg_match_all($pattern, $msg['PagePath'], $matchs);
                    if(!empty($matchs[0]) && !empty($matchs[2])) {
                        $queueHandler = QueueFactory::instance();
                        // 发送vip付款链接
                        $vipLevel = current($matchs[2]);
                        $payTrade = $this->createUserVipLevelOrderPayTrade($vipLevel, $userPlatform['user_id']);
                        if($payTrade) {
                            $payUrl = $payTrade['pay_url'];
                            $message = [
                                'touser' => $FromUserName,
                                'msgtype' => 'text',
                                'text' => [
                                    'content' => "将上方小程序分享给好友可免费获得一个月VIP会员。\r\n您也可以点击链接购买VIP会员，\r\n付款链接：" . $payUrl
                                ]
                            ];
//                        $this->wxMiniLogic->sendCustomServiceMessage($message);
                            $queueHandler->set('weapp_service_msg', [
                                'message' => $message,
                                'appid' => $this->wxMiniLogic->getAppId()
                            ]);
                        }
                        return $result;
                    }
                }
            } else {    // 微信审核中
                // 发送客服消息
                $this->sendCustomServiceWxQRCode($FromUserName);
                return $result;
            }
        }
        return $result;
    }

    private function sendCustomServiceWxQRCode($FromUserName)
    {
        $staticsUrl = $this->siteConfigService->getValueByCode('statics_url');
        $message = [
            'touser' => $FromUserName,
            'msgtype' => 'image',
            'image' => [
                'media_id' => ''
            ]
        ];

        $queueHandler = QueueFactory::instance();
        $queueHandler->set('weapp_service_msg', [
            'message' => $message,
            'image' => $staticsUrl . '/images/customer_service_wxid.jpeg',
            'appid' => $this->wxMiniLogic->getAppId()
        ]);
    }

    private function createUserVipLevelOrderPayTrade($vipLevel, $userId)
    {
        $vips = $this->siteConfigService->getValueByCode('vips');
        if(!isset($vips['level_' . $vipLevel])) {
            return false;
        }
        $orderMoney = floatval($vips['level_' . $vipLevel]['price']);
        $vipLevelName = $vips['level_' . $vipLevel]['name'];

        /**@var UserVipLevelOrderService $userVipLevelOrderService*/
        $userVipLevelOrderService = service('UserVipLevelOrder', SERVICE_NAMESPACE);
        $order = [
            'buyer_id' => $userId,
            'fromuid' => 0,
            'order_money' => $orderMoney,
            'vip_level' => $vipLevel,
            'appid' => $this->wxMiniLogic->getAppId(),
            'order_subject' => '购买VIP会员'
        ];
        $orderSn = $userVipLevelOrderService->createOrder($order);
        $order = $userVipLevelOrderService->getByOrderSn($orderSn);

        $appId = $this->wxMiniLogic->getAppId();

        // 生成支付参数
        /**@var PayTradeService $payTradeService */
        $payTradeService = service('PayTrade', SERVICE_NAMESPACE);

        $data = [
            'subject' => '开通VIP会员',
            'remark' => $vipLevelName,
            'order_id' => $order['id'],
            'order_sn' => $order['order_sn'],
            'order_type' => PayTrade::ORDER_TYPE_USER_VIP_LEVEL_ORDER,
            'order_amount' => $order['order_money'],
            'payment_code' => 'PartnerPay',
            'payment_name' => '微信支付',
            'appid' => $appId
        ];
        $tradeNo = $payTradeService->create($data);

        /** @var PartnerPayLogic $partnerPayLogic */
        $partnerPayLogic = logic('PartnerPay', '\vm\com\logic\\');
        $partnerPayLogic->init([
            'rpc_server' => env('rpc_pay.host') . '/partner_pay_order',
            'rpc_provider' => env('rpc_pay.provider'),
            'rpc_token' => env('rpc_pay.token'),
        ]);
        $notifyUrl = request()->scheme() . '://' . request()->host() . '/' . getContextPath();
        $notifyUrl .= 'pay.php/partner_pay/notify';
        $site = config('site');
        $ret = $partnerPayLogic->create([
            'appid' => $site['partner_pay']['appid'],
            'trade_type' => 'WECHAT_MP_JSAPI',
            'out_trade_no' => $tradeNo,
            'money' => $order['order_money'],
            'remark' => '开通VIP会员',
            'return_url' => '',
            'notify_url' => $notifyUrl
        ]);
        return $ret;
    }
}