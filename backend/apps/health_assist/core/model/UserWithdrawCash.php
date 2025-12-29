<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class UserWithdrawCash extends BaseModel
{
    const STATUS_AUDITTING = 5; // 审核中
    const STATUS_WAIT_PAY = 10; // 待打款
    const STATUS_PAIED = 20;    // 已打款
    const STATUS_REJECTED = 30;    // 被拒绝

    const PAY_TYPE_WECHAT = 10;  // 微信支付
    const PAY_TYPE_ALIPAY = 20; // 支付宝
    const PAY_TYPE_BANK = 25; // 银行转账
}