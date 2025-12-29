<?php


namespace apps\health_assist\app\mp\controller;


use think\Exception;
use apps\health_assist\app\Request;
use vm\com\logic\CaptchaLogic;
use vm\com\logic\SmsLogic;

class SmsCaptchaController extends BaseHealthAssistMpController
{
    /**
     * @var SmsLogic
     */
    private $smsLogic;

    /**
     * @var CaptchaLogic
     */
    private $captchaLogic;

    protected function init()
    {
        parent::init();
        $this->smsLogic = logic('Sms', '\vm\com\logic\\');
        $this->smsLogic->init([
            'rpc_server' => env('rpc_base.host') . '/sms',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
        $this->captchaLogic = logic('Captcha', '\vm\com\logic\\');
    }

    public function send(Request $request)
    {
        $imgCaptcha = $request->param('img_captcha');
        $mobile = $request->param('mobile');
        $sessionId = $request->param('session_id');
        if(!$this->captchaLogic->verify($sessionId, $imgCaptcha)) {
            throw new Exception('图形验证码错误');
        }
        $this->smsLogic->sendCaptcha('login', $mobile);
        return $this->success();
    }
}