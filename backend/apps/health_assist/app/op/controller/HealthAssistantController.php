<?php

namespace apps\health_assist\app\op\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\HealthAssistantService;
use apps\health_assist\core\service\UserService;

class HealthAssistantController extends BaseHealthAssistOpController
{
    /**
     * @var HealthAssistantService
     */
    private $healthAssistantService;

    /**
     * @var UserService
     */
    private $userService;

    protected function init()
    {
        parent::init();
        $this->healthAssistantService = service('HealthAssistant', SERVICE_NAMESPACE);
        $this->userService = service('User', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $params = [];
        $res = $this->healthAssistantService->pageListByParams($params, $pageSize);
        foreach ($res['data'] as &$rs) {
            $rs = $this->healthAssistantService->format($rs);
            $rs['user'] = $this->userService->getByPk($rs['user_id']);
            $rs['user'] = arrayOnly($rs['user'], ['id', 'nick', 'avatar_url']);
        }
        return $this->success($res);
    }

    public function audit(Request $request)
    {
        $userId = $request->param('user_id', 0, 'intval');
        $status = $request->param('status', 0, 'intval');
        $reason = $request->param('reason', '');
        $this->healthAssistantService->onAudit($userId, $status, $reason);

        $assistant = $this->healthAssistantService->getByUserId($userId);
        $assistant = $this->healthAssistantService->format($assistant);

        $assistant['user'] = $this->userService->getByPk($assistant['user_id']);
        $assistant['user'] = arrayOnly($assistant['user'], ['id', 'nick', 'avatar_url']);

        return $this->success([
            'assistant' => $assistant
        ]);
    }
}