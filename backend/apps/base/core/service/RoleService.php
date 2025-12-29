<?php
/**
 * Created by PhpStorm.
 * User: xaq
 * Date: 2017-8-4
 * Time: 15:51
 */

namespace apps\base\core\service;

use apps\base\core\model\Role;
use think\Exception;
use vm\com\BaseService;

class RoleService extends BaseService
{
    /**
     * @return Role
     */
    protected function getModel()
    {
        return new Role();
    }

    public function deleteByPk($id)
    {
        $result = parent::deleteByPk($id);
        if($result) {
            $roleResourceService = service('RoleResource', SERVICE_NAMESPACE);
            $result = $result && $roleResourceService->deleteByRoleId($id);
            $userRoleService = service('AdminRole', SERVICE_NAMESPACE);
            $result = $result && $userRoleService->deleteByRoleId($id);
        }
        if($result) {
            return $result;
        }
        throw new Exception('删除失败');
    }

    /**
     * 设置角色拥有的资源
     * @param $id
     * @param array $resourceIds
     * @return bool
     * @throws Exception
     */
    public function resources($id, array $resourceIds)
    {
        $roleResourceService = service('RoleResource', SERVICE_NAMESPACE);
        $roleResourceService->deleteByRoleId($id);
        if(!empty($resourceIds)) {
            foreach ($resourceIds as $resourceId) {
                $data = [
                    'role_id' => $id,
                    'resource_id' => $resourceId
                ];
                $roleResourceService->create($data);
            }
            return true;
        } else {
            return false;
        }
    }

}