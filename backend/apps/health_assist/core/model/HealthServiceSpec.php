<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class HealthServiceSpec extends BaseModel
{
    public function findAll($healthServiceId)
    {
        $res = $this->where('health_service_id', '=', $healthServiceId)
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