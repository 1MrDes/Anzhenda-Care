<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\core\service\UserService;
use apps\health_assist\core\service\UserWithdrawCashService;
use apps\health_assist\app\Request;

class WithdrawCashController extends BaseHealthAssistOpController
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
        $params = [];
        $res = $this->userWithdrawCashService->pageListByParams($params, $pageSize);
        if($res['data']) {
            foreach ($res['data'] as &$rs) {
                $rs = $this->userWithdrawCashService->format($rs);
                $rs['user'] = $this->userService->getByPk($rs['user_id']);
                $rs['user'] = arrayOnly($rs['user'], [
                    'id',
                    'nick',
                    'avatar_url'
                ]);
            }
        }
        return $this->success($res);
    }

    public function reject(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $remark = $request->param('remark', '');
        $this->userWithdrawCashService->onReject($id, $remark);
        $item = $this->userWithdrawCashService->getByPk($id);
        $item = $this->userWithdrawCashService->format($item);
        $item['user'] = $this->userService->getByPk($item['user_id']);
        $item['user'] = arrayOnly($item['user'], [
            'id',
            'nick',
            'avatar_url'
        ]);
        return $this->success($item);
    }

    public function agree(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $this->userWithdrawCashService->onAgree($id);
        $item = $this->userWithdrawCashService->getByPk($id);
        $item = $this->userWithdrawCashService->format($item);
        $item['user'] = $this->userService->getByPk($item['user_id']);
        $item['user'] = arrayOnly($item['user'], [
            'id',
            'nick',
            'avatar_url'
        ]);
        return $this->success($item);
    }

    public function pay(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $payType = $request->param('pay_type', 0, 'intval');
        $this->userWithdrawCashService->onPay($id, $payType);
        $item = $this->userWithdrawCashService->getByPk($id);
        $item = $this->userWithdrawCashService->format($item);
        $item['user'] = $this->userService->getByPk($item['user_id']);
        $item['user'] = arrayOnly($item['user'], [
            'id',
            'nick',
            'avatar_url'
        ]);
        return $this->success($item);
    }
}