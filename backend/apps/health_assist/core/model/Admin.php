<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 15:04
 * @description
 */

namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class Admin extends BaseModel
{
    public function getByUsername($username)
    {
        $admin = $this->where('username', $username)->find()->toArray();
        return $admin;
    }
}