<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\app\Request;
use apps\health_assist\core\service\HealthServiceTypeAttributeService;

class HealthServiceTypeAttributeController extends BaseHealthAssistOpController
{
    /**
     * @var HealthServiceTypeAttributeService
     */
    private $goodsTypeAttributeService;

    protected function init()
    {
        parent::init();
        $this->goodsTypeAttributeService = service('HealthServiceTypeAttribute', SERVICE_NAMESPACE);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] == 0) {
            $this->goodsTypeAttributeService->create($data);
        } else {
            $this->goodsTypeAttributeService->updateByPk($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $this->goodsTypeAttributeService->deleteByPk($request->param('id', 0, 'intval'));
        return $this->success();
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $typeId = $request->param('type_id', 0, 'intval');
        $params = [];
        if($typeId > 0) {
            $params['type_id'] = $typeId;
        }
        $res = $this->goodsTypeAttributeService->pageListByParams($params, $pageSize);
        return $this->success($res);
    }

    public function all(Request $request)
    {
        $typeId = $request->param('type_id', 0, 'intval');
        $attrs = $this->goodsTypeAttributeService->findAll($typeId);
        return $this->success(['attrs' => $attrs]);
    }

    public function info(Request $request)
    {
        $data = $this->goodsTypeAttributeService->getByPk($request->param('id', 0, 'intval'));
        return $this->success(['attribute' => $data]);
    }
}