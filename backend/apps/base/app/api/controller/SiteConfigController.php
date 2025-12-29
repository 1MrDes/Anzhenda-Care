<?php

namespace apps\base\app\api\controller;

use apps\base\core\service\SiteConfigService;

class SiteConfigController extends BaseApiController
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
        $data = $this->siteConfigService->getAllConfigs();
        return $this->success([
            'configs' => $data
        ]);
    }
}