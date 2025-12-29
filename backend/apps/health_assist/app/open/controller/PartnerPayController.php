<?php

namespace apps\health_assist\app\open\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\PayTradeService;

class PartnerPayController extends BaseHealthAssistOpenController
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

    public function notify(Request $request)
    {
        $tradeNo = $request->param('out_trade_no', '');
        $tradeResult = $this->payTradeService->query($tradeNo);
        return $this->success($tradeResult ? 'success' : 'fail');
    }

    public function return(Request $request)
    {
        $tradeNo = $request->param('out_trade_no', '');
        $tradeResult = $this->payTradeService->query($tradeNo);
        return $this->success($tradeResult ? 'success' : 'fail');
    }
}