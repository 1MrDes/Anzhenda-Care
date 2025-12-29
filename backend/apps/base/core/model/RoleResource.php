<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:14
 */

namespace apps\base\core\model;

use vm\com\BaseModel;

class RoleResource extends BaseModel
{

    public function getByRoleId($roleId)
    {
        return $this->getAll(['role_id' => $roleId]);
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