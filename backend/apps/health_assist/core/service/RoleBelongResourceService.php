<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:52
 */

namespace apps\health_assist\core\service;

use apps\health_assist\core\model\RoleBelongResource;
use think\Exception;
use vm\com\BaseService;

class RoleBelongResourceService extends BaseService
{

    /**
     * @return RoleBelongResource
     */
    protected function getModel()
    {
        return new RoleBelongResource();
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