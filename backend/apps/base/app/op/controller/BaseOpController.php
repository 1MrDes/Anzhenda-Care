<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/3/31 15:01
 * @description
 */
namespace apps\base\app\op\controller;

use think\Exception;
use vm\com\RestController;

abstract class BaseOpController extends RestController
{
    protected $user;

    protected function init()
    {
        if(!defined('IN_OP')) {
            die('hack attemping');
        }
        parent::init();
        $this->user = $this->getUser();
    }

    protected function auth()
    {
        $user = $this->getUser();
        $request = request();
        $path = $request->pathinfo();
        list($controller, $action) = explode('/', $path);
        $authConfig = include __DIR__ . '/../config/auth.php';
        if(isset($authConfig[$controller . '/*'])) {
            if($authConfig[$controller . '/*'] == 'non') {
                return true;
            } else if ($authConfig[$controller . '/*'] == 'login') {
                if($user) {
                    return true;
                } else {
                    throw new Exception('未登录');
                }
            }
        } else if(isset($authConfig[$controller . '/' . $action])) {
            if($authConfig[$controller . '/' . $action] == 'non') {
                return true;
            } else if ($authConfig[$controller . '/' . $action] == 'login') {
                if($user) {
                    return true;
                } else {
                    throw new Exception('未登录');
                }
            }
        } else {    // 其他资源根据角色判断权限
            if($user == null) {
                throw new Exception('未登录');
            }
            if($user['is_super'] == 1) {
                return true;
            }
            $adminService = service('Admin', SERVICE_NAMESPACE);
            $roleResources = $adminService->getResources($user['id']);
            if(in_array('/' . $controller . '/*', $roleResources)
                || in_array('/' . $controller . '/' . $action, $roleResources)) {
                return true;
            } else {
                throw new Exception('未授权');
            }
        }
    }

    protected function getUser()
    {
        $request = request();
        $accessToken = $request->header('access-token');
        if(empty($accessToken)) {
            $accessToken = $request->param('access_token');
            if(empty($accessToken)) {
                return null;
            }
        }
        $adminService = service('Admin', SERVICE_NAMESPACE);
        $user = $adminService->getAuth($accessToken);
        return $user;
    }
}