<?php


namespace apps\health_assist\app\mp\controller;


use vm\com\logic\CaptchaLogic;

class CaptchaController extends BaseHealthAssistMpController
{
    /**
     * @var CaptchaLogic
     */
    private $captchaLogic;

    protected function init()
    {
        parent::init();
        $this->captchaLogic = logic('Captcha', '\vm\com\logic\\');
    }

    public function entry()
    {
        return $this->success($this->captchaLogic->entry());
    }
}