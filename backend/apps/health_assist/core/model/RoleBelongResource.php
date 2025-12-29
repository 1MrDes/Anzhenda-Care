<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:14
 */

namespace apps\health_assist\core\model;

use vm\com\BaseModel;

class RoleBelongResource extends BaseModel
{

    public function getByRoleId($roleId)
    {
        return $this->where('role_id', $roleId)->select()->toArray();
    }

    public function deleteByRoleId($roleId)
    {
        return self::destroy(['role_id' => $roleId]);
    }

    public function deleteByResourceId($resourceId)
    {
        return self::destroy(['resource_id' => $resourceId]);
    }

}