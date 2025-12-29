<?php


namespace apps\health_assist\app\op\controller;


use apps\health_assist\core\model\HealthServiceOrder;
use apps\health_assist\core\service\HealthServiceOrderItemService;
use apps\health_assist\core\service\HealthServiceOrderService;
use apps\health_assist\core\service\HealthServiceService;
use apps\health_assist\core\service\UserService;
use think\Exception;
use apps\health_assist\app\Request;

class HealthServiceOrderController extends BaseHealthAssistOpController
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
     * @var UserService
     */
    private $userService;

    protected function init()
    {
        parent::init();
        $this->orderService = service('HealthServiceOrder', SERVICE_NAMESPACE);
        $this->orderItemService = service('HealthServiceOrderItem', SERVICE_NAMESPACE);
        $this->goodsService = service('HealthService', SERVICE_NAMESPACE);
        $this->userService = service('User', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $status = $request->param('status', 'all');
        $params = [];
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

    public function info(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $items = $this->orderItemService->getByOrderId($order['id']);
        $quantity = 0;
        foreach ($items as &$item) {
            $quantity += $item['quantity'];
            $item['health_service'] = $this->goodsService->getByPk($item['health_service_id']);
        }
        $order['quantity'] = $quantity;

        $buyer = $this->userService->getByPk($order['buyer_id']);
        $buyer = arrayOnly($buyer, ['id', 'nick', 'avatar_url']);

        $order['health_assistant'] = null;
        if($order['health_assistant_uid'] > 0) {
            $order['health_assistant'] = $this->userService->getByPk($order['health_assistant_uid']);
            $order['health_assistant'] = arrayOnly($order['health_assistant'], ['id', 'nick', 'avatar_url']);
            $order['health_assistant']['mobile'] = $this->userService->getMobile($order['health_assistant_uid']);
        }

        return $this->success([
            'order' => $order,
            'items' => $items,
            'buyer' => $buyer
        ]);
    }

    public function cancel(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $this->orderService->cancel($orderSn);
        return $this->success();
    }

    public function op(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $op = $request->param('op');
        $act = $request->param('act');
        $this->orderService->op($orderSn, $op, $act);
        $order = $this->orderService->getByOrderSn($orderSn);
        return $this->success(['order' => $order]);
    }

    public function modify_order_money(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $orderMoney = $request->param('order_money', 0.00, 'floatval');
        if($orderMoney < 0) {
            throw new Exception('金额错误');
        }
        $this->orderService->modifyOrderMoney($orderSn, $orderMoney);
        $order = $this->orderService->getByOrderSn($orderSn);
        return $this->success(['order' => $order]);
    }

    public function ship(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $expressSn = $request->param('express_sn', '');
        $expressCode = $request->param('express_code', '');
        $this->orderService->ship($orderSn, $expressSn, $expressCode);
        $order = $this->orderService->getByOrderSn($orderSn);
        return $this->success(['order' => $order]);
    }

    public function pay(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order) {
            throw new Exception('订单不存在');
        }
        $paymentName = $request->param('payment_name', '');
        $transactionId = $request->param('transaction_id', '');
        $payTime = $request->param('pay_time', 0, 'intval');
        $this->orderService->payConfirm($order['id'], $payTime, $paymentName, $transactionId);
        $order = $this->orderService->getByOrderSn($orderSn);
        return $this->success(['order' => $order]);
    }

    public function assign_assistant(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if (!$order) {
            throw new Exception('订单不存在');
        }
        $userId = $request->param('health_assistant_uid', 0, 'intval');
        $this->orderService->onTakeOrder($orderSn, $userId);
        return $this->success();
    }
}