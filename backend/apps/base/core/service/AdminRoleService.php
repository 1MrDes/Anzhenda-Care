<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:31
 */

namespace apps\base\core\service;


use apps\base\core\model\AdminRole;
use think\Exception;
use vm\com\BaseModel;
use vm\com\BaseService;

class AdminRoleService extends BaseService
{
    /**
     * @return AdminRole|BaseModel
     */
    protected function getModel()
    {
        return new AdminRole();
    }

    public function deleteByUserId($userId)
    {
        $result = $this->getModel()->deleteByUserId($userId);
        return $result;
    }

    public function deleteByRoleId($roleId)
    {
        $result = $this->getModel()->deleteByRoleId($roleId);
        return $result;
    }

    public function getByUserId($userId)
    {
        $data = $this->getModel()->getByUserId($userId);
        return $data;
    }

}