<?php

namespace apps\base\core\model;

use vm\com\BaseModel;

class SmsPlatform extends BaseModel
{
    public function getByCode($code)
    {
        return $this->info(['sms_code' => $code]);
    }

    public function getByWeight()
    {
        $list = array();
        $res = $this->order('weight', 'asc')->select();
        if($res) {
            foreach ($res as $rs) {
                $list[] = $rs->getData();
            }
        }
        return $list;
    }
}
