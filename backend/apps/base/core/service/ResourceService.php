<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:51
 */

namespace apps\base\core\service;

use apps\base\core\model\Resource;
use think\Facade\Cache;
use think\Exception;
use vm\com\BaseService;
use vm\org\Tree;
use vm\org\Tree2;

class ResourceService extends BaseService
{
    private $treeCacheName = 'backend:resources:tree';
    private $optionsCacheName = 'backend:resources:options';

    /**
     * @return Resource
     */
    protected function getModel()
    {
        return new Resource();
    }

    /**
     * @return RoleResourceService
     */
    private function getRoleResourceService()
    {
        return service('RoleResource', SERVICE_NAMESPACE);
    }

    public function getTree()
    {
//        if($data = Cache::get($this->treeCacheName)) {
//            return $data;
//        }
        $res = $this->getAll();
        if($res) {
            $tree = new Tree();
            $tree->setTree($res, 'id', 'parent_id', 'name');
            $data = $tree->getArrayList(0);
//            Cache::set($this->treeCacheName, $data);
        } else {
            $data = [];
        }

        return $data;
    }

    public function getOptions()
    {
//        if($data = Cache::get($this->optionsCacheName)) {
//            return $data;
//        }
        $res = $this->getAll();
        if($res) {
            $tree = new Tree();
            $tree->setTree($res, 'id', 'parent_id', 'name');
            $data = $tree->getOptions();
//            Cache::set($this->treeCacheName, $data);
        } else {
            $data = [];
        }
//        if(!empty($res)) {
//            $tree = new Tree2();
//            foreach ($res as $rs) {
//                $tree->setNode($rs['id'], $rs['parent_id'], $rs['name'],1);
//            }
//            $data = $tree->getCateTree(1);
//        } else {
//            $data = [];
//        }
//        Cache::set($this->optionsCacheName, $data);
        return $data;
    }

    public function create(array $data)
    {
        $data['parent_id'] = empty($data['parent_id']) ? 0 : $data['parent_id'];
        if($data['parent_id'] == 0) {
            $data['level'] = 1;
        } else {
            $parent = $this->getByPk($data['parent_id']);
            $data['level'] = 1 + $parent['level'];
        }
        $result = parent::create($data);
        \cache($this->optionsCacheName, null);
        \cache($this->treeCacheName, null);
        return $result;
    }

    public function update(array $data, array $where = [])
    {
        $data['parent_id'] = empty($data['parent_id']) ? 0 : $data['parent_id'];
        if($data['parent_id'] == 0) {
            $data['level'] = 1;
        } else {
            $parent = $this->getByPk($data['parent_id']);
            $data['level'] = 1 + $parent['level'];
        }
        $result = parent::updateByPk($data);
        \cache($this->optionsCacheName, null);
        \cache($this->treeCacheName, null);
        return $result;
    }

    /**
     * 删除
     * @param $id
     * @throws Exception
     */
    public function deleteByPk($id)
    {
        $childs = $this->getChildren($id);
        $children = [];
        foreach ($childs as $child) {
            $children[] = $child['id'];
        }
        $children[] = $id;

        $roleResourceService = $this->getRoleResourceService();
        foreach ($children as $v) {
            $result = parent::deleteByPk($v);
            if($result) {
                $roleResourceService->deleteByResourceId($v);
            } else {
                throw new Exception('删除失败');
            }
        }
        \cache($this->optionsCacheName, null);
        \cache($this->treeCacheName, null);
    }

    /**
     * 获取下级地区，仅返回第一级子地区
     * @param $parentId
     * @return false|static[]
     */
    public function getByParentId($parentId)
    {
        return parent::getAll(['parent_id' => $parentId]);
    }

    /**
     * 获取所有下级，不包括自己
     * @param int $id
     * @return array
     */
    public function getChildren($id)
    {
        $data = array();
        $res = $this->getByParentId($id);
        foreach ($res as $rs) {
            $data[] = $rs;
            $data = array_merge($data, $this->getChildren($rs['id']));
        }
        return $data;
    }

    /**
     * 获取所有上级，包括自己
     * @param $id
     * @return array
     */
    public function getParents($id)
    {
        $data = $this->getParentsHandler($id);
        $data = array_reverse($data);
        $data[] = $this->getByPk($id);
        return $data;
    }

    private function getParentsHandler($id)
    {
        $data = array();
        $rs = $this->getByPk($id);
        if($rs) {
            $parent = $this->getByPk($rs['parent_id']);
            if($parent && $parent['parent_id'] > 0) {
                $data[] = $parent;
                $data = array_merge($data, $this->getParentsHandler($parent['id']));
            }
        }
        return $data;
    }
}