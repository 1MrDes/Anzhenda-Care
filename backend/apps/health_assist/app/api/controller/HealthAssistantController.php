<?php

namespace apps\health_assist\app\api\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\model\HealthAssistant;
use apps\health_assist\core\service\HealthAssistantService;

class HealthAssistantController extends BaseHealthAssistApiController
{
    /**
     * @var HealthAssistantService
     */
    private $healthAssistantService;

    protected function init()
    {
        parent::init();
        $this->healthAssistantService = service('HealthAssistant', SERVICE_NAMESPACE);
    }

    public function my()
    {
        $assistant = $this->healthAssistantService->getByUserId($this->user['id']);
        return $this->success(['assistant' => $assistant]);
    }

    public function apply(Request $request)
    {
        $data = $request->param();
        $data = arrayOnly($data, ['province_id', 'city_id']);
        $data['user_id'] = $this->user['id'];
        $data['status'] = HealthAssistant::STATUS_WAIT_AUDIT;
        $data['apply_time'] = time();
        $this->healthAssistantService->insert($data, true);
        return $this->success();
    }
}