<?php


namespace apps\health_assist\app\api\controller;

use apps\health_assist\core\logic\crawler\HospitalCrawler;
use apps\health_assist\core\service\SiteConfigService;
use think\Exception;
use vm\com\logic\BaiduYunLogic;
use vm\org\HttpUtil;

class TestController extends BaseHealthAssistApiController
{
    /**
     * @var SiteConfigService
     */
    private $siteConfigService;

    protected function init()
    {
        parent::init();
        if(!isCli()) {
            throw new Exception('hack attempt');
        }
        $this->siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
    }

    public function regions_39()
    {
        $regions = file_get_contents(DOC_PATH . 'data/39regions.json');
        $regions = json_decode($regions, true);
        return $this->success(['regions' => $regions]);

        $province = '广西壮族自治区';
        $city = '南宁市';
        $provinceId = 0;
        $cityId = 0;
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
        return $this->success([
            'city_id' => $cityId,
            'province_id' => $provinceId
        ]);
    }

    public function regions_39_spider()
    {
        $provinceIds = '11,12,13,14,15,21,22,23,31,32,33,34,35,36,37,41,42,43,44,45,46,50,51,52,53,54,61,62,63,64,65,71,81,82';
        $provinceCodes = 'beijing,tianjin,hebei2,shanxi,nmgzzq,liaoning,jilin2,heilongjiang,shanghai,jiangsu,zhejiang,anhui,fujian,jiangxi,shandong,henan,hubei,hunan,guangdong,gxzzzzq,hainan2,chongqing,sichuan,guizhou,yunnan,xzzzq,shanxi2,gansu,qinghai,nxhzzzq,xjwwezzq,taiwan,xgtbxzq,amtbxzq';
        $provinceNames = '北京,天津,河北,山西,内蒙古,辽宁,吉林,黑龙江,上海,江苏,浙江,安徽,福建,江西,山东,河南,湖北,湖南,广东,广西,海南,重庆,四川,贵州,云南,西藏,陕西,甘肃,青海,宁夏,新疆,台湾,香港,澳门';

        $provinceIds = explode(',', $provinceIds);
        $provinceCodes = explode(',', $provinceCodes);
        $provinceNames = explode(',', $provinceNames);

        $regions = [];

        $url = 'https://yyk.39.net/home/areainfo';
        $httpUtil = new HttpUtil();
        for ($k = 0; $k < count($provinceIds); $k++) {
            $postData = [
                'id' => $provinceIds[$k]
            ];
            $re = $httpUtil->curl($url, HttpUtil::REQUEST_METHOD_POST, $postData);
            $regions[] = [
                'id' => $provinceIds[$k],
                'code' => $provinceCodes[$k],
                'name' => $provinceNames[$k],
                'cities' => json_decode($re, true)
            ];
            sleep(2);
        }

        file_put_contents(DOC_PATH . 'data/39regions.json', json_encode($regions, JSON_UNESCAPED_UNICODE));
        return response('finish.');
    }

    public function idcard()
    {
        $idCardNumber = '430481198205165439';
        $name = '肖安球';
        $image = base64_encode(file_get_contents(DOC_PATH . 'id.jpeg'));
        $baiduYunLogic = new BaiduYunLogic();
        $baiduYunLogic->init([
            'app_id' => $this->siteConfigService->getValueByCode('bdclound_appid'),
            'app_key' => $this->siteConfigService->getValueByCode('bdclound_app_key'),
            'secret_key' => $this->siteConfigService->getValueByCode('bdclound_secret_key'),
        ]);
        $result = $baiduYunLogic->faceMingJingVerify($idCardNumber, $name, $image);
        return $this->success($result);
    }
}