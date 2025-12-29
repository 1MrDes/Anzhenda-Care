<?php
/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-25 12:17
 * Description:
 */

namespace apps\base\core\logic;

use apps\base\core\service\SmsPlatformService;
use apps\base\core\service\SmsTemplateService;
use think\Exception;
use think\Validate;

require_once EXTEND_PATH . 'vm/org/requests/library/Requests.php';
\Requests::register_autoloader();

require_once DOC_PATH . '../../extend/vm/org/alidayu/vendor/autoload.php';

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

class SmsLogic
{
    static $acsClient = null;

    /**
     * @var SmsPlatformService
     */
    private $platformService;

    /**
     * @var SmsTemplateService
     */
    private $templateService;

    public function __construct()
    {
        $this->platformService = service('SmsPlatform', SERVICE_NAMESPACE);
        $this->templateService = service('SmsTemplate', SERVICE_NAMESPACE);
    }

    public function put2Queue(array $sms)
    {

    }

    /**
     * $sms = [
     *    'code' => 'verifyCode',
     *    'phone' => $request->param('phone'),
     *   'platform' => 'smsbao',
     *   'data' => [
     *      'code' => 125425,
     *      'time' => 10
     *    ]
     * ];
     * @param array $sms
     * @return mixed
     * @throws Exception
     */
    public function send(array $sms)
    {
        $platformCode = isset($sms['platform']) ? $sms['platform'] : null;
        $template = $this->templateService->findByCode($sms['code']);
        if (empty($template)) {
            throw new Exception('模板不存在');
        }

        if ($platformCode != null) {
            $platform = $this->platformService->getByCode($platformCode);
            if (empty($platform)) {
                throw new Exception('短信平台不存在');
            }
            $result = $this->$platformCode($template, $platform, $sms);
            return $result;
        } else {
            $relationPlatforms = $this->templateService->getRelationPlatforms($template['id']);
            if (empty($relationPlatforms)) {
                throw new Exception('短信平台不存在');
            }
            $platforms = array();
            foreach ($relationPlatforms as $relationPlatform) {
                $v = $this->platformService->getByPk($relationPlatform['platform_id']);
                if ($v['enable'] == 0) {
                    continue;
                }
                $platforms[$v['weight']] = $v;
            }
            if (empty($platforms)) {
                throw new Exception('短信平台不存在');
            }
            ksort($platforms);
            foreach ($platforms as $platform) {
                $relationPlatform = null;
                foreach ($relationPlatforms as $val) {
                    if ($val['platform_id'] == $platform['id']) {
                        $relationPlatform = $val;
                        break;
                    }
                }
                $platformCode = $platform['sms_code'];
                $result = $this->$platformCode($template, $platform, $relationPlatform, $sms);
                if ($result) {
                    return $result;
                }
            }
        }
    }

    public function smsbao($template, $platform, $relationPlatform, array $sms)
    {
        $config = parse_ini_string($platform['config']);
        $url = 'http://api.smsbao.com/sms?u=' . $config['username'] . '&p=' . md5($config['password']) . '&m=' . $sms['phone'] . '&c=';
        $content = $template['content'];
        if (!empty($sms['data'])) {
            foreach ($sms['data'] as $key => $val) {
                $content = str_replace('{' . $key . '}', $val, $content);
            }
        }
        $url .= urlencode($content);
        $response = \Requests::get($url);
        if (!$response->success) {
            throw new Exception('发送失败');
        }
        if ($response->body != '0') {
            throw new Exception('发送失败');
        }
        return true;
    }

    public function alidayu($template, $platform, $relationPlatform, array $sms)
    {
        $config = parse_ini_string($platform['config']);
        // 加载区域结点配置
        Config::load();

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($sms['phone']);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName($config['signName']);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($relationPlatform['platform_content']);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($sms['data'], JSON_UNESCAPED_UNICODE));


        // 发起访问请求
        $acsResponse = $this->getAcsClient($config)->getAcsResponse($request);
        if (strtoupper($acsResponse->Code) == 'OK') {
            return true;
        } else {
            throw new Exception(json_encode($acsResponse));
            return false;
        }
    }

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public function getAcsClient(array $platform)
    {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = $platform['accessKeyId']; // AccessKeyId

        $accessKeySecret = $platform['accessKeySecret']; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if (static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }
}