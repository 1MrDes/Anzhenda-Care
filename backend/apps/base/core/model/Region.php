<?php

namespace apps\base\core\model;

use think\Exception;
use vm\com\BaseModel;

class Region extends BaseModel
{
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

    /**
     * 批量删除
     * @param array $regionIds
     * @return int
     */
    public function batchDelete(array $regionIds)
    {
        $result = self::destroy(function ($query) use ($regionIds) {
            $query->where('region_id','in',$regionIds);
        });
        if($result) {
            return $result;
        }
        throw new Exception('删除失败');
    }
}