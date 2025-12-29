<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\core\service\AdvPositionService;
use apps\health_assist\core\service\AdvService;
use apps\health_assist\app\Request;

class AdvController extends BaseHealthAssistOpController
{
    /**
     * @var AdvService
     */
    private $advService;

    /**
     * @var AdvPositionService
     */
    private $advPositionService;

    protected function init()
    {
        parent::init();
        $this->advService = service('Adv', SERVICE_NAMESPACE);
        $this->advPositionService = service('AdvPosition', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $positionId = $request->param('position_id', 0, 'intval');
        $params = [];
        if($positionId > 0) {
            $params['position_id'] = $positionId;
        }
        $res = $this->advService->pageListByParams($params, $pageSize);
        foreach ($res['data'] as &$rs) {
            $rs['position'] = $this->advPositionService->getByPk($rs['position_id']);
        }
        return $this->success($res);
    }

    public function delete(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $this->advService->deleteByPk($id);
        return $this->success();
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->advService->create($data);
        } else {
            $this->advService->updateByPk($data);
        }
        return $this->success();
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $adv = $this->advService->getByPk($id);
        return $this->success(['adv' => $adv]);
    }
}