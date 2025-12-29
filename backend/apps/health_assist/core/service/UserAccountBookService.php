<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\UserAccountBook;
use vm\com\BaseService;

class UserAccountBookService extends BaseService
{

    /**
     * @return UserAccountBook
     */
    protected function getModel()
    {
        return new UserAccountBook();
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return service('User', SERVICE_NAMESPACE);
    }

    public function format($data)
    {
        if($data['op'] == UserAccountBook::OP_DECREASE) {
            $data['op_label'] = '支出';
        } else if($data['op'] == UserAccountBook::OP_INCREASE) {
            $data['op_label'] = '收入';
        }
        return $data;
    }

    /**
     * 记录收支
     * @param int $userId   用户ID
     * @param int $op     类型，具体查看 UserAccountBook::OP_*
     * @param float $money    金额
     * @param int $moneyType    具体查看 UserAccountBook::MONEY_TYPE_*
     * @param null $iidType 第三方id类型，具体查看 UserAccountBook::IID_TYPE_*
     * @param string $iid   第三方id
     * @param mixed|null $extend    扩展数据
     * @param mixed|null $remark
     * @return int
     * @throws Exception
     */
    public function record($userId, $op, $money, $moneyType, $iidType = null, $iid = null, $extend = null, $remark = '')
    {
        $user = $this->getUserService()->getByPk($userId);
        $balance = 0.00;
        if($moneyType == UserAccountBook::MONEY_TYPE_MONEY) {
            $balance = $user['balance'];
        } else if($moneyType == UserAccountBook::MONEY_TYPE_GOLD_COIN) {
            $balance = $user['gold_coin'];
        }
        $data = [
            'user_id' => $userId,
            'op' => $op,
            'amount' => $money,
            'balance' => $balance,
            'money_type' => $moneyType,
            'remark' => $remark,
            'dateline' => time()
        ];
        if($iidType !== null && $iid !== null) {
            $data['iid_type'] = $iidType;
            $data['iid'] = $iid;
        }
        if($extend) {
            $data['extend'] = is_array($extend) || is_object($extend) ? json_encode($extend) : $extend;
        }
        return $this->create($data);
    }

    public function sumAmount($userId, $op)
    {
        return $this->getModel()->sumAmount($userId, $op);
    }
}