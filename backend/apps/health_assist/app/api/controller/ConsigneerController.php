<?php


namespace apps\health_assist\app\api\controller;


use apps\health_assist\core\service\ConsigneerService;
use apps\health_assist\app\Request;
use think\Exception;

class ConsigneerController extends BaseHealthAssistApiController
{
    /**
     * @var ConsigneerService
     */
    private $consigneerService;

    protected function init()
    {
        parent::init();
        $this->consigneerService = service('Consigneer', SERVICE_NAMESPACE);
    }

    public function first()
    {
        $consigneer = $this->consigneerService->getDefaultByUserId($this->user['id']);
        $consigneer = $this->consigneerService->format($consigneer);
        return $this->success([
            'consigneer' => $consigneer
        ]);
    }

    public function all()
    {
        $consigneers = $this->consigneerService->getByUserId($this->user['id']);
        foreach ($consigneers as &$consigneer) {
            $consigneer = $this->consigneerService->format($consigneer);
        }
        return $this->success(['consigneers' => $consigneers]);
    }

    public function save(Request $request)
    {
        $data = $request->param();
        if($data['id'] > 0) {
            $consigneer = $this->consigneerService->getByPk($data['id']);
            if($consigneer['user_id'] != $this->user['id']) {
                throw new Exception('无权修改');
            }
            $this->consigneerService->updateByPk($data);
        } else {
            $data['user_id'] = $this->user['id'];
            $this->consigneerService->create($data);
        }
        return $this->success();
    }

    public function delete(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $this->consigneerService->deleteByPk($id);
        return $this->success();
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $consigneer = $this->consigneerService->getByPk($id);
        if($consigneer['user_id'] != $this->user['id']) {
            throw new Exception('无权修改');
        }
        $consigneer = $this->consigneerService->format($consigneer);
        return $this->success([
            'consigneer' => $consigneer
        ]);
    }
}