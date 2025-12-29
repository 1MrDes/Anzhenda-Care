<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\core\service\AdvPositionService;
use apps\health_assist\app\Request;

class AdvPositionController extends BaseHealthAssistOpController
{
    /**
     * @var AdvPositionService
     */
    private $advPositionService;

    protected function init()
    {
        parent::init();
        $this->advPositionService = service('AdvPosition', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $res = $this->advPositionService->pageList($pageSize);
        return $this->success($res);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->advPositionService->create($data);
        } else {
            $this->advPositionService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $this->advPositionService->deleteByPk($id);
        return $this->success();
    }

    public function all()
    {
        $positionList = $this->advPositionService->getAll();
        return $this->success(['positions' => $positionList]);
    }
}