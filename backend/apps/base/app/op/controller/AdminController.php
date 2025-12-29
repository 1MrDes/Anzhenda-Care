<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/2 11:55
 * @description
 */

namespace apps\base\app\op\controller;

use apps\base\core\service\AdminService;
use think\Request;

class AdminController extends BaseOpController
{
    /**
     * @var AdminService
     */
    protected $adminService;

    protected function init()
    {
        parent::init();
        $this->adminService = service('Admin', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('pageSize', 10);
        $res = $this->adminService->pageList($pageSize);
        return $this->success($res);
    }

    public function info(Request $request)
    {
        $params = $request->param();
        $admin = $this->adminService->getByPk($params['id']);
        unset($admin['password'], $admin['salt']);
        $userRoleService = service('AdminRole', SERVICE_NAMESPACE);
        $userRoles = $userRoleService->getByUserId($params['id']);
        $roleIds = array();
        if(!empty($userRoles)) {
            foreach ($userRoles as $userRole) {
                $roleIds[] = $userRole['role_id'];
            }
        }
        $admin['roleIds'] = $roleIds;
        return $this->success($admin);
    }

    public function submit(Request $request)
    {
        $params = $request->param();
        if($params['id'] == 0) {
            $this->adminService->addAdmin($params, $params['roleIds']);
        } else {
            $this->adminService->renew($params, $params['roleIds']);
        }
        return $this->success('');
    }

    public function lock(Request $request)
    {
        $params = $request->param();
        $this->adminService->lock($params['id']);
        return $this->success('');
    }

    public function unlock(Request $request)
    {
        $params = $request->param();
        $this->adminService->unlock($params['id']);
        return $this->success('');
    }

    public function password(Request $request)
    {
        $params = $request->param();
        $this->adminService->setPassword($this->user['id'], $params['password']);
        return $this->success('');
    }
}