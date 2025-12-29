<?php

namespace apps\base\core\model;

use vm\com\BaseModel;

class SiteConfig extends BaseModel
{
    public function getNonHiddenItems()
    {
        $res = $this->where('type', '<>', 'hidden')->order('sort_order asc')->select();
        $data = array();
        if($res) {
            foreach ($res as $rs) {
                $data[] = $rs->getData();
            }
        }
        return $data;
    }

    public function updateByCode($code, array $data)
    {
        return $this->where('code', $code)->save($data);
    }
}