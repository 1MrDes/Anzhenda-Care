<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class UserAccountBook extends BaseModel
{
    const OP_INCREASE = 1;
    const OP_DECREASE = 0;

    const MONEY_TYPE_MONEY = 10;        // 人民币
    const MONEY_TYPE_GOLD_COIN = 20;    // 金币

    const IID_TYPE_WITHDRAW_CASH = 10;  //用户提现
    const IID_TYPE_REALNAME_VERIFY_TASK = 15;  //实名认证
    const IID_TYPE_HEALTH_SERVICE_ASSISTANT_SALARY = 20;  //陪诊服务佣金
    const IID_TYPE_REALNAME_AUTH_TASK = 25;  // 实名认证
    const IID_TYPE_USER_VIP_LEVEL = 30;  // 购买会员
    const IID_TYPE_UNION_HEALTH_ASSIST_SERVICE_ORDER = 40;    // 联盟陪诊订单

    public function sumAmount($userId, $op)
    {
        $amount = $this->where('op', $op)->where('user_id', $userId)->sum('amount');
        return $amount;
    }
}