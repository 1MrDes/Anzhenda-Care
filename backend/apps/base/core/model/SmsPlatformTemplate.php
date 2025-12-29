<?php
/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-15 14:33
 * Description:
 */

namespace apps\base\core\model;

use vm\com\BaseModel;

class SmsPlatformTemplate extends BaseModel
{
    public function deleteByTempId($tempId)
    {
        return self::destroy(['temp_id' => $tempId]);
    }

    public function getByTempId($tempId)
    {
        return $this->getAll(['temp_id' => $tempId]);
    }
}