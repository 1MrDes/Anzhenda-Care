<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\RealnameAuthTask;
use apps\health_assist\core\model\UserAccountBook;
use apps\health_assist\core\payment\IPayOrder;
use think\Exception;
use vm\com\BaseModel;
use vm\com\BaseService;
use vm\org\qcloud\FaceDetect;

class RealnameAuthTaskService extends BaseService implements IPayOrder
{
    /**
     * @return RealnameAuthTask
     */
    protected function getModel()
    {
        return new RealnameAuthTask();
    }

    /**
     * @return SiteConfigService
     */
    private function getSiteConfigService()
    {
        return service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return UserAccountBookService
     */
    protected function getUserAccountBookService()
    {
        return service('UserAccountBook', SERVICE_NAMESPACE);
    }

    /**
     * @return SystemNoticeService
     */
    private function getSystemNoticeService()
    {
        return service('SystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return UserSystemNoticeService
     */
    protected function getUserSystemNoticeService()
    {
        return service('UserSystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return service('User', SERVICE_NAMESPACE);
    }

    /**
     * @return FaceDetect
     */
    protected function getFaceDetect()
    {
        $secretId = $this->getSiteConfigService()->getValueByCode('qcloud_secret_id');
        $secretKey = $this->getSiteConfigService()->getValueByCode('qcloud_secret_key');
        return new FaceDetect($secretId, $secretKey);
    }

    private function genSn()
    {
        do {
            $tradeNo = date('ymdHis') . rand_string(8, 1);
            $isExists = $this->info([
                'sn' => $tradeNo
            ]);
        } while($isExists);
        return $tradeNo;
    }

    public function getByUserId($userId)
    {
        return $this->info(['user_id' => $userId]);
    }

    public function getByOrderSn($sn)
    {
        return $this->info(['sn' => $sn]);
    }

    public function createTask(array $data)
    {
        $task = $this->getByUserId($data['user_id']);
        if($task && $task['pay_status'] == RealnameAuthTask::PAY_STATUS_PAIED) {
            $this->updateByPk([
                'id' => $task['id'],
                'real_name' => $data['real_name'],
                'idcard_no' => $data['idcard_no'],
            ]);
            return [
                'id' => $task['id'],
                'sn' => $task['sn'],
                'pay_status' => RealnameAuthTask::PAY_STATUS_PAIED
            ];
        }
        $payMoney = ($this->getSiteConfigService()->getValueByCode('service_prices'))['idcard_verify'];
        $sn = $this->genSn();
        $id = $this->insert([
            'sn' => $sn,
            'user_id' => $data['user_id'],
            'pay_money' => $payMoney,
            'real_name' => $data['real_name'],
            'idcard_no' => $data['idcard_no'],
            'pay_status' => RealnameAuthTask::PAY_STATUS_WAIT_PAY,
            'create_time' => time()
        ], true);
        return [
            'id' => $id,
            'sn' => $sn,
            'pay_status' => RealnameAuthTask::PAY_STATUS_WAIT_PAY
        ];
    }

    /**
     * 获取认证url
     * @param int $userId           --用户ID
     * @param string $url           --回调地址
     * @return array
     * @throws Exception
     */
    public function getFaceDetectUrl($userId, $url)
    {
        $task = $this->getByUserId($userId);
        if(empty($task) || $task['pay_status'] == RealnameAuthTask::PAY_STATUS_WAIT_PAY) {
            throw new Exception('请先完成付款');
        }
        if($task['auth_result'] == 1) {
            throw new Exception('您已完成实名认证');
        }
        $ruleId = $this->getSiteConfigService()->getValueByCode('qcloud_face_detect_rule_id');
        $detectUrl = $this->getFaceDetect()->getFaceDetectUrl($ruleId, $userId, $task['real_name'], $task['idcard_no'], $url);
        return $detectUrl;
    }

    public function onFaceIdDetectCompleted($userId)
    {
        $ruleId = $this->getSiteConfigService()->getValueByCode('qcloud_face_detect_rule_id');
        $detectInfo = $this->getFaceDetect()->getFaceIdDetectInfo($ruleId, $userId);
        if($detectInfo['Text']
            && $detectInfo['Text']['ErrCode'] == 0
            && $detectInfo['Text']['LiveStatus'] == 0
            && $detectInfo['Text']['Comparestatus'] == 0) {
            $data = [
                'real_name' => $detectInfo['Text']['Name'],
                'idcard_no' => $detectInfo['Text']['IdCard'],
                'idcard_img_frontend' => $detectInfo['IdCardData']['OcrFront'],
                'idcard_img_backend' => $detectInfo['IdCardData']['OcrBack'],
                'auth_result' => 1,
            ];
            if($this->update($data, ['user_id' => $userId])) {
                $this->getUserService()->updateByPk([
                    'id' => $userId,
                    'real_name' => $detectInfo['Text']['Name'],
                    'realname_auth_status' => 1
                ]);
            }
            return true;
        } else {
            return false;
        }
    }

    public function onPayTradeCreated($orderId, $tradeNo)
    {
        return $this->updateByPk([
            'id' => $orderId,
            'pay_trade_no' => $tradeNo
        ]);
    }

    public function onPaySuccess($tradeNo, $transactionId, $payTime, $paymentName)
    {
        $order = $this->info([
            'pay_trade_no' => $tradeNo
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
        if ($order['pay_status'] != RealnameAuthTask::PAY_STATUS_WAIT_PAY) {
//            throw new Exception('只有已确认待付款订单才可确认付款');
            return true;
        }
        $data = [
            'pay_status' => RealnameAuthTask::PAY_STATUS_PAIED,
            'pay_time' => $payTime,
        ];
        if ($this->update($data, [
            'id' => $orderId,
            'pay_status' => RealnameAuthTask::PAY_STATUS_WAIT_PAY
        ])) {
            $this->getUserAccountBookService()->record($order['user_id'], UserAccountBook::OP_DECREASE, $order['pay_money'], UserAccountBook::MONEY_TYPE_MONEY, UserAccountBook::IID_TYPE_REALNAME_AUTH_TASK, $orderId, null, '实名认证');
            return true;
        }
        throw new Exception('操作失败');
    }
}