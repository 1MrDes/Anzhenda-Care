<?php


namespace apps\health_assist\app\api\controller;


use apps\health_assist\core\model\HealthServiceOrder;
use apps\health_assist\core\model\PayTrade;
use apps\health_assist\core\service\HealthServiceOrderItemService;
use apps\health_assist\core\service\HealthServiceOrderService;
use apps\health_assist\core\service\HealthServiceService;
use apps\health_assist\core\service\PayTradeService;
use apps\health_assist\core\service\UserService;
use think\Exception;
use apps\health_assist\app\Request;

class HealthServiceOrderController extends BaseHealthAssistApiController
{
    /**
     * @var HealthServiceOrderService
     */
    private $orderService;

    /**
     * @var HealthServiceOrderItemService
     */
    private $orderItemService;

    /**
     * @var HealthServiceService
     */
    private $goodsService;

    /**
     * @var PayTradeService
     */
    private $payTradeService;

    /**
     * @var UserService
     */
    private $userService;

    protected function init()
    {
        parent::init();
        $this->orderService = service('HealthServiceOrder', SERVICE_NAMESPACE);
        $this->orderItemService = service('HealthServiceOrderItem', SERVICE_NAMESPACE);
        $this->goodsService = service('HealthService', SERVICE_NAMESPACE);
        $this->payTradeService = service('PayTrade', SERVICE_NAMESPACE);
        $this->userService = service('User', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 15, 'intval');
        $pageSize = 15;
        $status = $request->param('status', 'all');
        $params = [
            'buyer_id' => $this->user['id']
        ];
        if($status == 'unpaied') {
            $params['order_status'] = HealthServiceOrder::ORDER_STATUS_CONFIRMED;
            $params['payment_status'] = HealthServiceOrder::PAYMENT_STATUS_UNPAIED;
        } else if($status == 'paied') {
            $params['order_status'] = HealthServiceOrder::ORDER_STATUS_WAIT_SHIP;
        } else if($status == 'shipped') {
            $params['order_status'] = HealthServiceOrder::ORDER_STATUS_SHIPPED;
        } else if($status == 'finished') {
            $params['order_status'] = HealthServiceOrder::ORDER_STATUS_FINISHED;
        } else if($status == 'refunded') {
            $params['order_status'] = HealthServiceOrder::ORDER_STATUS_REFUNDED;
        }
        $res = $this->orderService->pageListByParams($params, $pageSize);
        if($res['data']) {
            foreach ($res['data'] as &$rs) {
                $rs = $this->orderService->format($rs);
                if($request['__mpplatform'] == 'mp-alipay') {
                    $rs['create_time_str'] = date('Y-m-d H:i:s', $rs['create_time']);
                    $rs['pay_time_str'] = date('Y-m-d H:i:s', $rs['pay_time']);
                    $rs['clinic_time_str'] = date('Y-m-d H:i:s', $rs['clinic_time']);
                    $rs['shipping_time_str'] = date('Y-m-d H:i:s', $rs['shipping_time']);
                }
                $rs['items'] = $this->orderItemService->getByOrderId($rs['id']);
                $quantity = 0;
                foreach ($rs['items'] as $item) {
                    $quantity += $item['quantity'];
                }
                $rs['quantity'] = $quantity;
            }
        }
        return $this->success($res);
    }

    public function detail(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order || $order['buyer_id'] != $this->user['id']) {
            throw new Exception('订单不存在');
        }
        if($request['__mpplatform'] == 'mp-alipay') {
            $order['create_time_str'] = date('Y-m-d H:i:s', $order['create_time']);
            $order['pay_time_str'] = date('Y-m-d H:i:s', $order['pay_time']);
            $order['clinic_time_str'] = date('Y-m-d H:i:s', $order['clinic_time']);
            $order['shipping_time_str'] = date('Y-m-d H:i:s', $order['shipping_time']);
        }

        $items = $this->orderItemService->getByOrderId($order['id']);
        $quantity = 0;
        foreach ($items as &$item) {
            $quantity += $item['quantity'];
            $item['goods'] = $this->goodsService->getByPk($item['health_service_id']);
        }
        $order['quantity'] = $quantity;

        $order['health_assistant'] = null;
        if($order['health_assistant_uid'] > 0) {
            $order['health_assistant'] = $this->userService->getByPk($order['health_assistant_uid']);
            $order['health_assistant'] = arrayOnly($order['health_assistant'], ['id', 'nick', 'avatar_url']);
            $order['health_assistant']['mobile'] = $this->userService->getMobile($order['health_assistant_uid']);
        }

        return $this->success([
            'order' => $order,
            'items' => $items
        ]);
    }

    public function cancel(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order || $order['buyer_id'] != $this->user['id']) {
            throw new Exception('订单不存在');
        }
        $this->orderService->cancel($orderSn);
        return $this->success();
    }

    public function receive_goods(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order || $order['buyer_id'] != $this->user['id']) {
            throw new Exception('订单不存在');
        }
        $this->orderService->receiveGoods($orderSn);
        return $this->success();
    }

    public function create(Request $request)
    {
        $data = $request->param();
        $fromuid = $request->param('fromuid', 0, 'intval');
        if($fromuid == 0) {
            $fromuid = $this->user['fromuid'];
        }
        $order = [
            'buyer_id' => $this->user['id'],
            'hospital_id' => intval($data['hospital_id']),
            'hospital_name' => $data['hospital_name'],
            'hospital_lab' => $data['hospital_lab'],
            'clinic_time' => intval($data['clinic_time']),
            'contact_name' => $data['contact_name'],
            'contact_tel' => $data['contact_tel'],
            'remark' => $data['remark'],
            'fromuid' => $fromuid,
            'consigneer_id' => $data['consigneer_id']
        ];
        $orderItems = json_decode($data['order_items'], true);
        $orderSn = $this->orderService->createOrder($order, $orderItems);
        // 生成支付参数
        $payData = $this->payTradeService->createPayTrade(
            PayTrade::ORDER_TYPE_HEALTH_SERVICE_ORDER,
            $orderSn,
            $data['__mpplatform']
        );

        return $this->success([
            'order_sn' => $orderSn,
            'pay_params' => $payData['pay_params'],
            'pay_trade_no' => $payData['pay_trade_no'],
        ]);
    }

    public function op(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order || $order['buyer_id'] != $this->user['id']) {
            throw new Exception('订单不存在');
        }
        $op = $request->param('op');
        $act = $request->param('act');
        $this->orderService->op($orderSn, $op, $act);
        $order = $this->orderService->getByOrderSn($orderSn);
        return $this->success(['order' => $order]);
    }
}