<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/1/2 11:50
 * @description
 */

namespace apps\base\core\model;


use vm\com\BaseModel;

class ArticleCategory extends BaseModel
{
    public function getByCode($code)
    {
        $rs = $this->where('code', $code)->find();
        if($rs) {
            return $rs->getData();
        } else {
            return null;
        }
    }

    public function getByParentId($parentId)
    {
        $res = $this->where('parent_id', $parentId)->select();
        if($res) {
            $data = array();
            foreach ($res as $rs) {
                $data[] = $rs->getData();
            }
            return $data;
        }
        return null;
    }
}