<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:52
 */

namespace apps\base\core\service;

use apps\base\core\model\RoleResource;
use think\Exception;
use vm\com\BaseService;

class RoleResourceService extends BaseService
{

    /**
     * @return RoleResource
     */
    protected function getModel()
    {
        return new RoleResource();
    }

    public function getByRoleId($roleId)
    {
        $res = $this->getModel()->getByRoleId($roleId);
        return $res;
    }

    public function deleteByRoleId($roleId)
    {
        $result = $this->getModel()->deleteByRoleId($roleId);
        return $result;
    }

    public function deleteByResourceId($resourceId)
    {
        $result = $this->getModel()->deleteByResourceId($resourceId);
        return $result;
    }

}