<?php

namespace apps\health_assist\core\payment;

use think\facade\Log;

require_once EXTEND_PATH . "vm/org/alipay-sdk/AopClient.php";
require_once EXTEND_PATH . "vm/org/alipay-sdk/request/AlipayTradeAppPayRequest.php";
require_once EXTEND_PATH . "vm/org/alipay-sdk/request/AlipayTradeQueryRequest.php";
require_once EXTEND_PATH . "vm/org/alipay-sdk/AopCertClient.php";

class AliPay extends PaymentBase
{
    protected $name = '支付宝支付';
    protected $_config = [
        'app_id' => '',
        'rsa_private_key' => '',
        'rsa_private_key_path' => '',
        'rsa_public_key' => '',
        'rsa_public_key_path' => '',
        'alipayrsa_public_key' => '',
        'alipayrsa_public_key_path' => '',
    ];
    protected $payConfig = null;

    public function __construct($cfg)
    {
        parent::__construct($cfg);
        $this->_config = array_merge($this->_config, $cfg);
        $this->_config['rsa_private_key'] = file_get_contents($this->_config['rsa_private_key_path']);
        $this->_config['rsa_public_key'] = file_get_contents($this->_config['rsa_public_key_path']);
        $this->_config['alipayrsa_public_key'] = file_get_contents($this->_config['alipayrsa_public_key_path']);

//        $aop = new \AopCertClient();
//        $alipayCertPath = $this->_config['alipayrsa_public_key_path'];
//        //调用getPublicKey获取支付宝公钥
//        $alipayrsaPublicKey = $aop->getPublicKey($alipayCertPath);
//        $alipayrsaPublicKey = str_replace("\n", '', $alipayrsaPublicKey);
//        $this->_config['alipayrsa_public_key'] = $alipayrsaPublicKey;
//        $aop = null;
    }

    public function getAppId()
    {
        return $this->_config['app_id'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCode()
    {
        return 'AliPay';
    }

    protected function buildNotifyUrl($outTradeNo)
    {
        $baseUrl = request()->scheme() . '://' . request()->host() . '/' . getContextPath();
        return $baseUrl . 'pay.php/alipay/notify/' . $outTradeNo;
    }

    /**
     * 生成预支付订单
     *
     * @param string $payOutTradeNo -- 商户订单号
     * @param float $totalFee -- 订单总金额,单位为元
     * @param string $payType -- 支付方式
     * @param string $buyerId -- 用户ID
     * @param string $body -- 简要描述
     * @param string $attach -- 定义附加数据，将原样返回
     * @return mixed
     * @throws
     */
    public function buildPreOrder($payOutTradeNo, $totalFee, $payType, $buyerId = '', $body = '', $attach = '')
    {
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->_config['app_id'];
        // 请填写开发者私钥去头去尾去回车，一行字符串
        $aop->rsaPrivateKey = $this->_config['rsa_private_key'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        // 请填写支付宝公钥，一行字符串
        $aop->alipayrsaPublicKey = $this->_config['alipayrsa_public_key'];
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
//        $bizcontent = "{\"body\":\"***\","
//            . "\"subject\": \"" . $body . "\","
//            . "\"out_trade_no\": \"" . $payOutTradeNo . "\","
//            . "\"timeout_express\": \"30m\","
//            . "\"total_amount\": \"" . $totalFee . "\","
//            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
//            . "}";

        $bizcontent = [
            'subject' => '***',
            'body' => $body,
            'out_trade_no' => $payOutTradeNo,
            'timeout_express' => '30m',
            'total_amount' => $totalFee,
            'product_code' => 'QUICK_MSECURITY_PAY'
        ];
        $request->setNotifyUrl($this->buildNotifyUrl($payOutTradeNo));
        $request->setBizContent(json_encode($bizcontent));
//        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);//就是orderString 可以直接给客户端请求，无需再做处理。
        return $response;
    }

    /**
     * 查询支付结果
     * @param string $payOutTradeNo -- 商户订单号
     * @return null|array
     * @throws
     */
    public function query($payOutTradeNo)
    {
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->_config['app_id'];
        // 请填写开发者私钥去头去尾去回车，一行字符串
        $aop->rsaPrivateKey = $this->_config['rsa_private_key'];
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        // 请填写支付宝公钥，一行字符串
        $aop->alipayrsaPublicKey = $this->_config['alipayrsa_public_key'];
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeQueryRequest();
        $bizcontent = [
            'out_trade_no' => $payOutTradeNo
        ];
        $request->setBizContent(json_encode($bizcontent));
        $response = $aop->execute($request);
        if (empty($response)) {
            return null;
        }
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $response->$responseNode->code;
        if (!empty($resultCode) && intval($resultCode) == 10000) {
            if($response->$responseNode->trade_status == 'TRADE_SUCCESS' || $response->$responseNode->trade_status == 'TRADE_FINISHED') {
                // 用户实际支付金额
                $payAmount = isset($response->$responseNode->buyer_pay_amount) && !empty($response->$responseNode->buyer_pay_amount) ? floatval($response->$responseNode->buyer_pay_amount) : 0.00;

                return [
                    'pay_status' => PaymentBase::PAY_STATUS_SUCCESS,
                    'transaction_id' => $response->$responseNode->trade_no,
                    'out_trade_no' => $payOutTradeNo,
                    'total_fee' => floatval($response->$responseNode->total_amount),
                    'attach' => isset($response->$responseNode->ext_infos) && !empty($response->$responseNode->ext_infos) ? $response->$responseNode->ext_infos : '',
                    'pay_time' => time()
                ];
            } else if($response->$responseNode->trade_status == 'WAIT_BUYER_PAY') {     // 待付款

            } else if($response->$responseNode->trade_status == 'TRADE_CLOSED') {   // 未付款交易超时关闭，或支付完成后全额退款

            }
        } else {
            Log::error('支付交易【'.$payOutTradeNo.'】查询出错：' . $response->$responseNode->msg);
            return null;
        }
        return null;
    }

    public function buildForm(array $orders, $extra = '', $attach = '')
    {

    }

    public function verifyReturn($payOutTradeNo, $payTotalFee, &$result)
    {

    }

    public function verifyNotify($payOutTradeNo, $payTotalFee, &$result)
    {

    }

    public function refund(array $refundInfo)
    {

    }

    public function refundDetail($refundId, $outRefundNo)
    {

    }
}