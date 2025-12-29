<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:15
 */

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class AdminRole extends BaseModel
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
        $res = $this->where('user_id', $userId)->select();
        $data = array();
        if($res) {
            foreach ($res as $rs) {
                $data[] = $rs->getData();
            }
        }
        return $data;
    }
}