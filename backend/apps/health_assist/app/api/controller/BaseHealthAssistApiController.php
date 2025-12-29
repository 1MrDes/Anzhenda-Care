<?php


namespace apps\health_assist\app\api\controller;


use think\Exception;
use vm\com\RestController;

abstract class BaseHealthAssistApiController extends RestController
{
    protected $user;

    protected function init()
    {
        if(!defined('IN_API')) {
            die('hack attemping');
        }
        $this->user = $this->getUser();
        parent::init();
    }

    protected function auth()
    {
        $user = $this->user;
        $request = request();
        $path = $request->pathinfo();
        list($controller, $action) = explode('/', $path);
        $controller = strtolower($controller);
        $action = strtolower($action);
        $authConfig = include __DIR__ . '/../config/auth.php';
        if(isset($authConfig[$controller . '/*'])) {
            if($authConfig[$controller . '/*'] == 'non') {
                return true;
            } else if ($authConfig[$controller . '/*'] == 'login') {
                if($user) {
                    return true;
                } else {
                    throw new Exception('未登录', 106);
                }
            }
        } else if(isset($authConfig[$controller . '/' . $action])) {
            if($authConfig[$controller . '/' . $action] == 'non') {
                return true;
            } else if ($authConfig[$controller . '/' . $action] == 'login') {
                if($user) {
                    return true;
                } else {
                    throw new Exception('未登录', 106);
                }
            }
        }
        return true;
    }

    private function getUser()
    {
        $request = request();
        $accessToken = $request->header('access-token');
        if(empty($accessToken)) {
            return null;
        }
        $userService = service('User', SERVICE_NAMESPACE);
        $user = $userService->getAuth($accessToken);
        return $user;
    }
}