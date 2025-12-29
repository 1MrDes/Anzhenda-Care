<?php
/**
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2018/4/10 09:35
 * @description
 */

namespace apps\base\app\api\controller;

use think\Exception;
use think\Request;
use vm\org\queue\QueueFactory;

class SmsController extends BaseApiController
{

    protected function init()
    {
        parent::init();
    }

    public function send(Request $request)
    {
        $code = $request->param('code', '');
        $phone = $request->param('phone', '');
        $platform = $request->param('platform', '');
        $data = $request->param('data', '');
        if(!check_mobile($phone)) {
            throw new Exception('手机号错误');
        }

        $sms = [
            'code' => $code,
            'phone' => $phone,
            'platform' => $platform,
            'data' => json_decode($data, true),
        ];

        $config = config('queue');
        $queueHandler = QueueFactory::instance();
        $queueHandler->set($config['queues']['sms'], $sms);
        return $this->success();
    }
}