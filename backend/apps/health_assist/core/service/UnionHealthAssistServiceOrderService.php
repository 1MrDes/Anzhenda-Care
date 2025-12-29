<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\UnionHealthAssistServiceOrder;
use apps\health_assist\core\model\UserAccountBook;
use apps\health_assist\core\payment\IPayOrder;
use think\Exception;
use think\facade\Log;
use vm\com\BaseModel;
use vm\com\BaseService;
use vm\com\logic\FileLogic;
use vm\com\logic\RegionLogic;
use vm\org\lock\DisLockFactory;

class UnionHealthAssistServiceOrderService extends BaseService implements IPayOrder
{
    /**
     * @var RegionLogic
     */
    private $regionLogic;

    /**
     * @var FileLogic
     */
    protected $fileLogic;

    /**
     * @return UnionHealthAssistServiceOrder
     */
    protected function getModel()
    {
        return new UnionHealthAssistServiceOrder();
    }

    /**
     * @return SiteConfigService
     */
    private function getSiteConfigService()
    {
        return service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return service('User', SERVICE_NAMESPACE);
    }

    private function getRegionLogic()
    {
        if($this->regionLogic !== null) {
            return $this->regionLogic;
        }
        $this->regionLogic = logic('Region', '\vm\com\logic\\');
        $this->regionLogic->init([
            'rpc_server' => env('rpc_base.host') . '/region',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
        return $this->regionLogic;
    }

    private function getFileLogic()
    {
        if($this->fileLogic !== null) {
            return $this->fileLogic;
        }
        $this->fileLogic = logic('File', '\vm\com\logic\\');
        $this->fileLogic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
        return $this->fileLogic;
    }

    /**
     * @return UserAccountBookService
     */
    private function getUserAccountBookService()
    {
        return service('UserAccountBook', SERVICE_NAMESPACE);
    }

    private function genOrderSn()
    {
        do {
            $sn = date('ymdHis') . rand_string(8, 1);
            $isExists = $this->info([
                'order_sn' => $sn
            ]);
        } while($isExists);
        return $sn;
    }

    public function create(array $data)
    {
        $placeUser = $this->getUserService()->getByPk($data['place_user_id']);
        if($placeUser['realname_auth_status'] != 1) {
            throw new Exception('请先完成实名认证', ERROR_REALNAME_AUTH_INVALID);
        }
        $data['order_sn'] = $this->genOrderSn();
        $data['place_time'] = time();
        if($data['fee_type'] == UnionHealthAssistServiceOrder::FEE_TYPE_COMPETITIVE) {
            $data['status'] = UnionHealthAssistServiceOrder::STATUS_WAIT_PLACE_USER_PAY;
        } else if($data['fee_type'] == UnionHealthAssistServiceOrder::FEE_TYPE_OFFER) {
            $data['status'] = UnionHealthAssistServiceOrder::STATUS_WAIT_HEALTH_ASSISTANT_OFFER;
        }

        $orderId = parent::create($data);
        return [
            'order_id' => $orderId,
            'order_sn' => $data['order_sn']
        ];
    }

    public function getByOrderSn($sn)
    {
        return $this->info([
            'order_sn' => $sn
        ]);
    }

    public function onCancel($sn)
    {
        $order = $this->getByOrderSn($sn);
        if ($order['receive_user_id'] > 0 || $order['status'] == UnionHealthAssistServiceOrder::STATUS_CANCELED) {
            throw new Exception('订单号无效');
        }
        if($this->updateByPk([
            'id' => $order['id'],
            'status' => UnionHealthAssistServiceOrder::STATUS_CANCELED
        ])) {
            if($order['order_fee_pay_status'] == 1) {   // 已支付服务费，退还费用
                if($order['order_fee_pay_type'] == UnionHealthAssistServiceOrder::ORDER_FEE_PAY_TYPE_BALANCE) {     // 退回到余额
                    $this->getUserService()->increaseWithdrawBalance(
                                $order['place_user_id'],
                                $order['fee'],
                                true,
                                '取消陪诊订单，退还服务费',
                                UserAccountBook::IID_TYPE_UNION_HEALTH_ASSIST_SERVICE_ORDER,
                                $order['id']
                    );
                } else if($order['order_fee_pay_type'] == UnionHealthAssistServiceOrder::ORDER_FEE_PAY_TYPE_ONLINE) {     // 在线支付，按原路退回
                    //TODO:

                }
            }
            return true;
        }
        throw new Exception('订单取消失败');
    }

    public function onPayBalance($sn)
    {
        $order = $this->getByOrderSn($sn);
        if ($order['order_fee_pay_status'] == 1 || $order['status'] == UnionHealthAssistServiceOrder::STATUS_CANCELED) {
            throw new Exception('订单号无效');
        }
        $user = $this->getUserService()->getByPk($order['place_user_id']);
        if($user['withdraw_balance'] < $order['fee']) {
            throw new Exception('账户余额不足');
        }
        $this->getUserService()->decreaseWithdrawBalance(
            $order['place_user_id'],
            $order['fee'],
            true,
            '支付陪诊服务费',
            UserAccountBook::IID_TYPE_UNION_HEALTH_ASSIST_SERVICE_ORDER,
            $order['id']
        );
        $data = [
            'id' => $order['id'],
            'order_fee_pay_status' => 1,
            'order_fee_pay_type' => UnionHealthAssistServiceOrder::ORDER_FEE_PAY_TYPE_BALANCE,
            'order_fee_pay_time' => time()
        ];
        if($order['fee_type'] == UnionHealthAssistServiceOrder::FEE_TYPE_COMPETITIVE) {
            $data['status'] = UnionHealthAssistServiceOrder::STATUS_WAIT_HEALTH_ASSISTANT_RECEIVE;
        } else if($order['fee_type'] == UnionHealthAssistServiceOrder::FEE_TYPE_OFFER) {
            $data['status'] = UnionHealthAssistServiceOrder::STATUS_WAIT_PLACE_USER_CONFIRM_FINISH;
        }
        return $this->updateByPk($data);
    }

    public function onReceive($receiveUserId, $sn)
    {
        $receiveUser = $this->getUserService()->getByPk($receiveUserId);
        if($receiveUser['realname_auth_status'] != 1) {
            throw new Exception('请先完成实名认证', ERROR_REALNAME_AUTH_INVALID);
        }
        $order = $this->getByOrderSn($sn);
        if($order['competitive_end_time'] < time()) {
            throw new Exception('抢单时间已截止');
        }
        $lockKey = 'union_health_assist_service_order_receive:' . $sn;
        $lock = DisLockFactory::instance()->lock($lockKey, 8000);
        if(!$lock) {
            throw new Exception('请重试');
        }
        try {
            if($order['receive_user_id'] > 0) {
                throw new Exception('该订单已由其他陪诊师接单');
            }
            $result = $this->update([
                'receive_user_id' => $receiveUserId,
                'status' => UnionHealthAssistServiceOrder::STATUS_WAIT_PLACE_USER_CONFIRM_FINISH,
                'receive_time' => time()
            ], [
                'order_sn' => $sn,
                'receive_user_id' => 0
            ]);
            if($result) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            throw new Exception($e->getMessage());
        } finally {
            DisLockFactory::instance()->unlock($lockKey);
        }
    }

    public function format(array $data)
    {
        $data['region'] = '';
        $regions = $this->getRegionLogic()->parents($data['city_id']);
        foreach ($regions as $region) {
            $data['region'] .= $region['region_name'];
        }

        $data['service_type_label'] = '';
        $serviceTypes = $this->getSiteConfigService()->getValueByCode('union_health_service_type');
        foreach ($serviceTypes as $serviceType) {
            if($serviceType['id'] == $data['service_type']) {
                $data['service_type_label'] = $serviceType['name'];
                break;
            }
        }

        switch ($data['status']) {
            case UnionHealthAssistServiceOrder::STATUS_WAIT_HEALTH_ASSISTANT_OFFER:
                $data['status_label'] = '陪诊师报价中';
                break;
            case UnionHealthAssistServiceOrder::STATUS_WAIT_PLACE_USER_PAY:
                $data['status_label'] = '待付款';
                break;
            case UnionHealthAssistServiceOrder::STATUS_WAIT_HEALTH_ASSISTANT_RECEIVE:
                $data['status_label'] = '待接单';
                break;
            case UnionHealthAssistServiceOrder::STATUS_HEALTH_ASSISTANT_RECEIVED:
                $data['status_label'] = '已接单';
                break;
            case UnionHealthAssistServiceOrder::STATUS_HEALTH_ASSISTANT_SERVICE_FINISH:
                $data['status_label'] = '服务完成';
                break;
            case UnionHealthAssistServiceOrder::STATUS_WAIT_PLACE_USER_CONFIRM_FINISH:
                $data['status_label'] = '待确认完成';
                break;
            case UnionHealthAssistServiceOrder::STATUS_CANCELED:
                $data['status_label'] = '订单取消';
                break;
            case UnionHealthAssistServiceOrder::STATUS_SUCCESS:
                $data['status_label'] = '服务成功';
                break;
            default:
                $data['status_label'] = 'N/A';
        }
        return $data;
    }

    public function onPayTradeCreated($orderId, $tradeNo)
    {
        return $this->updateByPk([
            'id' => $orderId,
            'order_fee_pay_trade_no' => $tradeNo
        ]);
    }

    public function onPaySuccess($tradeNo, $transactionId, $payTime, $paymentName)
    {
        $order = $this->info([
            'order_fee_pay_trade_no' => $tradeNo
        ]);
        if (!$order) {
            throw new Exception('订单不存在');
        }
        return $this->payConfirm($order['id'], $payTime, $paymentName, $transactionId);
    }

    /**
     * 后台确认已付款
     * @param int $orderId                  --订单ID
     * @param int $payTime                  --付款时间
     * @param string $payPlatformName       --支付平台名称
     * @param string $payPlatformTradeNo    --支付平台流水号
     * @return bool
     * @throws
     */
    public function payConfirm($orderId, $payTime, $payPlatformName, $payPlatformTradeNo)
    {
        $order = $this->getByPk($orderId);
        if ($order['order_fee_pay_status'] != 0) {
//            throw new Exception('只有已确认待付款订单才可确认付款');
            return true;
        }
        $data = [
            'order_fee_pay_status' => 1,
            'order_fee_pay_time' => $payTime,
        ];
        if ($this->update($data, [
            'id' => $orderId,
            'order_fee_pay_status' => 0
        ])) {
            $this->getUserAccountBookService()->record($order['place_user_id'], UserAccountBook::OP_DECREASE, $order['fee'], UserAccountBook::MONEY_TYPE_MONEY, UserAccountBook::IID_TYPE_UNION_HEALTH_ASSIST_SERVICE_ORDER, $orderId, null, '陪诊服务费');
            return true;
        }
        throw new Exception('操作失败');
    }
}