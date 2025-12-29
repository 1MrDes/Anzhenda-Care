<?php

namespace apps\base\core\service;

use apps\base\core\model\Region;
//use think\Facade\Cache;
use think\Exception;
use think\Validate;
use vm\com\BaseService;
use vm\org\Tree;
//use vm\org\Zoo;

class RegionService extends BaseService
{
    private $treeCacheName = 'region_tree';
    private $allCacheName = 'region_all';

    const ZOO_REGION_TREE_PATH = '/regions/tree';

    /**
     * @return Region|\vm\com\BaseModel
     */
    protected function getModel()
    {
        return new Region();
    }

    public function getByPk($id)
    {
        return parent::info([
            'region_id' => $id
        ]);
    }

    /**
     * 获取下级地区，仅返回第一级子地区
     * @param $parentId
     * @return false|static[]
     */
    public function getByParentId($parentId)
    {
        return $this->getModel()->getByParentId($parentId);
    }

    /**
     * 添加或更新地区
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function submit(array $data)
    {
//        $validate = new Validate([
//            'parent_id'  => 'require',
//            'region_name' => 'require',
//            'sort_order' => 'require',
//            'first_char' => 'require'
//        ]);
//        if(!$validate->check($data)) {
//            throw new Exception('参数错误');
//        }
        if($data['parent_id'] > 0) {
            $parent = $this->getByPk($data['parent_id']);
            $data['region_type'] = $parent['region_type'] + 1;
        } else {
            $data['region_type'] = 1;
        }
        if($data['region_id'] == 0) {   //   添加
            $result = $this->create($data);
        } else {    // 编辑
            $result = $this->update($data, ['region_id' => $data['region_id']]);
        }
        if($result) {
            \cache($this->treeCacheName, null);
            \cache($this->allCacheName, null);
            $this->broadcast();
        }
    }

    /**
     * 删除地区
     * 将删除所有下级地区
     * @param $regionId
     * @return int
     */
    public function deleteByPk($regionId)
    {
        // 所有下级地区
        $children = $this->getChildren($regionId);
        if(!empty($children)) {
            foreach ($children as $v) {
                parent::delete(['region_id' => $v['region_id']]);
            }
        }
        parent::delete(['region_id' => $regionId]);
        \cache($this->treeCacheName, null);
        \cache($this->allCacheName, null);
        $this->broadcast();
    }

    /**
     * 获取所有下级地区，不包括自己
     * @param $regionId
     * @return array
     */
    public function getChildren($regionId)
    {
        $regions = array();
        $res = $this->getByParentId($regionId);
        foreach ($res as $rs) {
            $regions[] = $rs;
            $regions = array_merge($regions, $this->getChildren($rs['region_id']));
        }
        return $regions;
    }

    /**
     * 获取所有上级地区，包括自己
     * @param $regionId
     * @return array
     */
    public function getParents($regionId)
    {
        $regions = $this->getParentsHandler($regionId);
        $regions = array_reverse($regions);
        $region = $this->getByPk($regionId);
        $regions[] = $region;
        return $regions;
    }

    private function getParentsHandler($regionId)
    {
        $regions = array();
        $region = $this->getByPk($regionId);
        if($region) {
            $parent = $this->getByPk($region['parent_id']);
            if($parent && $parent['parent_id'] > 0) {
                $regions[] = $parent;
                $regions = array_merge($regions, $this->getParentsHandler($parent['region_id']));
            }
        }
        return $regions;
    }

    public function getTree()
    {
        $data = \cache($this->treeCacheName);
        if($data) {
            return $data;
        }
        $nodes = $this->getModel()->getAll();
        $treeKit = new Tree();
        $treeKit->setTree($nodes, 'region_id', 'parent_id', 'region_name');
        $treeKit->setIdKeyName('value');
        $treeKit->setValueKeyName('label');
        $treeKit->setChildrenKeyName('cities');
        $treeKit->setShowChildrenKeyIfNull(false);
        $data = $treeKit->getArrayList(1);
        \cache($this->treeCacheName, $data, 3600*24*30);
        return $data;
    }

    public function getAll(array $params = null)
    {
        if($data = \cache($this->allCacheName)) {
            return $data;
        }
        $data = [];
        $items = parent::getAll();
        if($items) {
            foreach ($items as $item) {
                $data[$item['region_id']] = $item;
            }
            \cache($this->allCacheName, $data, 3600*24*365);
        }
        return $data;
    }

    private function broadcast()
    {
//        $config = config('zookeeper');
//        $zoo = Zoo::instance($config);
//        if(!$zoo->exists(self::ZOO_REGION_TREE_PATH)) {
//            $zoo->create(self::ZOO_REGION_TREE_PATH, null);
//        }
//        $url = request()->scheme() . '://' . $_SERVER['HTTP_HOST'] . getContextPath() . 'static/regions.json';
//        $data = [
//            'lastTime' => time(),
//            'downloadUrl' => $url
//        ];
//        $zoo->set(self::ZOO_REGION_TREE_PATH, json_encode($data));
    }
}