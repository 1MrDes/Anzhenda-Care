<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:51
 */

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\AdminResource;
use think\Facade\Cache;
use think\Exception;
use vm\com\BaseService;
use vm\org\Tree;
use vm\org\Tree2;

class AdminResourceService extends BaseService
{
    private $treeCacheName = 'backend:resources:tree';
    private $optionsCacheName = 'backend:resources:options';

    /**
     * @return AdminResource
     */
    protected function getModel()
    {
        return new AdminResource();
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
        if(!empty($res)) {
            $tree = new Tree2();
            foreach ($res as $rs) {
                $tree->setNode($rs['id'], $rs['parent_id'], $rs['name'],1);
            }
            $data = $tree->getCateTree(1);
        } else {
            $data = [];
        }
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
        Cache::rm($this->optionsCacheName);
        Cache::rm($this->treeCacheName);
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
        Cache::rm($this->optionsCacheName);
        Cache::rm($this->treeCacheName);
        return $result;
    }

    /**
     * 删除
     * @param $id
     * @throws Exception
     */
    public function deleteByPk($id)
    {
        $options = $this->getOptions();
        $children = array();
        if(!empty($options)) {
            foreach ($options as $v) {
                if($v['id'] == $id) {
                    $children = $v['childs'];
                    break;
                }
            }
        }
        $children[] = $id;
        foreach ($children as $v) {
            $result = parent::deleteByPk($v);
            if($result) {
                $roleResourceService = service('RoleBelongResource', SERVICE_NAMESPACE);
                $roleResourceService->deleteByResourceId($v);
            } else {
                throw new Exception('删除失败');
            }
        }
        Cache::rm($this->optionsCacheName);
        Cache::rm($this->treeCacheName);
    }

}