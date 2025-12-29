<?php

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class HealthHospital extends BaseModel
{
    /**
     * 按条件分页查询
     * @param array $params
     * @param $pageSize
     * @param array $paginateConfig
     * @return array|null
     * @throws \think\exception\DbException
     */
    public function pageListByParams(array $params, $pageSize, array $paginateConfig = [], array $sortOrder = [])
    {
        if(empty($params)) {
            return $this->pageList($pageSize, false, $paginateConfig);
        }
        $pk = $this->getPk();
        $query = $this;
        foreach ($params as $key => $val) {
            if($key == 'keywords') {
                $query = $query->whereLike('name', '%' . $val . '%');
            } else {
                $query = $query->where($key, $val);
            }
        }
        if(empty($sortOrder)) {
            $sortOrder = [$pk => 'desc'];
        }
        $paginateConfig['list_rows'] = $pageSize;
        return $query->order($sortOrder)->paginate($paginateConfig, false)->toArray();
    }
}