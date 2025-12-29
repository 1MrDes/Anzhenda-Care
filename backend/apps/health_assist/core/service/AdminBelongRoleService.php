<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:31
 */

namespace apps\health_assist\core\service;


use apps\health_assist\core\model\AdminBelongRole;
use think\Exception;
use vm\com\BaseModel;
use vm\com\BaseService;

class AdminBelongRoleService extends BaseService
{
    /**
     * @return AdminBelongRole|BaseModel
     */
    protected function getModel()
    {
        return new AdminBelongRole();
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