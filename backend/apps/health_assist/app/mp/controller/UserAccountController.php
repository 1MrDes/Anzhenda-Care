<?php


namespace apps\health_assist\app\mp\controller;


use apps\health_assist\core\service\UserAccountBookService;
use apps\health_assist\app\Request;

class UserAccountController extends BaseHealthAssistMpController
{
    /**
     * @var UserAccountBookService
     */
    private $userAccountService;

    protected function init()
    {
        parent::init();
        $this->userAccountService = service('UserAccountBook', SERVICE_NAMESPACE);
    }

    public function summary()
    {
        $user = $this->user;
        return $this->success([
            'account' => arrayOnly($user, ['balance', 'withdraw_balance', 'withdrawing_balance', 'alipay_name', 'alipay_email'])
        ]);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10, 'intval');
        $params = [
            'user_id' => $this->user['id']
        ];
        $res = $this->userAccountService->pageListByParams($params, $pageSize);
        if($res['data']) {
            foreach ($res['data'] as &$rs) {
                $rs = $this->userAccountService->format($rs);
            }
        }
        return $this->success($res);
    }
}