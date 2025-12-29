<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\app\Request;
use apps\health_assist\core\service\HealthServiceTypeService;

class HealthServiceTypeController extends BaseHealthAssistOpController
{
    /**
     * @var HealthServiceTypeService
     */
    private $goodsTypeService;

    protected function init()
    {
        parent::init();
        $this->goodsTypeService = service('HealthServiceType', SERVICE_NAMESPACE);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        $data['store_id'] = 0;
        if($data['id'] == 0) {
            $data['attr_group'] = '';
            $this->goodsTypeService->create($data);
        } else {
            $this->goodsTypeService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $this->goodsTypeService->deleteByPk($request->param('id', 0, 'intval'));
        return $this->success();
    }

    public function lists()
    {
        $data = $this->goodsTypeService->getAll();
        return $this->success(['types' => $data]);
    }

    public function info(Request $request)
    {
        $data = $this->goodsTypeService->getByPk($request->param('id', 0, 'intval'));
        return $this->success(['type' => $data]);
    }
}