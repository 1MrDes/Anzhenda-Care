<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:14
 */

namespace apps\base\core\model;

use vm\com\BaseModel;

class AdminRole extends BaseModel
{
    public function deleteByUserId($userId)
    {
        return $this->where('user_id', $userId)->delete();
    }

    public function deleteByRoleId($roleId)
    {
        return $this->where('role_id', $roleId)->delete();
    }

    public function getByUserId($userId)
    {
        $res = $this->getAll(['user_id' => $userId]);
        return $res;
    }
}