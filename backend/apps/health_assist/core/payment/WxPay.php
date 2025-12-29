<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2019-03-11
 * Time: 20:10
 */

namespace apps\health_assist\core\payment;


use think\Exception;
use think\facade\Log;

class WxPay extends PaymentBase
{
    protected $name = '微信支付';
    protected $_config = array();
    protected $wxPayConfig = null;

    public function __construct($cfg)
    {
        parent::__construct($cfg);
        $this->_config['app_id'] = $cfg['app_id'];
        $this->_config['app_secret'] = $cfg['app_secret'];
        $this->_config['mch_id'] = $cfg['mch_id'];
        $this->_config['pay_key'] = $cfg['pay_key'];

        require_once EXTEND_PATH . "vm/org/wxpay/WxPayConfig.php";
        $this->wxPayConfig = new \WxPayConfig();
        $this->wxPayConfig->setAppId($cfg['app_id']);
        $this->wxPayConfig->setMerchantId($cfg['mch_id']);
        $this->wxPayConfig->setSignType(\WxPayConfig::SIGN_TYPE_MD5);
        $this->wxPayConfig->setKey($cfg['pay_key']);
        $this->wxPayConfig->setAppSecret($cfg['app_secret']);
        $this->wxPayConfig->setSSLCertPath(DOC_PATH . 'data/wxpay/apiclient_cert.pem', DOC_PATH . 'data/wxpay/apiclient_key.pem');
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * 生成预支付订单
     *
     * @param string $payOutTradeNo     商户订单号
     * @param float $totalFee           订单总金额,单位为元
     * @param string $tradeType         交易类型，JSAPI -JSAPI支付，NATIVE -Native支付，APP -APP支付
     * @param string $openid            微信用户ID
     * @param string $body              简要描述
     * @param string $attach            定义附加数据，将原样返回
     * @throws
     * @return mixed
     */
    public function buildPreOrder($payOutTradeNo, $totalFee, $tradeType, $openid, $body = '', $attach = '')
    {
        require_once EXTEND_PATH . "vm/org/wxpay/WxPay.Api.php";
        require_once EXTEND_PATH . "vm/org/wxpay/WxPay.Data.php";
        require_once EXTEND_PATH . "vm/org/wxpay/WxPay.JsApiPay.php";

        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetAppid($this->_config['app_id']);
        $input->SetBody($body);
        $input->SetAttach($attach);
        $input->SetOut_trade_no($payOutTradeNo);
        $input->SetTotal_fee(strval($totalFee * 100));
//        $input->SetTime_start(date("YmdHis"));
//        $input->SetTime_expire(date("YmdHis", time() + 3600));
        $input->SetGoods_tag('');
        $input->SetNotify_url($this->buildNotifyUrl($payOutTradeNo));
        $input->SetTrade_type($tradeType);
        $input->SetOpenid($openid);

        $this->wxPayConfig->setNotifyUrl($this->buildNotifyUrl($payOutTradeNo));
        $result = \WxPayApi::unifiedOrder($this->wxPayConfig, $input);
        $return = null;

        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $wxpayApi = new \JsApiPay();
            $return = $wxpayApi->GetJsApiParameters($this->wxPayConfig, $result);
        } else {
            Log::error($result);
        }
        return $return;
    }

    public function buildForm(array $orders, $extra = '', $attach = '')
    {

    }

    /**
     * 查询支付结果
     * @param $payOutTradeNo            商户订单号
     * @return null|array
     * @throws
     */
    public function query($payOutTradeNo)
    {
        require_once EXTEND_PATH . "vm/org/wxpay/WxPayNotifyCallBack.php";
        $callBack = new \WxPayNotifyCallBack();
        $result = $callBack->queryOrderByOutTradeNo($this->wxPayConfig, $payOutTradeNo);
        if(isset($result['trade_state'])) {
            switch ($result['trade_state']) {
                case 'SUCCESS':     // 支付成功
                    return [
                        'pay_status' => PaymentBase::PAY_STATUS_SUCCESS,
                        'transaction_id' => $result['transaction_id'],
                        'out_trade_no' => $payOutTradeNo,
                        'total_fee' => $result['total_fee'] / 100,
                        'attach' => $result['attach'],
                        'pay_time' => strtotime($result['time_end'])
                    ];
                case 'REFUND':     // 转入退款

                    break;
                case 'NOTPAY':     // 未支付

                    break;
                case 'CLOSED':     // 已关闭

                    break;
                case 'REVOKED':     // 已撤销

                    break;
                case 'USERPAYING':     // 支付中，付款码支付

                    break;
                case 'PAYERROR':     // 支付失败

                    break;
                default:

                    break;
            }
        }
        return null;
    }

    public function verifyReturn($payOutTradeNo, $payTotalFee, &$result)
    {
        return $this->verifyNotify($payOutTradeNo, $payTotalFee,$result);
    }

    /**
     * 验证异步通知结果
     * @param $payOutTradeNo
     * @param $payTotalFee
     * @param $result
     * @return array|bool|null
     */
    public function verifyNotify($payOutTradeNo, $payTotalFee, &$result)
    {
//        require_once EXTEND_PATH . "vm/org/wxpay/WxPayNotifyCallBack.php";
//        $callBack = new \WxPayNotifyCallBack();
//        $callBack->Handle($this->wxPayConfig);
        $result = $this->query($payOutTradeNo);
        if($result && $result['pay_status'] == PaymentBase::PAY_STATUS_SUCCESS && $result['total_fee'] == $payTotalFee) {
            $xml  = "<xml>";
            $xml .= "<return_code><![CDATA[SUCCESS]]></return_code>";
            $xml .= "<return_msg><![CDATA[OK]]></return_msg>";
            $xml .= "</xml>";
            return $xml;
        }
        return null;
    }

    public function refund(array $refundInfo)
    {

    }

    public function refundDetail($refundId, $outRefundNo)
    {

    }

    protected function buildNotifyUrl($outTradeNo)
    {
        $baseUrl = request()->scheme() . '://' . request()->host() . '/' . getContextPath();
        return $baseUrl . 'pay.php/wxpay/notify/' . $outTradeNo;
    }

    /**
     * 企业付款到零钱
     * @param string $tradeNo   商家订单号
     * @param float $amount  金额：元
     * @param string $openid [发送人的 openid]
     * @param string $desc [企业付款描述信息 (必填)]
     * @param string $name [收款用户姓名 (选填)]
     * @return [type]    [description]
     */
    public function transfer($tradeNo, $openid, $amount, $desc = '', $name = '')
    {
        require_once EXTEND_PATH . "vm/org/wxpay/WxPay.Api.php";
        $result = \WxPayApi::transfer($this->wxPayConfig, $tradeNo, $openid, $amount, $desc, $name);
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return [
                'partner_trade_no' => $result['partner_trade_no'],
                'payment_no' => $result['payment_no'],
                'payment_time' => $result['payment_time'],
                'mch_appid' => $result['mch_appid'],
                'mchid' => $result['mchid'],
            ];
        }
        $result = $this->queryTransferInfo($tradeNo);
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' && $result['status'] == 'SUCCESS') {
            return [
                'partner_trade_no' => $result['partner_trade_no'],
                'payment_no' => $result['detail_id'],
                'payment_time' => $result['payment_time'],
                'mch_appid' => $result['appid'],
                'mchid' => $result['mch_id'],
            ];
        }
        Log::error('errcode:' . $result['err_code'] . ' errmsg:' . $result['err_code_des']);
        $this->_errorCode = $result['err_code'];
        $this->_error = $result['err_code_des'];
        throw new Exception('转账失败');
    }

    public function queryTransferInfo($tradeNo)
    {
        require_once EXTEND_PATH . "vm/org/wxpay/WxPay.Api.php";
        $result = \WxPayApi::queryTransferInfo($this->wxPayConfig, $tradeNo);
        return $result;
    }
}