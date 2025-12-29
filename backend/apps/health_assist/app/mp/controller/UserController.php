<?php


namespace apps\health_assist\app\mp\controller;

use apps\health_assist\core\service\UserService;
use think\Exception;
use apps\health_assist\app\Request;
use vm\com\logic\SmsLogic;

class UserController extends BaseHealthAssistMpController
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var SmsLogic
     */
    private $smsLogic;

    protected function init()
    {
        parent::init();
        $this->userService = service('User', SERVICE_NAMESPACE);
        $this->smsLogic = logic('Sms', '\vm\com\logic\\');
        $this->smsLogic->init([
            'rpc_server' => env('rpc_base.host') . '/sms',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
    }

    public function login(Request $request)
    {
        $mobile = $request->param('mobile');
        $captcha = $request->param('captcha');
        if(!check_mobile($mobile)) {
            throw new Exception('手机号错误');
        }
        if(!$this->smsLogic->verifyCaptcha('login', $mobile, $captcha)) {
            throw new Exception('短信验证码错误');
        }
        $user = $this->userService->getByMobile($mobile);
        if($user) {
            $user['access_token'] = $this->userService->genAccessToken($user);
            $user['upload_token'] = $this->userService->getUploadToken($user['id']);
        } else {
            $data = [
                'nick' => $mobile,
                'mobile' => $mobile,
                'password' => rand_string(6),
                'intro' => ''
            ];
            $id = $this->userService->create($data);
            $user = $this->userService->getByPk($id);
            $user['access_token'] = $this->userService->genAccessToken($user);
            $user['upload_token'] = $this->userService->getUploadToken($user['id']);
        }
        return $this->success(['user' => $user]);
    }

    public function login_by_token(Request $request)
    {
        $accessToken = $request->param('access_token');
        $user = $this->userService->getAuth($accessToken);
        $user['access_token'] = $accessToken;
        $user['upload_token'] = $this->userService->getUploadToken($user['id']);
        return $this->success(['user' => $user]);
    }

    public function logout(Request $request)
    {
        $request = request();
        $accessToken = $request->header('access-token');
        $this->userService->logout($accessToken);
        return $this->success();
    }
}