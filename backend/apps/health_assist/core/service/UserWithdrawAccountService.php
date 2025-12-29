<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\UserWithdrawAccount;
use vm\com\BaseModel;
use vm\com\BaseService;

class UserWithdrawAccountService extends BaseService
{
    /**
     * @return UserWithdrawAccount
     */
    protected function getModel()
    {
        return new UserWithdrawAccount();
    }

    public function save(array $data)
    {
        return $this->insert($data, true);
    }

    public function getByUserId($userId)
    {
        return $this->info(['user_id' => $userId]);
    }
}