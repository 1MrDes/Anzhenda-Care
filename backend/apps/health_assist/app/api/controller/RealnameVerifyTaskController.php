<?php

namespace apps\health_assist\app\api\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\model\PayTrade;
use apps\health_assist\core\service\PayTradeService;
use apps\health_assist\core\service\RealnameVerifyTaskService;

class RealnameVerifyTaskController extends BaseHealthAssistApiController
{
    /**
     * @var RealnameVerifyTaskService
     */
    private $realnameVerifyTaskService;

    /**
     * @var PayTradeService
     */
    private $payTradeService;

    protected function init()
    {
        parent::init();
        $this->realnameVerifyTaskService = service('RealnameVerifyTask', SERVICE_NAMESPACE);
        $this->payTradeService = service('PayTrade', SERVICE_NAMESPACE);
    }

    public function create(Request $request)
    {
        $data = $request->param();
        $data = arrayOnly($data, ['name', 'cardno', 'file_id']);
        $data['buyer_id'] = $this->user['id'];
        $result = $this->realnameVerifyTaskService->create($data);

        // 生成支付参数
        $payData = $this->payTradeService->createPayTrade(
            PayTrade::ORDER_TYPE_REALNAME_VERIFY_TASK,
            $result['order_sn'],
            $request->param('__mpplatform', '')
        );

        return $this->success([
            'order_sn' => $result['order_sn'],
            'pay_params' => $payData['pay_params'],
            'pay_trade_no' => $payData['pay_trade_no'],
        ]);
    }

    public function query(Request $request)
    {
        $sn = $request->param('sn', '');
        $result = $this->realnameVerifyTaskService->onVerify($sn);
        return $this->success([
            'verify_result' => $result
        ]);
    }
}