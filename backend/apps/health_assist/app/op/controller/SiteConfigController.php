<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\core\service\SiteConfigService;
use apps\health_assist\app\Request;

class SiteConfigController extends BaseHealthAssistOpController
{
    /**
     * @var SiteConfigService
     */
    private $siteConfigService;

    protected function init()
    {
        parent::init();
        $this->siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
    }

    public function all()
    {
        $data = $this->siteConfigService->getNonHiddenItems();
        return $this->success($data);
    }

    public function save(Request $request)
    {
        $params = $request->param();
        $data = $this->siteConfigService->batchUpdate($params['keys'], $params['values']);
        return $this->success($data);
    }
}