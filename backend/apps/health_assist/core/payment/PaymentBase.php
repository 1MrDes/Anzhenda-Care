<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2019-03-11
 * Time: 19:57
 */

namespace apps\health_assist\core\payment;


abstract class PaymentBase
{
    const PAYMENT_CODE_WXPAY = 'WxPay';
    const PAYMENT_CODE_ALIPAY = 'AliPay';
    const PAYMENT_CODE_PARTNER_PAY = 'PartnerPay';

    const PAY_STATUS_SUCCESS = 'success';

    protected $_code = '';
    protected $_gateway = '';
    protected $_cfg = array();
    protected $_errorCode = 0;
    protected $_error = '';
    protected $baseUrl;

    public function __construct($cfg)
    {
        $code = strtolower(get_class($this));
        $code = explode('\\', $code);
        $this->_code = array_pop($code);
        $this->_cfg = $cfg;
        $request = request();
        if(ENV == 'release') {
            $this->baseUrl = 'https://pay.open.vanmai.com/qianxun/';
        } else {
            $this->baseUrl = 'http://dev.vanmai.com/vm/qianxun/';
        }
    }

    abstract function getName();

    protected function buildReturnUrl($outTradeNo)
    {
        return '';
    }

    protected function buildNotifyUrl($outTradeNo)
    {
        return $this->baseUrl . 'wxpay.php/pay/notify/' . $outTradeNo;
    }

    protected function buildRefundUrl($outTradeNo)
    {
        return $this->baseUrl . 'wxpay.php/pay/refund/' . $outTradeNo;
    }

    /**
     * 配置参数
     */
    public function config()
    {

    }

    /**
     * 语言项
     */
    public function lang($charset = 'UTF-8')
    {
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * 构建支付表单html
     *
     * @param array $orders , $orders = array('total_amount'=> 10.00,'list' => array( 0 => array('order_id' => 1, 'order_sn' => 14111201010, 'order_amount' => 10.00, 'extension'=> 'normal')))
     * @param string $extra 备注
     * @param string $attach 附加数据
     * @return string
     */
    abstract function buildForm(array $orders, $extra = '', $attach = '');

    /**
     * 获取规范的支付表单数据
     *
     * @param string $method
     * @param array $params
     * @return array
     */
    protected function _createPayform($method = '', $params = array(), $formHtml = '')
    {
        $config = $this->config();
        $lang = $this->lang();
        return array(
            'online' => $config['is_online'],
            'desc' => $lang['desc'],
            'method' => $method,
            'gateway' => $this->_gateway,
            'params' => $params,
            'form_html' => $formHtml
        );
    }

    /**
     * 验证通知结果
     * @param $payOutTradeNo
     * @param $payTotalFee
     * @param $result
     * @return mixed
     */
    abstract function verifyReturn($payOutTradeNo, $payTotalFee, &$result);

    /**
     * 验证异步通知结果
     * @param $payOutTradeNo        商户订单号
     * @param $payTotalFee          支付金额
     * @param $result               支付结果
     * @return mixed
     */
    abstract function verifyNotify($payOutTradeNo, $payTotalFee, &$result);

    /**
     * 发起退款
     * @param string $out_refund_no 唯一的退款流水号
     * @param int $refund_fee 退款金额，单位：元
     * @param array $orderinfo 订单信息
     * @return array
     */
    abstract function refund(array $refundInfo);

    /**
     * 退款详情
     * @param unknown $out_refund_no
     * @param unknown $refund_fee
     * @param array $orderinfo
     * @return array
     */
    abstract function refundDetail($refundId, $outRefundNo);

    /**
     * 获取外部交易号 覆盖基类
     *
     * @param     array $orderList
     * @return    string
     */
    protected function getTradeSn($orderList)
    {

    }

    protected function getRefundNo(array $refundInfo)
    {

    }
}