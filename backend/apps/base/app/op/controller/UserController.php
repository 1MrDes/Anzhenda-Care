<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 15:01
 * @description
 */

namespace apps\base\app\op\controller;

use apps\base\core\logic\FileLogic;
use apps\base\core\service\AdminService;
use think\Request;

class UserController extends BaseOpController
{
    /**
     * @var AdminService
     */
    protected $adminService;

    /**
     * @var FileLogic
     */
    protected $fileLogic;

    protected function init()
    {
        parent::init();
        $this->adminService = service('Admin', SERVICE_NAMESPACE);
        $this->fileLogic = logic('File', LOGIC_NAMESPACE);
    }

    public function login(Request $request)
    {
        $params = $request->param();
        $admin = $this->adminService->login($params['username'], $params['password']);
        if($admin) {
            $admin['upload_token'] = $this->fileLogic->genToken('base_admin_' . $admin['id']);
        }
        return $this->success([
            'user' => $admin
        ]);
    }

    public function logout(Request $request)
    {
        $this->adminService->logout($request->param('access_token', ''));
        return $this->success();
    }

}