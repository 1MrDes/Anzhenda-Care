<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2019-03-12
 * Time: 18:07
 */

namespace apps\health_assist\core\payment;


interface IPayOrder
{
    /**
     * 创建支付交易后回调
     * @param int $orderId
     * @param string $tradeNo
     * @return mixed
     */
    function onPayTradeCreated($orderId, $tradeNo);

    /**
     * 支付成功后回调
     * @param string $tradeNo              --本站支付流水号
     * @param string $transactionId        --第三方支付平台流水号
     * @param string $payTime              --付款时间
     * @param string $paymentName          --支付方式名称
     * @return mixed
     */
    function onPaySuccess($tradeNo, $transactionId, $payTime, $paymentName);

    /**
     * 根据订单号获取订单信息
     * @param string $orderSn
     * @return mixed
     */
    function getByOrderSn($orderSn);
}