<?php


namespace apps\health_assist\app\api\controller;

use apps\health_assist\core\service\AdvService;
use apps\health_assist\app\Request;

class AdvController extends BaseHealthAssistApiController
{
    /**
     * @var AdvService
     */
    private $advService;

    protected function init()
    {
        parent::init();
        $this->advService = service('Adv', SERVICE_NAMESPACE);
    }

    public function position(Request $request)
    {
        $position = $request->param('position', '');
        $advs = $this->advService->getByPosition($position);
        return $this->success(['advs' => $advs]);
    }
}