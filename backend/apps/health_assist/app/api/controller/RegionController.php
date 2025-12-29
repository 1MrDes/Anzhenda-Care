<?php

namespace apps\health_assist\app\api\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\SiteConfigService;
use vm\com\logic\QMapLogic;
use vm\com\logic\RegionLogic;

class RegionController extends BaseHealthAssistApiController
{
    /**
     * @var RegionLogic
     */
    private $regionLogic;

    protected function init()
    {
        parent::init();
        $this->regionLogic = logic('Region', '\vm\com\logic\\');
        $this->regionLogic->init([
            'rpc_server' => env('rpc_base.host') . '/region',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
    }

    public function tree()
    {
        return $this->success(['regions' => $this->regionLogic->tree()]);
    }

    public function all()
    {
        return $this->success(['regions' => $this->regionLogic->all()]);
    }

    public function geodecoder(Request $request)
    {
        $lat = $request->param('lat', '');
        $lng = $request->param('lng', '');

        /**@var SiteConfigService $siteConfigService */
        $siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
        /**@var QMapLogic $qmapLogic */
        $qmapLogic = logic('QMap', '\vm\com\logic\\');
        $qmapLogic->init([
            'key' => $siteConfigService->getValueByCode('qqmap_key')
        ]);
        $region = $qmapLogic->geodecoder($lng, $lat);
        return $this->success([
            'province' => $region['address_component']['province'],
            'city' => $region['address_component']['city'],
            'district' => $region['address_component']['district'],
            'street' => $region['address_component']['street'],
            'street_number' => $region['address_component']['street_number'],
        ]);
    }
}