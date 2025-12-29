<?php


namespace apps\health_assist\app\op\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\HealthServiceTypeSpecItemService;
use apps\health_assist\core\service\HealthServiceTypeSpecService;

class HealthServiceTypeSpecController extends BaseHealthAssistOpController
{
    /**
     * @var HealthServiceTypeSpecService
     */
    private $goodsTypeSpecService;

    /**
     * @var HealthServiceTypeSpecItemService
     */
    private $goodsTypeSpecItemService;

    protected function init()
    {
        parent::init();
        $this->goodsTypeSpecService = service('HealthServiceTypeSpec', SERVICE_NAMESPACE);
        $this->goodsTypeSpecItemService = service('HealthServiceTypeSpecItem', SERVICE_NAMESPACE);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->goodsTypeSpecService->create($data);
        } else {
            $this->goodsTypeSpecService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $this->goodsTypeSpecService->deleteByPk($request->param('id', 0, 'intval'));
        return $this->success();
    }

    public function all(Request $request)
    {
        $typeId = $request->param('type_id', 0, 'intval');
        $specs = $this->goodsTypeSpecService->findAll($typeId);
        $specsItems = [];
        foreach ($specs as $spec) {
            $specsItems[] = $this->goodsTypeSpecItemService->getAll(['spec_id' => $spec['id']]);
        }
        return $this->success(['specs' => $specs, 'specItems' => $specsItems]);
    }

    public function info(Request $request)
    {
        $data = $this->goodsTypeSpecService->getByPk($request->param('id', 0, 'intval'));
        return $this->success(['spec' => $data]);
    }
}