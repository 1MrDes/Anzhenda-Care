<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2018/11/19
 * Time: 10:48
 */

namespace apps\health_assist\core\service;


use apps\health_assist\core\model\PayTrade;
use apps\health_assist\core\payment\IPayOrder;
use apps\health_assist\core\payment\PaymentBase;
use apps\health_assist\core\payment\WxPay;
use think\Exception;
use think\facade\Log;
use vm\com\BaseService;
use vm\com\logic\PartnerPayLogic;
use vm\org\lock\DisLockFactory;

class PayTradeService extends BaseService
{
    protected $cachePrefix = 'pay_trade:';

    /**
     * @var UserPlatformService
     */
    private $userPlatformService;

    /**
     * @var SiteConfigService
     */
    private $siteConfigService;

    protected function init()
    {
        parent::init();
        $this->userPlatformService = service('UserPlatform', SERVICE_NAMESPACE);
        $this->siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return PayTrade
     */
    protected function getModel()
    {
        return new PayTrade();
    }

    public function create(array $data)
    {
        $data['trade_no'] = $this->genTradeNo();
        $data['pay_status'] = PayTrade::STATUS_WAIT_PAY;
        $data['dateline'] = time();
        $id = parent::create($data);
        if($id) {
            $orderService = service($data['order_type'], SERVICE_NAMESPACE);
            $orderService->onPayTradeCreated($data['order_id'], $data['trade_no']);
            return $data['trade_no'];
        }
        throw new Exception('创建失败');
    }

    /**
     * 创建支付交易流水
     * @param string $orderType     --订单类型
     * @param string $orderSn       --订单号
     * @param string $mpPlatform    --运行平台
     * @param string $appid         --小程序、公众号等的appId
     * @return string[]
     * @throws Exception
     */
    public function createPayTrade($orderType, $orderSn, $mpPlatform, $appid = '')
    {
        $orderService = service($orderType, SERVICE_NAMESPACE);
        if(!$orderService->getInstance() instanceof IPayOrder) {
            throw new Exception('订单类型无效');
        }
        $order = $orderService->getByOrderSn($orderSn);

        $data = [
            'order_id' => $order['id'],
            'order_sn' => $order['order_sn'],
            'order_type' => $orderType,
            'order_amount' => $order['order_money'],
            'payment_code' => '',
            'payment_name' => '',
            'appid' => $appid,
            'subject' => $order['order_subject']
        ];

        $retData = [
            'pay_params' => '',
            'pay_trade_no' => ''
        ];

        $body = $order['order_subject'];
        if($mpPlatform == 'mp-weixin') {  // 微信
            $userPlatform = $this->userPlatformService->info([
                'appid' => $appid,
                'user_id' => $order['buyer_id']
            ]);
            $openid = $userPlatform ? $userPlatform['open_id'] : '';

            $weapps = $this->siteConfigService->getValueByCode('weapps');

            $data['payment_code'] = PaymentBase::PAYMENT_CODE_WXPAY;
            $data['payment_name'] = '微信支付';
            $data['appid'] = $appid;
            $payConfig = [
                'app_id' => $appid,
                'app_secret' => $weapps[$appid]['weapp_app_secret'],
                'mch_id' => $this->siteConfigService->getValueByCode('weapp_pay_mch_id'),
                'pay_key' => $this->siteConfigService->getValueByCode('weapp_pay_sign_key'),
            ];
            $payment = new WxPay($payConfig);
            $tradeNo = $this->create($data);
            $payParams = $payment->buildPreOrder($tradeNo, $order['order_money'], 'JSAPI', $openid, $body);
            $retData['pay_params'] = $payParams;
            $retData['pay_trade_no'] = $tradeNo;
        } else if($mpPlatform == 'mp-alipay') {   // 支付宝
            $data['payment_code'] = PaymentBase::PAYMENT_CODE_ALIPAY;
            $data['payment_name'] = '支付宝';
        } else if($mpPlatform == 'mp-baidu') {    //  百度

        } else if($mpPlatform == 'mp-toutiao') {  //  字节

        }
        return $retData;
    }

    private function genTradeNo()
    {
        do {
            $tradeNo = date('ymdHis') . rand_string(8, 1);
            $isExists = $this->info([
                'trade_no' => $tradeNo
            ]);
        } while($isExists);
        return $tradeNo;
    }

    public function getByTradeNo($tradeNo)
    {
        if($trade = cache($this->cachePrefix . $tradeNo)) {
            $trade['pay_status_label'] = PayTrade::getStatusLabel($trade['pay_status']);
            return $trade;
        }
        $trade = $this->info([
            'trade_no' => $tradeNo
        ]);
        if($trade) {
            cache($this->cachePrefix, $tradeNo, 3600*24);
            $trade['pay_status_label'] = PayTrade::getStatusLabel($trade['pay_status']);
        }
        return $trade;
    }

    public function query($tradeNo)
    {
        $trade = $this->getByTradeNo($tradeNo);
        if($trade['payment_code'] == PaymentBase::PAYMENT_CODE_WXPAY) {
            $appid = $trade['appid'];
            $weapps = $this->siteConfigService->getValueByCode('weapps');
            $cfg = [
                'app_id' => $appid,
                'app_secret' => $weapps[$appid]['weapp_app_secret'],
                'mch_id' => $this->siteConfigService->getValueByCode('weapp_pay_mch_id'),
                'pay_key' => $this->siteConfigService->getValueByCode('weapp_pay_sign_key'),
            ];
            $payment = 'apps\health_assist\core\payment\\' . $trade['payment_code'];
            $payment = new $payment($cfg);
            $result = $payment->query($tradeNo);
            if($result && $result['pay_status'] == PaymentBase::PAY_STATUS_SUCCESS
                && $result['total_fee'] == $trade['order_amount']) {
                $this->paySuccess($tradeNo, $result['transaction_id'], $result['pay_time'], $result['attach']);
                return $result;
            }
        } else if($trade['payment_code'] == PaymentBase::PAYMENT_CODE_PARTNER_PAY) {
            /** @var PartnerPayLogic $partnerPayLogic */
            $partnerPayLogic = logic('PartnerPay', '\vm\com\logic\\');
            $partnerPayLogic->init([
                'rpc_server' => env('rpc_pay.host') . '/partner_pay_order',
                'rpc_provider' => env('rpc_pay.provider'),
                'rpc_token' => env('rpc_pay.token'),
            ]);
            $result = $partnerPayLogic->query($tradeNo);
            if($result && $result['pay_status'] == PaymentBase::PAY_STATUS_SUCCESS
                && $result['pay_money'] == $trade['order_amount']) {
                $this->paySuccess($tradeNo, $result['trade_no'], $result['pay_time'], '');
                return $result;
            }
        }
        return null;
    }

    /**
     * 支付成功后回调
     * @param $tradeNo
     * @param $payTime
     * @return bool
     * @throws Exception
     */
    public function paySuccess($tradeNo, $transactionId, $payTime, $attach = '')
    {
        // 开始加锁
        $lockKey = 'pay_trade_success:' . $tradeNo;
        $lock = DisLockFactory::instance()->lock($lockKey, 5000);
        if(!$lock) {
//            throw new Exception('发生错误');
            return false;
        }
        $result = $this->getModel()->paySuccess($tradeNo, $transactionId, $payTime);
        if($result) {
            try {
                $trade = $this->getByTradeNo($tradeNo);
                $orderService = service($trade['order_type'], SERVICE_NAMESPACE);
                $orderService->onPaySuccess($tradeNo, $transactionId, $payTime, $trade['payment_name']);
                cache($this->cachePrefix . $tradeNo, null);
                return true;
            } catch (Exception $e) {
                Log::error($e->getTraceAsString());
                return false;
            } finally {
                // 释放锁
                DisLockFactory::instance()->unlock($lockKey);
            }
        }
        // 释放锁
        DisLockFactory::instance()->unlock($lockKey);
//        throw new Exception('发生错误');
        return false;
    }
}