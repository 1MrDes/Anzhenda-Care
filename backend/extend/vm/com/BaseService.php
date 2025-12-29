<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-7-5
 * Time: 18:01
 */

namespace vm\com;


use think\Exception;

abstract class BaseService
{
    public function __construct()
    {
        $this->init();
    }

    protected function init(){}

    /**
     * @return BaseModel
     */
    abstract protected function getModel();

    /**
     * 获取当前模型的数据库查询对象
     * @return \think\db\BaseQuery
     */
    public function getDb()
    {
        return $this->getModel()->db();
    }

    /**
     * 获取所有数据
     * @param $params
     * @return false|null|static[]
     */
    public function getAll(array $params = null)
    {
        return $this->getModel()->getAll($params);
    }

    /**
     * 分页列表
     * @param $pageSize
     * @return array|null
     */
    public function pageList($pageSize, $simple = false, $config = [])
    {
        return $this->getModel()->pageList($pageSize, $simple, $config);
    }

    /**
     * 按条件分页查询
     * @param $params
     * @param $pageSize
     * @param $paginateConfig
     * @throws Exception
     * @return array|null
     */
    public function pageListByParams(array $params, $pageSize, array $paginateConfig = [], array $sortOrder = [])
    {
        return $this->getModel()->pageListByParams($params, $pageSize, $paginateConfig, $sortOrder);
    }

    /**
     * 查询一条记录
     * @param $param
     * @throws Exception
     * @return null|static
     */
    public function info($param)
    {
        return $this->getModel()->info($param);
    }

    /**
     * 根据主键获取数据
     * @param $id
     * @throws Exception
     * @return null|static
     */
    public function getByPk($id)
    {
        return $this->getModel()->getByPk($id);
    }

    /**
     * 获取最新一条记录
     * @param array $params
     * @param array $order
     * @return array
     */
    public function getLastItem(array $params, array $order = [])
    {
        return $this->getModel()->getLastItem($params, $order);
    }

    public function create(array $data)
    {
        $model = $this->getModel();
        if($model) {
            $pk = $model->getPk();
            if(isset($data[$pk])) {
                unset($data[$pk]);
            }
            $result = $model::create($data);
            if($result) {
                return $model->getLastInsID();
//                return $result->$pk;
            } else {
                throw new Exception('添加失败');
            }
        }
        throw new Exception('添加失败');
    }

    public function insert(array $data, $replace = false)
    {
        $model = $this->getModel();
        if($model) {
            $pk = $model->getPk();
            if(isset($data[$pk])) {
                unset($data[$pk]);
            }
            $result = $model::create($data, [], $replace);
            if($result) {
                return $model->getLastInsID();
//                return $result->$pk;
            } else {
                throw new Exception('添加失败');
            }
        }
        throw new Exception('添加失败');
    }

    /**
     * 根据主键更新
     * @param array $data
     * @return $this|bool
     * @throws
     */
    public function updateByPk(array $data)
    {
        $model = $this->getModel();
        if($model) {
            $pk = $model->getPk();
            $model::update($data, [$pk => $data[$pk]]);
            $result = $model->getNumRows();
            return $result > 0;
//            if($result) {
//                return $result;
//            } else {
//                throw new Exception('更新失败');
//            }
        }
        throw new Exception('更新失败');
    }

    public function update(array $data, array $where)
    {
        $model = $this->getModel();
        if($model) {
            $model::update($data, $where);
            $result = $model->getNumRows();
            return $result > 0;
//            if($result) {
//                return $result;
//            } else {
//                throw new Exception('更新失败');
//            }
        }
        throw new Exception('更新失败');
    }

    /**
     * 根据主键删除
     * @param $id
     * @return bool|int
     * @throws
     */
    public function deleteByPk($id)
    {
        $model = $this->getModel();
        if($model) {
            $model::destroy($id);
            $result = $model->getNumRows();
            return $result > 0;
//            if($result) {
//                return $result;
//            } else {
//                throw new Exception('删除失败');
//            }
        }
        throw new Exception('删除失败');
    }

    public function delete(array $params)
    {
        $model = $this->getModel();
        if($model) {
//            $model::destroy($params);
            foreach ($params as $key => $val) {
                $model->where($key, $val);
            }
            $model->delete();
            $result = $model->getNumRows();
            return $result > 0;
//            if($result) {
//                return $result;
//            } else {
//                throw new Exception('删除失败');
//            }
        }
        throw new Exception('删除失败');
    }

    public function count(array $params = [])
    {
        $model = $this->getModel();
        foreach ($params as $key => $val) {
            if(is_array($val)) {
                $model = $model->where($key, $val[0], $val[1]);
            } else {
                $model = $model->where($key, $val);
            }
        }
        return $model->count();
    }

    public function sum($field, array $params = [])
    {
        $model = $this->getModel();
        foreach ($params as $key => $val) {
            if(is_array($val)) {
                $model = $model->where($key, $val[0], $val[1]);
            } else {
                $model = $model->where($key, $val);
            }
        }
        return $model->sum($field);
    }

    public function avg($field, array $params = [])
    {
        $model = $this->getModel();
        foreach ($params as $key => $val) {
            if(is_array($val)) {
                $model = $model->where($key, $val[0], $val[1]);
            } else {
                $model = $model->where($key, $val);
            }
        }
        return $model->avg($field);
    }

    public function increase($field, array $where = [], $step = 1)
    {
        $result = $this->getModel()->increase($field, $where, $step);
        if($this->getModel()->getNumRows() > 0) {
            return true;
        }
        throw new Exception('更新失败');
    }

    public function decrease($field, array $where = [], $step = 1)
    {
        $result = $this->getModel()->decrease($field, $where, $step);
        if($this->getModel()->getNumRows() > 0) {
            return true;
        }
        throw new Exception('更新失败');
    }
}