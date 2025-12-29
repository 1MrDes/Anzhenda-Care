<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\UserWithdrawCash;
use apps\health_assist\core\model\SystemNotice;
use apps\health_assist\core\model\UserPlatform;
use apps\health_assist\core\model\UserSystemNotice;
use apps\health_assist\core\payment\WxPay;
use think\Exception;
use think\facade\Log;
use vm\com\BaseService;
use vm\org\queue\QueueFactory;

class UserWithdrawCashService extends BaseService
{

    /**
     * @return UserWithdrawCash
     */
    protected function getModel()
    {
        return new UserWithdrawCash();
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return service('User', SERVICE_NAMESPACE);
    }

    /**
     * @return UserAccountBookService
     */
    private function getUserAccountService()
    {
        return service('UserAccountBook', SERVICE_NAMESPACE);
    }

    /**
     * @return SiteConfigService
     */
    private function getSiteConfigService()
    {
        return service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return SystemNoticeService
     */
    protected function getSystemNoticeService()
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
     * @return UserPlatformService
     */
    protected function getUserPlatformService()
    {
        return service('UserPlatform', SERVICE_NAMESPACE);
    }

    /**
     * @return UserWithdrawAccountService
     */
    protected function getUserWithdrawAccountService()
    {
        return service('UserWithdrawAccount', SERVICE_NAMESPACE);
    }

    public function format($data)
    {
        switch ($data['status']) {
            case UserWithdrawCash::STATUS_AUDITTING:
                $data['status_label'] = '审核中';
                break;
            case UserWithdrawCash::STATUS_WAIT_PAY:
                $data['status_label'] = '待打款';
                break;
            case UserWithdrawCash::STATUS_PAIED:
                $data['status_label'] = '已打款';
                break;
            case UserWithdrawCash::STATUS_REJECTED:
                $data['status_label'] = '未通过审核';
                break;
            default:
                $data['status_label'] = 'N/A';
                break;
        }

        switch ($data['pay_type']) {
            case UserWithdrawCash::PAY_TYPE_WECHAT:
                $data['pay_type_label'] = '微信支付';
                break;
            case UserWithdrawCash::PAY_TYPE_ALIPAY:
                $data['pay_type_label'] = '支付宝';
                break;
            case UserWithdrawCash::PAY_TYPE_BANK:
                $data['pay_type_label'] = '银行转账';
                break;
            default:
                $data['pay_type_label'] = 'N/A';
                break;
        }
        return $data;
    }

    public function onBatchAudit(array $ids, $status)
    {
        if(!in_array($status, [UserWithdrawCash::STATUS_WAIT_PAY, UserWithdrawCash::STATUS_REJECTED])) {
            throw new Exception('参数错误');
        }
        foreach ($ids as $id) {
            $this->update([
                'status' => $status
            ], [
                'id' => $id,
                'status' => UserWithdrawCash::STATUS_AUDITTING
            ]);
        }
        return true;
    }

    /**
     * 申请提现
     * @param int $userId               --用户ID
     * @param float $amount             --金额
     * @return false
     * @throws Exception
     */
    public function onApply($userId, $amount)
    {
        if(!is_numeric($amount) || $amount <= 0) {
            throw new Exception('参数错误');
        }
        $withdrawAccount = $this->getUserWithdrawAccountService()->getByUserId($userId);
        if(empty($withdrawAccount)) {
            throw new Exception('请先设置提现账号');
        }
        $withdrawCashSetting = $this->getSiteConfigService()->getValueByCode('withdraw_cash');
        $withdrawCashMinmoney = floatval($withdrawCashSetting['withdraw_cash_min_money']);
        if ($amount < $withdrawCashMinmoney) {
            throw new Exception('提现金额不能少于'.$withdrawCashMinmoney.'元');
        }
        $user = $this->getUserService()->getByPk($userId);
        if($user['withdrawing_balance'] > 0) {
            throw new Exception('您还有提现申请正在处理中');
        }
        if($amount > $user['withdraw_balance']) {
            throw new Exception('可提现金额不足');
        }
        $data2 = [
            'user_id' => $userId,
            'amount' => $amount,
            'status' => UserWithdrawCash::STATUS_AUDITTING,
            'apply_time' => time(),
            'alipay_name' => $withdrawAccount['alipay_name'],
            'alipay_account' => $withdrawAccount['alipay_account'],
            'bank_name' => $withdrawAccount['bank_name'],
            'bank_branch_name' => $withdrawAccount['bank_branch_name'],
            'bank_account' => $withdrawAccount['bank_account'],
            'bank_card_no' => $withdrawAccount['bank_card_no'],
        ];
        $applyId = $this->create($data2);
        $result = $this->getUserService()->applyWithdraw($userId, $amount, $applyId);
        if(!$result) {
            throw new Exception('申请失败');
        }
        return true;
    }

    /**
     * 拒绝提现申请
     * @param int $id
     * @param string $remark        --备注信息
     * @return bool
     * @throws Exception
     */
    public function onReject($id, $remark)
    {
        $result = $this->update([
            'status' => UserWithdrawCash::STATUS_REJECTED,
            'remark' => $remark
        ], [
            'id' => $id,
            'status' => UserWithdrawCash::STATUS_AUDITTING
        ]);
        if(!$result) {
            throw new Exception('操作失败');
        }
        $record = $this->getByPk($id);
        $this->getUserService()->rejectWithdraw($record['user_id'], $record['amount'], $id);

        // 发送通知
        $systemNoticeId = $this->getSystemNoticeService()->create([
            'title' => '提现申请处理结果',
            'content' => '您的提现申请已被拒绝，原因为：' . $remark,
            'type' => SystemNotice::TYPE_SINGLE,
            'status' => SystemNotice::STATUS_WAIT_PULL,
            'recipient_id' => $record['user_id'],
            'manager_id' => 0,
            'url' => json_encode([
                'weapp' => '/pages/my/withdraw_cash/index',
                'web' => '',
                'app' => ''
            ])
        ]);
        return true;
    }

    /**
     * 同意提现申请
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function onAgree($id)
    {
        $result = $this->update([
            'status' => UserWithdrawCash::STATUS_WAIT_PAY
        ], [
            'id' => $id,
            'status' => UserWithdrawCash::STATUS_AUDITTING
        ]);
        if(!$result) {
            throw new Exception('操作失败');
        }
        $record = $this->getByPk($id);
        // 发送通知
        $systemNoticeId = $this->getSystemNoticeService()->create([
            'title' => '提现申请处理结果',
            'content' => '您的提现申请已审核通过。',
            'type' => SystemNotice::TYPE_SINGLE,
            'status' => SystemNotice::STATUS_WAIT_PULL,
            'recipient_id' => $record['user_id'],
            'manager_id' => 0,
            'url' => json_encode([
                'weapp' => '/pages/my/withdraw_cash/index',
                'web' => '',
                'app' => ''
            ])
        ]);
        return true;
    }

    /**
     * 给申请人打款
     * @param int $id
     * @param int $payType  --付款方式
     * @return bool
     */
    public function onPay($id, $payType)
    {
        if($payType == UserWithdrawCash::PAY_TYPE_WECHAT) {
            $this->onTransferByWePay($id);
        } else if($payType == UserWithdrawCash::PAY_TYPE_ALIPAY) {

        } else if($payType == UserWithdrawCash::PAY_TYPE_BANK) {

        } else {
            throw new Exception('付款方式错误');
        }
        return $this->onFinish($id);
    }

    /**
     * 完成打款
     * @param int $id
     * @throws Exception
     */
    public function onFinish($id)
    {
        $result = $this->update([
            'status' => UserWithdrawCash::STATUS_PAIED
        ], [
            'id' => $id,
            'status' => UserWithdrawCash::STATUS_WAIT_PAY
        ]);
        if(!$result) {
            throw new Exception('操作失败');
        }
        $record = $this->getByPk($id);
        $this->getUserService()->finishWithdraw($record['user_id'], $record['amount']);
        // 发送通知
        $systemNoticeId = $this->getSystemNoticeService()->create([
            'title' => '提现已打款',
            'content' => '您的提现已打款，请注意查收。',
            'type' => SystemNotice::TYPE_SINGLE,
            'status' => SystemNotice::STATUS_WAIT_PULL,
            'recipient_id' => $record['user_id'],
            'manager_id' => 0,
            'url' => json_encode([
                'weapp' => '/pages/my/withdraw_cash/index',
                'web' => '',
                'app' => '',
            ])
        ]);
        return true;
    }

    /**
     * 转账到微信钱包
     * @param $id
     * @return bool
     * @throws Exception
     */
    private function onTransferByWePay($id)
    {
        $record = $this->getByPk($id);
        if(empty($record['pay_trade_no'])) {
            $tradeNo = date('ymdHis') . rand_string(8, 1);
            while (parent::info(['pay_trade_no' => $tradeNo])) {
                $tradeNo = date('ymdHis') . rand_string(8, 1);
            }
        } else {
            $tradeNo = $record['pay_trade_no'];
        }
        $userPlaforms = $this->getUserPlatformService()->getByUserId($record['user_id']);
        if(empty($userPlaforms)) {
            throw new Exception('该用户未在小程序登录过，无法用微信转账');
        }
        $userPlaform = null;
        foreach ($userPlaforms as $item) {
            if($item['platform'] == UserPlatform::PLATFORM_WX_MINI) {
                $userPlaform = $item;
                break;
            }
        }
        if(empty($userPlaform)) {
            throw new Exception('该用户未在小程序登录过，无法用微信转账');
        }
        $appId = $userPlaform['appid'];

        $siteConfigService = $this->getSiteConfigService();
        $appName = $siteConfigService->getValueByCode('app_name');
        $weapps = $siteConfigService->getValueByCode('weapps');
        $payConfig = [
            'app_id' => $appId,
            'app_secret' => $weapps[$appId]['weapp_app_secret'],
            'mch_id' => $siteConfigService->getValueByCode('weapp_pay_mch_id'),
            'pay_key' => $siteConfigService->getValueByCode('weapp_pay_sign_key'),
        ];
        $payment = new WxPay($payConfig);

        $result = $payment->transfer($tradeNo,$userPlaform['open_id'], $record['amount'], $appName . '-提现');
        $res = $this->update([
            'pay_trade_no' => $tradeNo,
            'pay_type' => UserWithdrawCash::PAY_TYPE_WECHAT,
            'payment_name' => '微信支付',
            'payment_iid' => $result['payment_no'],
            'pay_time' => strtotime($result['payment_time']),
        ], [
            'id' => $id,
            'status' => UserWithdrawCash::STATUS_WAIT_PAY
        ]);
        if(!$res) {
            throw new Exception('转账失败');
        }
        return true;
    }
}