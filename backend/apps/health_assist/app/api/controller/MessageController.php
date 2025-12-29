<?php

namespace apps\health_assist\app\api\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\model\UserSystemNotice;
use apps\health_assist\core\service\SystemNoticeService;
use apps\health_assist\core\service\UserSystemNoticeService;

class MessageController extends BaseHealthAssistApiController
{

    /**
     * @var UserSystemNoticeService
     */
    private $userSystemNoticeService;

    /**
     * @var SystemNoticeService
     */
    private $systemNoticeService;

    protected function init()
    {
        parent::init();
        $this->userSystemNoticeService = service('UserSystemNotice', SERVICE_NAMESPACE);
        $this->systemNoticeService = service('SystemNotice', SERVICE_NAMESPACE);
    }

    public function unread()
    {
        $count = $this->userSystemNoticeService->count([
            'recipient_id' => $this->user['id'],
            'status' => UserSystemNotice::STATUS_WAIT_READ
        ]);
        return $this->success(['count' => $count]);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 15, 'intval');
        $pageSize = 15;
        $params = [
            'recipient_id' => $this->user['id']
        ];
        $res = $this->userSystemNoticeService->pageListByParams($params, $pageSize);
        $dataList = [];
        foreach ($res['data'] as $rs) {
            $item = $this->systemNoticeService->getByPk($rs['system_notice_id']);
            $item['url'] = json_decode($item['url'], true);
            $dataList[] = $item;
            $this->userSystemNoticeService->updateByPk([
                'id' => $rs['id'],
                'status' => UserSystemNotice::STATUS_READ
            ]);
        }
        $res['data'] = $dataList;
        return $this->success($res);
    }
}