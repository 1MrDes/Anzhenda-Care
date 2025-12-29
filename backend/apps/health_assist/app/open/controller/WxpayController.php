<?php

namespace apps\health_assist\app\open\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\service\PayTradeService;

class WxpayController extends BaseHealthAssistOpenController
{
    /**
     * @return PayTradeService
     */
    private function getPayTradeService()
    {
        return service('PayTrade', SERVICE_NAMESPACE);
    }

    public function notify(Request $request)
    {
        $tradeNo = $request->route('no');
        $tradeResult = $this->getPayTradeService()->query($tradeNo);
        if($tradeResult) {
            return response();
        } else {
            return json([
                'code' => 'FAIL',
                'message' => ''
            ]);
        }
    }
}