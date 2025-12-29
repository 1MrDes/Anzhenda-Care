<?php


namespace apps\health_assist\app\api\controller;

use apps\health_assist\core\model\PayTrade;
use apps\health_assist\core\service\PayTradeService;
use apps\health_assist\app\Request;

class PayTradeController extends BaseHealthAssistApiController
{

    /**
     * @var PayTradeService
     */
    private $payTradeService;

    protected function init()
    {
        parent::init();
        $this->payTradeService = service('PayTrade', SERVICE_NAMESPACE);
    }

    public function create(Request $request)
    {
        $orderType = $request->param('order_type', '');
        $orderSn = $request->param('order_sn', '');
        $mpPlatform = $request->param('__mpplatform', '');
        $appid = $request->param('__appid', '');
        $payData = $this->payTradeService->createPayTrade(
            $orderType,
            $orderSn,
            $mpPlatform,
            $appid
        );
        return $this->success($payData);
    }

    public function query(Request $request)
    {
        $tradeNo = $request->param('pay_trade_no');
        $tradeResult = $this->payTradeService->query($tradeNo);
        return $this->success([
            'success' => $tradeResult ? 1 : 0
        ]);
    }
}