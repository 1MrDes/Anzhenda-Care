<?php


namespace apps\health_assist\app\api\controller;


use apps\health_assist\core\service\UserService;
use apps\health_assist\core\service\UserWithdrawCashService;
use think\Exception;
use apps\health_assist\app\Request;

class UserWithdrawCashController extends BaseHealthAssistApiController
{
    /**
     * @var UserWithdrawCashService
     */
    private $userWithdrawCashService;

    /**
     * @var UserService
     */
    private $userService;

    protected function init()
    {
        parent::init();
        $this->userWithdrawCashService = service('UserWithdrawCash', SERVICE_NAMESPACE);
        $this->userService = service('User', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10, 'intval');
        $params = [
            'user_id' => $this->user['id']
        ];
        $res = $this->userWithdrawCashService->pageListByParams($params, $pageSize);
        if($res['data']) {
            foreach ($res['data'] as &$rs) {
                $rs = $this->userWithdrawCashService->format($rs);
            }
        }
        return $this->success($res);
    }

    public function apply(Request $request)
    {
//        $member = $this->userService->getByPk($this->user['id']);
//        $member = arrayOnly($member, ['balance', 'withdraw_balance', 'withdrawing_balance', 'alipay_name', 'alipay_email', 'contribution']);
//        if(empty($member['alipay_name']) || empty($member['alipay_email'])) {
//            throw new Exception('请设置提现账号');
//        }
        $money = $request->param('amount', 0.00, 'floatval');
        $alipayName = $request->param('alipay_name', '');
        $alipayEmail = $request->param('alipay_email', '');
        $this->userWithdrawCashService->onApply($this->user['id'], $money, $alipayName, $alipayEmail);
        $user = $this->userService->getByPk($this->user['id']);
        return $this->success([
            'user' => arrayOnly($user, ['balance', 'withdraw_balance', 'withdrawing_balance'])
        ]);
    }

    public function save_account(Request $request)
    {
        $alipayName = $request->param('alipay_name');
        $alipayEmail = $request->param('alipay_email');
        $this->userService->updateByPk([
            'id' => $this->user['id'],
            'alipay_name' => $alipayName,
            'alipay_email' => $alipayEmail
        ]);
        return $this->success();
    }
}