<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\core\service\UserService;
use apps\health_assist\app\Request;

class MemberController extends BaseHealthAssistOpController
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

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $params = [];
        $res = $this->userService->pageListByParams($params, $pageSize);
        if($res['data']) {
            foreach ($res['data'] as &$rs) {
                $rs['nick'] = strip_tags($rs['nick']);
                $rs['avatar_url'] = $this->userService->getAvatarUrl($rs['avatar']);
            }
        }
        return $this->success($res);
    }
}