<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/7/29 11:24
 * @description
 */

namespace apps\base\app\api\controller;

use apps\base\core\logic\SmsCaptchaLogic;
use think\Request;

class SmsCaptchaController extends BaseApiController
{
    /**
     * @var SmsCaptchaLogic
     */
    private $smsCaptchaLogic;

    protected function init()
    {
        parent::init();
        $this->smsCaptchaLogic = logic('SmsCaptcha', LOGIC_NAMESPACE);
    }

    public function send(Request $request)
    {
        $type = $request->param('type', '');
        $phone = $request->param('phone', '');
        $result = $this->smsCaptchaLogic->send($phone, $type);
        return $this->success();
    }

    public function check(Request $request)
    {
        $type = $request->param('type', '');
        $phone = $request->param('phone', '');
        $captcha = $request->param('captcha', '');
        $result = $this->smsCaptchaLogic->check($phone, $type, $captcha);
        return $this->success([
            'result' => $result ? 1 : 0
        ]);
    }

    public function verify(Request $request)
    {
        $type = $request->param('type', '');
        $phone = $request->param('phone', '');
        $captcha = $request->param('captcha', '');
        $result = $this->smsCaptchaLogic->verify($phone, $type, $captcha);
        return $this->success([
            'result' => $result ? 1 : 0
        ]);
    }
}