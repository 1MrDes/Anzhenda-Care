<?php
/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-15 14:27
 * Description:
 */

namespace apps\base\core\model;


use vm\com\BaseModel;

class SmsTemplate extends BaseModel
{
    public function getByCode($code)
    {
        return $this->info(['code' => $code]);
    }
}