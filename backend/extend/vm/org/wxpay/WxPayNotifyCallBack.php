<?php
/**
 * Created by PhpStorm.
 * User: holobo
 * Date: 2019-03-12
 * Time: 14:54
 */

require_once __DIR__ . '/WxPay.Notify.php';
require_once __DIR__ . '/WxPay.Api.php';
require_once __DIR__ . '/WxPay.Data.php';

class WxPayNotifyCallBack extends WxPayNotify
{
    /**
     * 查询订单
     * @param $config
     * @param $transactionId            支付平台流水号
     * @return 成功时返回，其他抛异常
     * @throws WxPayException
     * @throws \think\Exception
     */
    public function queryOrderByTransactionId($config, $transactionId)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transactionId);
        $result = WxPayApi::orderQuery($config, $input);
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS") {
            return $result;
        }
        throw new \think\Exception('查询失败');
    }

    /**
     * 查询订单
     * @param $config
     * @param $outTradeNo           商户订单号
     * @return 成功时返回，其他抛异常
     * @throws WxPayException
     * @throws \think\Exception
     */
    public function queryOrderByOutTradeNo($config, $outTradeNo)
    {
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($outTradeNo);
        $result = WxPayApi::orderQuery($config, $input);
        if (array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS") {
            return $result;
        }
        throw new \think\Exception('查询失败');
    }

    /**
     *
     * 回包前的回调方法
     * 业务可以继承该方法，打印日志方便定位
     * @param string $xmlData 返回的xml参数
     *
     **/
    public function LogAfterProcess($xmlData)
    {
        \think\facade\Log::info("call back， return xml:" . $xmlData);
        return;
    }

    /**
     * 重写回调处理函数
     * @param WxPayNotifyResults $objData   回调解析出的参数
     * @param WxPayConfigInterface $config
     * @param string $msg
     * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调|成功时返回，其他抛异常
     * @throws \think\Exception
     */
    public function NotifyProcess($objData, $config, &$msg)
    {
        $data = $objData->GetValues();
        // 1、进行参数校验
        if (!array_key_exists("return_code", $data)
            || (array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
            // 失败,不是支付成功的通知
            // 如果有需要可以做失败时候的一些清理处理，并且做一些监控
            $msg = "异常异常";
            throw new \think\Exception('pay_failed');
        }
        if (!array_key_exists("transaction_id", $data)) {
            $msg = "输入参数不正确";
            throw new \think\Exception('transaction_id不存在');
        }

        // 2、进行签名验证
        try {
            $checkResult = $objData->CheckSign($config);
            if ($checkResult == false) {
                //签名错误
                \think\facade\Log::error('签名错误');
                throw new \think\Exception('签名错误');
            }
        } catch (Exception $e) {
            \think\facade\Log::error(json_encode($e));
        }

        // 3、处理业务逻辑
        \think\facade\Log::debug("call back:" . json_encode($data));

        //查询订单，判断订单真实性
//        return $this->queryOrderByTransactionId($config, $data["transaction_id"]);
        return true;
    }
}