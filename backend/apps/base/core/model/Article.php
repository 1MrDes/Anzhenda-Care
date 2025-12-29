<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/2 11:51
 * @description
 */

namespace apps\base\core\model;


use vm\com\BaseModel;

class Article extends BaseModel
{
    public function getByCode($code)
    {
        $rs = $this->where('code', $code)->find();
        if($rs) {
            return $rs->getData();
        }
        return null;
    }

    public function getByCateId($cateId, $isShow = -1, $pageSize = 10)
    {
        $query = $this->where('cate_id', $cateId);
        if($isShow != -1) {
            $query->where('is_show', $isShow);
        }
        $res = $query->order('sort_order', 'asc')->paginate($pageSize);
        $data = array();
        if ($res) {
            foreach ($res as $rs) {
                $data[] = $rs->getData();
            }
        }
        return $data;
    }
}