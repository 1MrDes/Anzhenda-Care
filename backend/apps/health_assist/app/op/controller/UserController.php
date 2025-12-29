<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 15:01
 * @description
 */

namespace apps\health_assist\app\op\controller;

use apps\health_assist\core\service\AdminService;
use think\Exception;
use apps\health_assist\app\Request;
use vm\com\logic\FileLogic;

class UserController extends BaseHealthAssistOpController
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
        $this->fileLogic = logic('File', '\vm\com\logic\\');
        $this->fileLogic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
    }

    public function login(Request $request)
    {
        $params = $request->param();
        $admin = $this->adminService->login($params['username'], $params['password']);
        if($admin) {
            $admin['upload_token'] = $this->fileLogic->genToken('health_assist_admin_' . $admin['id']);
        }
        return $this->success(['user' => $admin]);
    }

    public function logout(Request $request)
    {
        $this->adminService->logout($request->param('access-token', ''));
        return $this->success();
    }

    public function login_by_token(Request $request)
    {
        $accessToken = $request->param('access_token', '');
        if(empty($accessToken)) {
            throw new Exception('access_token为空');
        }
        $admin = $this->adminService->loginByToken($accessToken);
        if($admin) {
            $admin['upload_token'] = $this->fileLogic->genToken('health_assist_admin_' . $admin['id']);
        }
        return $this->success(['user' => $admin]);
    }
}