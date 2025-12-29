<?php


namespace apps\health_assist\app\api\controller;


use apps\health_assist\core\service\SiteConfigService;

class IndexController extends BaseHealthAssistApiController
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

    public function site_config()
    {
        $serviceBuyPromotion = <<<EOF
        <p>1、下单后我们会电话与您联系，告知您规划的就诊流程，提醒就诊注意事项，如携带就诊所需证件和资料、就诊时间、取号步骤等信息；</p>
        <p>2、因各医院、科室及项目医疗流程的不同，建议您联系客服确认后再下单；</p>
        <p>3、如遇医生临时停诊等特殊情况，我们会电话与您确认，若有其他情况请联系客服；</p>
        <p>4、就医本身产生的挂号费、药费、检查费、手术费等所有费用由用户自理；</p>
        <p>5、就诊过程中需要就诊人本人或亲属签字的环节(如麻醉、手术、穿刺检查等)我们无法代办(或需要您的特殊授权)；</p>
        <p>6、接送服务仅限就诊城市城区范围内，如您是从其他城市或从农村到城区前来就医，下单前请联系客服了解接送详情；</p>
        <p>7、服务完成后我们可能会对您进行电话回访，询问了解服务情况、用户建议以及客户满意度等。</p>
EOF;

$serviceOrderCancelPromotion = <<<EOF
        <p>1、在订单详情页面点击取消按钮可取消订单；</p>
        <p>2、每天18点前可取消第二天以后的订单，超过18点则不能取消第二天的订单；</p>
        <p>3、取消订单将给我们增加成本，请谨慎取消订单。</p>
EOF;

        $siteConfig = [];
        $keys = [
            'app_name',
            'service_email',
            'customer_service_tel',
            'customer_service_wxid',
            'mp_site',
            'privacy_policy_url',
            'terms_conditions_url',
            'statics_url',
            'feedback_url',
            'health_service_buy_protocol_url',
            'see_doctor_assistant_policy',
            'city_partner_policy',
            'service_prices',
            'health_assistant_salary_ratio'
        ];
        foreach ($keys as $key) {
            $siteConfig[$key] = $this->siteConfigService->getValueByCode($key);
        }

        $weapp = config('weapp');
        $siteConfig['msg_template'] = $weapp['msg_template'];

        $siteConfig['service_buy_promotion'] = $serviceBuyPromotion;
        $siteConfig['service_order_cancel_promotion'] = $serviceOrderCancelPromotion;


        return $this->success(['site_config' => $siteConfig]);
    }

    public function express_company()
    {
        $sites = config('site');
        return $this->success(['express_company' => $sites['express_company']]);
    }
}