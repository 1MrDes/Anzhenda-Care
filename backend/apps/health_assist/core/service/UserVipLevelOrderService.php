<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\SystemNotice;
use apps\health_assist\core\model\UserAccountBook;
use apps\health_assist\core\model\UserPlatform;
use apps\health_assist\core\model\UserSystemNotice;
use apps\health_assist\core\model\UserVipLevelOrder;
use apps\health_assist\core\payment\IPayOrder;
use think\Exception;
use think\facade\Log;
use vm\com\BaseService;
use vm\com\logic\WechatMiniAppLogic;
use vm\org\lock\DisLockFactory;

class UserVipLevelOrderService extends BaseService implements IPayOrder
{

    /**
     * @return UserVipLevelOrder
     */
    protected function getModel()
    {
        return new UserVipLevelOrder();
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return service('User', SERVICE_NAMESPACE);
    }

    /**
     * @return UserAccountBookService
     */
    protected function getUserAccountBookService()
    {
        return service('UserAccountBook', SERVICE_NAMESPACE);
    }

    /**
     * @return SiteConfigService
     */
    private function getSiteConfigService()
    {
        return  service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return UserPlatformService
     */
    private function getUserPlatformService()
    {
        return service('UserPlatform', SERVICE_NAMESPACE);
    }

    /**
     * @return SystemNoticeService
     */
    private function getSystemNoticeService()
    {
        return service('SystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return UserSystemNoticeService
     */
    private function getUserSystemNoticeService()
    {
        return service('UserSystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return WechatMiniAppLogic
     */
    private function getWxMiniLogic($appid)
    {
        static $logic;
        if($logic !== null) {
            return $logic;
        }

        $weapps = $this->getSiteConfigService()->getValueByCode('weapps');
        if(!isset($weapps[$appid])) {
            return null;
        }
        $logic = logic('WechatMiniApp', '\vm\com\logic\\');
        $logic->init([
            'app_id' => $weapps[$appid]['weapp_app_id'],
            'app_secret' =>$weapps[$appid]['weapp_app_secret'],
            'app_token' => $weapps[$appid]['weapp_app_token'],
            'encode_aeskey' => $weapps[$appid]['weapp_app_encoding_aeskey']
        ]);

        return $logic;
    }

    private function genSn()
    {
        while (true) {
            $sn = date('ymdHis') . rand_string(8, 1);
            if (!$this->getModel()->info(['order_sn' => $sn])) {
                return $sn;
            }
        }
    }

    public function createOrder(array $order)
    {
        $user = $this->getUserService()->getByPk($order['buyer_id']);
//        if($order['vip_level'] < $user['vip_level']) {
//            throw new Exception('只能升级为更高级别会员');
//        }

        $lockKey = 'lock_submit_user_vip_level_order';
        $lock = DisLockFactory::instance();
        $lock->lock($lockKey, 5000);
        try {
            $order['order_sn'] = $this->genSn();
            $order['order_status'] = UserVipLevelOrder::ORDER_STATUS_CONFIRMED;
            $order['create_time'] = time();
            $order['pay_status'] = UserVipLevelOrder::PAY_STATUS_UNPAIED;
            $orderId = parent::create($order);
            return $order['order_sn'];
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            throw new Exception('订单创建失败');
        } finally {
            $lock->unlock($lockKey);
        }
    }

    public function getByOrderSn($orderSn)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        $order = $this->format($order);
        return $order;
    }

    public function format(array $order)
    {
        switch ($order['order_status']) {
            case UserVipLevelOrder::ORDER_STATUS_CANCELLED:
                $order['order_status_label'] = '已取消';
                break;
            case UserVipLevelOrder::ORDER_STATUS_UNCONFIRMED:
                $order['order_status_label'] = '未确认';
                break;
            case UserVipLevelOrder::ORDER_STATUS_REFUNDED:
                $order['order_status_label'] = '已退款';
                break;
            case UserVipLevelOrder::ORDER_STATUS_FINISHED:
                $order['order_status_label'] = '已完成';
                break;
            case UserVipLevelOrder::ORDER_STATUS_CONFIRMED:
                $order['order_status_label'] = '待付款';
                break;
            case UserVipLevelOrder::ORDER_STATUS_WAIT_SHIP:
                $order['order_status_label'] = '待发货';
                break;
            case UserVipLevelOrder::ORDER_STATUS_SHIPPED:
                $order['order_status_label'] = '已发货';
                break;
            case UserVipLevelOrder::ORDER_STATUS_REFUNDING:
                $order['order_status_label'] = '退款中';
                break;
            default:
                $order['order_status_label'] = 'N/A';
                break;
        }

        switch ($order['pay_status']) {
            case UserVipLevelOrder::PAY_STATUS_UNPAIED:
                $order['pay_status_label'] = '未付款';
                break;
            case UserVipLevelOrder::PAY_STATUS_PAIED:
                $order['pay_status_label'] = '已付款';
                break;
            default:
                $order['pay_status_label'] = 'N/A';
                break;
        }

        return $order;
    }

    /**
     * 创建支付交易后回调
     * @param $id
     * @param $tradeNo
     * @return bool|mixed|BaseService
     */
    public function onPayTradeCreated($id, $tradeNo)
    {
        return $this->updateByPk([
            'id' => $id,
            'pay_trade_no' => $tradeNo
        ]);
    }

    /**
     * 支付成功后回调
     * @param array $tradeNo                    --本站支付流水号
     * @param string $transactionId             --第三方支付平台流水号
     * @param int $payTime                      --付款时间
     * @param string $paymentName               --支付方式名称
     * @return bool|mixed
     * @throws Exception
     */
    public function onPaySuccess($tradeNo, $transactionId, $payTime, $paymentName)
    {
        $order = $this->info([
            'pay_trade_no' => $tradeNo
        ]);
        if (!$order) {
            throw new Exception('订单不存在');
        }
        return $this->payConfirm($order['id'], $payTime, $paymentName, $transactionId);
    }

    /**
     * 后台确认已付款
     * @param int $orderId                  --订单ID
     * @param int $payTime                  --付款时间
     * @param string $payPlatformName       --支付平台名称
     * @param string $payPlatformTradeNo    --支付平台流水号
     * @return bool
     * @throws
     */
    public function payConfirm($orderId, $payTime, $payPlatformName, $payPlatformTradeNo)
    {
        $order = $this->getByPk($orderId);
        if ($order['order_status'] != UserVipLevelOrder::ORDER_STATUS_CONFIRMED
            || $order['pay_status'] != UserVipLevelOrder::PAY_STATUS_UNPAIED) {
//            throw new Exception('只有已确认待付款订单才可确认付款');
            return true;
        }
        $lockKey = 'lock_user_vip_level_order_pay_' . $orderId;
        $lock = DisLockFactory::instance();
        $lock->lock($lockKey, 5000);
        try {
            $data = [
                'order_status' => UserVipLevelOrder::ORDER_STATUS_FINISHED,
                'pay_status' => UserVipLevelOrder::PAY_STATUS_PAIED,
                'pay_time' => $payTime,
                'transaction_id' => $payPlatformTradeNo,
                'payment_name' => $payPlatformName
            ];
            $where = [
                'id' => $orderId,
                'order_status' => UserVipLevelOrder::ORDER_STATUS_CONFIRMED,
                'pay_status' => UserVipLevelOrder::PAY_STATUS_UNPAIED,
            ];
            if ($this->update($data, $where)) {
                $this->getUserService()->onSetVipLevel($order['buyer_id'], $order['vip_level']);
                $this->getUserAccountBookService()->record(
                    $order['buyer_id'],
                    UserAccountBook::OP_DECREASE,
                    $order['order_money'],
                    UserAccountBook::MONEY_TYPE_MONEY,
                    UserAccountBook::IID_TYPE_USER_VIP_LEVEL,
                    $orderId,
                    null,
                    '购买会员');

                $this->_sendMsg($orderId);
                return true;
            }
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return false;
        } finally {
            $lock->unlock($lockKey);
        }
        return false;
    }

    private function _sendMsg($orderId)
    {
        $order = $this->getByPk($orderId);
        // 给用户发送通知
        $systemNoticeId = $this->getSystemNoticeService()->create([
            'title' => '会员购买成功',
            'content' => '您已成功购买vip会员，请点击查看。',
            'type' => SystemNotice::TYPE_SINGLE,
            'status' => SystemNotice::STATUS_WAIT_PULL,
            'recipient_id' => $order['buyer_id'],
            'manager_id' => 0,
            'url' => json_encode([
                'weapp' => '/my/pages/vip/index',
                'web' => '/vip/index',
                'app' => '/my/pages/vip/index'
            ])
        ]);
        // 发送小程序客服消息
        try {
            $wxMiniLogic = $this->getWxMiniLogic($order['appid']);
            if($wxMiniLogic !== null) {
                $userPlatform = $this->getUserPlatformService()->getByUserIdWithAppid(
                    $wxMiniLogic->getAppId(),
                    $order['buyer_id']
                );
                if($userPlatform) {
                    $staticsUrl = $this->getSiteConfigService()->getValueByCode('statics_url');
                    $dir = WWW_PATH . 'uploads/';
                    $fileName = 'share.jpg';
                    if(!file_exists($dir . $fileName)) {
                        grabRemoteFile($staticsUrl . '/images/share.jpg', $dir, $fileName);
                    }
                    $mediaId = $wxMiniLogic->uploadTempMedia($dir . $fileName);
                    $serviceMsg = [
                        'touser' => $userPlatform['open_id'],
                        "msgtype" => "miniprogrampage",
                        'miniprogrampage' => [
                            'title' => 'VIP会员开通成功',
                            'pagepath' => 'my/pages/vip/index',
                            'thumb_media_id' => $mediaId
                        ]
                    ];
                    $wxMiniLogic->sendCustomServiceMessage($serviceMsg);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
        }
    }
}