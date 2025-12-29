<?php


namespace apps\health_assist\app\api\controller;

use apps\health_assist\core\service\UserService;
use apps\health_assist\core\model\User;

class MemberController extends BaseHealthAssistApiController
{
    /**
     * @var UserService
     */
    private $userService;

    protected function init()
    {
        parent::init();
        $this->userService = service('User', SERVICE_NAMESPACE);
    }

    public function account()
    {
        $member = $this->userService->getByPk($this->user['id']);
        $member = arrayOnly($member, [
            'balance',
            'withdraw_balance',
            'withdrawing_balance',
            'alipay_name',
            'alipay_email',
            'gold_coin',
            'realname_auth_status'
        ]);
        $member['mobile'] = $this->userService->getMobile($this->user['id']);
        if(substr($member['mobile'], 0, 3) == User::VIRTUAL_MOBILE_PHONE_PREFIX) {
            $member['mobile'] = '';
        }
        return $this->success(['member' => $member]);
    }
}