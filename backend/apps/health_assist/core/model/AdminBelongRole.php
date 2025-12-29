<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:14
 */

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class AdminBelongRole extends BaseModel
{
    public function deleteByUserId($userId)
    {
        return self::destroy(['user_id' => $userId]);
    }

    public function deleteByRoleId($roleId)
    {
        return self::destroy(['role_id' => $roleId]);
    }

    public function getByUserId($userId)
    {
        return $this->where('user_id', $userId)->select()->toArray();
    }
}