<?php

namespace apps\health_assist\app\api\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\HealthHospitalLabsService;
use apps\health_assist\core\service\HealthHospitalService;
use think\Exception;

class HealthHospitalController extends BaseHealthAssistApiController
{
    /**
     * @var HealthHospitalService
     */
    private $healthHospitalService;

    /**
     * @var HealthHospitalLabsService
     */
    private $healthHospitalLabsService;

    protected function init()
    {
        parent::init();
        $this->healthHospitalService = service('HealthHospital', SERVICE_NAMESPACE);
        $this->healthHospitalLabsService = service('HealthHospitalLabs', SERVICE_NAMESPACE);
    }

    public function regions()
    {
        $regions = file_get_contents(DOC_PATH . 'data/39regions.json');
        $regions = json_decode($regions, true);
        return $this->success([
            'regions' => $regions
        ]);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 15, 'intval');
        $pageSize = 15;

        $keywords = $request->param('keywords', '');
        $province = $request->param('province', '');
        $city = $request->param('city', '');
        $cityId = $request->param('city_id', 0, 'intval');
        $provinceId = 0;
        if(!empty($province) && !empty($city) && $cityId == 0) {
            $regions = file_get_contents(DOC_PATH . 'data/39regions.json');
            $regions = json_decode($regions, true);
            foreach ($regions as $region) {
                if(preg_match('/^'.$region['name'].'/', $province)) {
                    foreach ($region['cities'] as $item) {
                        if(preg_match('/^'.$item['name'].'/', $city)) {
                            $cityId = $item['id'];
                            break;
                        }
                    }
                    $provinceId = $region['id'];
                    break;
                }
            }
        }

        $params = [];
        if($cityId > 0) {
            $params['city_id'] = $cityId;
        }
        if($keywords) {
            $params['keywords'] = $keywords;
        }
        $sortOrder = [
            'level' => 'DESC',
            'id' => 'DESC'
        ];
        $res = $this->healthHospitalService->search($params, $pageSize, $sortOrder);
        if($res['data']) {
            foreach ($res['data'] as &$rs) {
                $rs = $this->healthHospitalService->format($rs);
            }
        }
        return $this->success($res);
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $hospital = $this->healthHospitalService->getByPk($id);
        if(empty($hospital)) {
            throw new Exception('医院不存在');
        }
        $hospital = $this->healthHospitalService->format($hospital);
        return $this->success([
            'hospital' => $hospital
        ]);
    }

    public function labs(Request $request)
    {
        $hospitalId = $request->param('hospital_id', 0, 'intval');
        $labs = $this->healthHospitalLabsService->getByHospitalId($hospitalId);
        return $this->success([
            'labs' => $labs
        ]);
    }
}