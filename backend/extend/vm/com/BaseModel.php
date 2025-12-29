<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2017/7/2
 * Time: 23:02
 */

namespace vm\com;


use think\Exception;
use think\facade\Db;
use think\Model;

class BaseModel extends Model
{

    public function getFields()
    {
        static $fileds = null;
        if($fileds !== null) {
            return $fileds;
        }
        $fileds = $this->db()->getConnection()->getTableFields($this->getTable());
        return $fileds;
    }

    public function add(array $data)
    {
        return parent::insert($data, true);
    }

    /**
     * 获取所有数据
     * @param $params
     * @throws
     * @return false|null|static[]
     */
    public function getAll(array $params = null)
    {
        if(empty($params)) {
            $res = $this->select();
        } else {
            $query = $this;
            foreach ($params as $key => $val) {
                $query = $query->where($key, $val);
            }
            $res = $query->select();
        }
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
     * 分页列表
     * @param $pageSize
     * @throws
     * @return array|null
     */
    public function pageList($pageSize, $simple = false, $config = [])
    {
        $pk = $this->getPk();
        $query = $this->order($pk, 'desc');
        if(empty($config)) {
            $query = $query->paginate($pageSize, $simple);
        } else {
            $config['list_rows'] = $pageSize;
            $query = $query->paginate($config, $simple);
        }
        return $query->toArray();
    }

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

        $query = $this;
        foreach ($params as $key => $val) {
            if(is_array($val)) {
                $query = $query->where($key, $val[0], $val[1]);
            } else {
                $query = $query->where($key, $val);
            }
        }
        if(empty($sortOrder)) {
            $pk = $this->getPk();
            $sortOrder = [$pk => 'desc'];
        }
        $query = $query->order($sortOrder);

        if(empty($paginateConfig)) {
            $query = $query->paginate($pageSize, false);
        } else {
            $paginateConfig['list_rows'] = $pageSize;
            $query = $query->paginate($paginateConfig, false);
        }
        return $query->toArray();
    }

    /**
     * 查询一条记录
     * @param array $params
     * @throws Exception
     * @return null|static
     */
    public function info(array $params)
    {
        if(empty($params)) {
            $rs = $this->find();
        } else {
            $query = $this;
            foreach ($params as $key => $val) {
                $query = $query->where($key, $val);
            }
            $rs = $query->find();
        }

        if($rs) {
            return $rs->getData();
        } else {
            return null;
        }
    }

    /**
     * 根据主键获取数据
     * @param $id
     * @throws Exception
     * @return null|static
     */
    public function getByPk($id)
    {
        $pk = $this->getPk();
        $rs = $this->where($pk, $id)->find();
        if($rs) {
            return $rs->getData();
        } else {
            return null;
        }
    }

    public function getLastItem(array $params, array $order = [])
    {
        $query = $this;
        foreach ($params as $key => $val) {
            $query = $query->where($key, $val);
        }
        foreach ($order as $key => $value) {
            $query = $query->order($key, $value);
        }
        $pk = $this->getPk();
        if($pk) {
            $query = $query->order($pk, 'DESC');
        }
        $data = $query->find();
        return $data ? $data->toArray() : null;
    }

    public function increase($field, array $where = [], $step = 1)
    {
        $query = $this;
        foreach ($where as $key => $val) {
            if(is_array($val)) {
                $query = $query->where($key, $val[0], $val[1]);
            } else {
                $query = $query->where($key, $val);
            }
        }
        $query->inc($field, $step)->update();
        return $this->getNumRows() > 0;
    }

    public function decrease($field, array $where = [], $step = 1)
    {
        $query = $this;
        foreach ($where as $key => $val) {
            if(is_array($val)) {
                $query = $query->where($key, $val[0], $val[1]);
            } else {
                $query = $query->where($key, $val);
            }
        }
        $query->dec($field, $step)->update();
        return $this->getNumRows() > 0;
    }
}