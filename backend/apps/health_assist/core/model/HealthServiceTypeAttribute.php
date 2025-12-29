<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class HealthServiceTypeAttribute extends BaseModel
{
    public function findAll($typeId)
    {
        $res = $this->where('type_id', '=', $typeId)
                    ->order('sort_order', 'ASC')
                    ->select();
        $data = [];
        if($res) {
            foreach ($res as $rs) {
                $data[] = $rs->getData();
            }
            return $data;
        }
        return $data;
    }
}