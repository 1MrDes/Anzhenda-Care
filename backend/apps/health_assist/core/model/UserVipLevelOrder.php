<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class UserVipLevelOrder extends BaseModel
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

    const PAY_STATUS_UNPAIED = 0;    // 未付款
    const PAY_STATUS_PAIED = 1;      // 已付款
}