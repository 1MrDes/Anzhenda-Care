<?php


namespace apps\health_assist\core\model;


use vm\com\BaseModel;

class HealthServiceCategory extends BaseModel
{
    public function findAll()
    {
        $res = $this->order('sort_order', 'ASC')->select();
        $data = [];
        if($res) {
            foreach ($res as $rs) {
                $data[] = $rs->getData();
            }
            return $data;
        }
        return $data;
    }

    public function getByParentId($parentId)
    {
        $res = $this->where('parent_id', $parentId)->order('sort_order', 'asc')->select();
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