<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2018/11/19
 * Time: 10:48
 */

namespace apps\health_assist\core\model;


use think\facade\Db;
use vm\com\BaseModel;

class PayTrade extends BaseModel
{
    const STATUS_WAIT_PAY = 10; // 待付款
    const STATUS_PAIED = 30;    // 已付款
    const STATUS_CLOSED = 50;   // 已关闭，在指定时间段内未支付时关闭的交易、在交易完成全额退款成功时关闭的交易
    const STATUS_FINISHED = 100;    // 已结束,此状态后不可进行任何操作

    const ORDER_TYPE_HEALTH_SERVICE_ORDER = 'HealthServiceOrder'; // 陪诊订单
    const ORDER_TYPE_REALNAME_VERIFY_TASK = 'RealnameVerifyTask'; // 实名认证
    const ORDER_TYPE_USER_VIP_LEVEL_ORDER = 'UserVipLevelOrder'; // 用户vip订单
    const ORDER_TYPE_REALNAME_AUTH_TASK = 'RealnameAuthTask'; // 实名认证
    const ORDER_TYPE_UNION_HEALTH_ASSIST_SERVICE_ORDER = 'UnionHealthAssistServiceOrder'; // 联盟陪诊订单

    public static function getStatusLabel($status)
    {
        switch ($status) {
            case self::STATUS_WAIT_PAY:
                return '待付款';
            case self::STATUS_PAIED:
                return '已付款';
            case self::STATUS_CLOSED:
                return '已关闭';
            case self::STATUS_FINISHED:
                return '已结束';
            default:
                return 'N/A';
        }
    }

    /**
     * 支付成功更新付款状态
     * @param $tradeNo
     * @param $transactionId
     * @param $payTime
     * @return int
     */
    public function paySuccess($tradeNo, $transactionId, $payTime)
    {

        $sql = "update " . $this->getTable()
            . " set pay_status=".self::STATUS_PAIED.", transaction_id='".$transactionId."', pay_time=$payTime"
            . " where trade_no='$tradeNo' and pay_status=" . self::STATUS_WAIT_PAY;
        return Db::execute($sql);
    }
}