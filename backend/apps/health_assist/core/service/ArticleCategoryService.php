<?php

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\ArticleCategory;
use vm\com\BaseService;
use vm\org\Tree2;

class ArticleCategoryService extends BaseService
{
    private $treeCacheName = 'article_category_tree';
    private $allCacheName = 'article_category_all';

    /**
     * @return ArticleCategory
     */
    protected function getModel()
    {
        return new ArticleCategory();
    }

    public function create(array $data)
    {
        cache($this->allCacheName, null);
        cache($this->treeCacheName, null);
        return parent::create($data);
    }

    public function updateByPk(array $data)
    {
        cache($this->allCacheName, null);
        cache($this->treeCacheName, null);
        return parent::updateByPk($data);
    }

    public function deleteByPk($id)
    {
        cache($this->allCacheName, null);
        cache($this->treeCacheName, null);
        return parent::deleteByPk($id);
    }

    public function findAll()
    {
        if($data = cache($this->allCacheName)) {
            return $data;
        }
        $data = $this->getModel()->findAll();
        if(!empty($data)) {
            $res = [];
            foreach ($data as $datum) {
                $res[$datum['id']] = $datum;
            }
            $data = $res;
            cache($this->allCacheName, $res, 3600*24*365);
        }
        return $data;
    }

    /**
     * 获取下级分类，仅返回第一级子分类
     * @param $parentId
     * @return false|static[]
     */
    public function getByParentId($parentId)
    {
        return $this->getModel()->getByParentId($parentId);
    }

    /**
     * 获取所有下级分类，不包括自己
     * @param int $id
     * @return array
     */
    public function getChildren($id)
    {
        $dataList = array();
        $res = $this->getByParentId($id);
        foreach ($res as $rs) {
            $dataList[] = $rs;
            $dataList = array_merge($dataList, $this->getChildren($rs['id']));
        }
        return $dataList;
    }

    /**
     * 获取所有上级分类，包括自己
     * @param int $id
     * @return array
     */
    public function getParents($id)
    {
        $dataList = $this->getParentsHandler($id);
        $dataList = array_reverse($dataList);
        $dataList[] = $this->getByPk($id);
        return $dataList;
    }

    private function getParentsHandler($id)
    {
        $dataList = [];
        $data = $this->getByPk($id);
        if($data) {
            $parent = $this->getByPk($data['parent_id']);
            if($parent) {
                $dataList[] = $parent;
                if($parent['parent_id'] > 0) {
                    $dataList = array_merge($dataList, $this->getParentsHandler($parent['id']));
                }
            }
        }
        return $dataList;
    }

    public function getTree()
    {
        $data = cache($this->treeCacheName);
        if($data) {
            return $data;
        }
        $res = $this->getModel()->getAll();
        if(empty($res)) {
            return [];
        }
        $tree = new Tree2();
        foreach ($res as $rs) {
            $tree->setNode($rs['id'], $rs['parent_id'], $rs['name'],$rs['sort_order']);
        }
        $data = $tree->getCateTree(0);
        cache($this->treeCacheName, $data, 3600*24*365);
        return $data;
    }
}