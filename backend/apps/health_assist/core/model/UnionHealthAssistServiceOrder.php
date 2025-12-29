<?php

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class UnionHealthAssistServiceOrder extends BaseModel
{
    const FEE_TYPE_COMPETITIVE = 10; // 陪诊师抢单 competitive
    const FEE_TYPE_OFFER = 20; // 陪诊师报价

    const ORDER_FEE_PAY_TYPE_BALANCE = 10;  // 余额支付
    const ORDER_FEE_PAY_TYPE_ONLINE = 20;  // 在线支付

    const STATUS_WAIT_HEALTH_ASSISTANT_OFFER = 3; // 陪诊师报价中
    const STATUS_WAIT_PLACE_USER_PAY = 5; // 待发单人付款
    const STATUS_WAIT_HEALTH_ASSISTANT_RECEIVE = 10; // 待陪诊师接单
    const STATUS_HEALTH_ASSISTANT_RECEIVED = 20; // 陪诊师已接单
    const STATUS_HEALTH_ASSISTANT_SERVICE_FINISH = 30; // 陪诊师服务完成
    const STATUS_CANCELED = 40; // 订单取消
    const STATUS_WAIT_PLACE_USER_CONFIRM_FINISH = 90; // 待发单人确认完成
    const STATUS_SUCCESS = 100; // 服务成功

    /**
     * 按条件分页查询
     * @param array $params
     * @param $pageSize
     * @param array $paginateConfig
     * @return array|null
     * @throws \think\exception\DbException
     */
    public function pageListByParams(array $params, $pageSize, array $paginateConfig = [], array $sortOrder = [])
    {
        if(empty($params)) {
            return $this->pageList($pageSize, false, $paginateConfig, $sortOrder);
        }

        $query = $this;
        foreach ($params as $key => $val) {
            if(is_array($val)) {
                $query = $query->where($key, $val[0], $val[1]);
            } else if($key == 'type' && $val == 'competitive') {
                $query = $query->where('receive_user_id', 0);
                $query = $query->whereIn('status', [
                    self::STATUS_WAIT_HEALTH_ASSISTANT_OFFER,
                    self::STATUS_WAIT_PLACE_USER_PAY,
                    self::STATUS_WAIT_HEALTH_ASSISTANT_RECEIVE
                ]);
                $query = $query->where('order_fee_pay_status', 1);
            } else {
                $query = $query->where($key, $val);
            }
        }
        if(empty($sortOrder)) {
            $pk = $this->getPk();
            $sortOrder = [$pk => 'desc'];
//            $query = $query->order($pk, 'desc');
        }
//        else {
//            foreach ($sortOrder as $field => $order) {
//                $query = $query->order($field, $order);
//            }
//        }
        $query = $query->order($sortOrder);

        if(empty($paginateConfig)) {
            $query = $query->paginate($pageSize, false);
        } else {
            $paginateConfig['list_rows'] = $pageSize;
            $query = $query->paginate($paginateConfig, false);
        }
        return $query->toArray();
    }
}