<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class HealthServiceOrder extends BaseModel
{
    // 10-未确认,20-已确认、待付款,25-待发货,30-已发货,40-已完成,50-已取消,60-已退款,70-退款中
    const ORDER_STATUS_UNCONFIRMED = 10;
    const ORDER_STATUS_CONFIRMED = 20;
    const ORDER_STATUS_WAIT_SHIP = 25;
    const ORDER_STATUS_SHIPPED = 30;
    const ORDER_STATUS_FINISHED = 40;
    const ORDER_STATUS_CANCELLED = 50;
    const ORDER_STATUS_REFUNDED = 60;
    const ORDER_STATUS_REFUNDING = 70;
    const ORDER_STATUS_ACTING = 80; // 服务中

    const PAYMENT_STATUS_UNPAIED = 0;    // 未付款
    const PAYMENT_STATUS_PAIED = 1;      // 已付款

    // 配送状态 10-待发货；20-已发货；30-已退货
    const SHIPPING_STATUS_WAIT_SHIP = 10;
    const SHIPPING_STATUS_SHIPPED = 20;
    const SHIPPING_STATUS_REFUNDED = 30;

    // 退款状态
    const REFUND_STATUS_AUDITING = 10;  //  审核中
    const REFUND_STATUS_GOODS_RETURNING = 20;  //  退货中
    const REFUND_STATUS_PAYING = 30;  //  打款中
    const REFUND_STATUS_FINISHED = 40;  //  已完成

    // 配送方式 10-线下服务；20-物流配送
    const SHIPPING_TYPE_OFFLINE = 10;
    const SHIPPING_TYPE_EXPRESS = 20;
}