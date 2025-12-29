<?php

namespace apps\base\app\op\controller;

use apps\base\core\service\SiteConfigService;
use think\Request;

class SiteConfigController extends BaseOpController
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