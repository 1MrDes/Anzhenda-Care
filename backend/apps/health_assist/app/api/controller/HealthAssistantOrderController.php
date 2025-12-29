<?php

namespace apps\health_assist\app\api\controller;

use apps\health_assist\app\Request;
use apps\health_assist\core\model\HealthServiceOrder;
use apps\health_assist\core\service\HealthServiceOrderItemService;
use apps\health_assist\core\service\HealthServiceOrderService;
use apps\health_assist\core\service\HealthServiceService;
use think\Exception;

class HealthAssistantOrderController extends BaseHealthAssistApiController
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

    protected function init()
    {
        parent::init();
        $this->orderService = service('HealthServiceOrder', SERVICE_NAMESPACE);
        $this->orderItemService = service('HealthServiceOrderItem', SERVICE_NAMESPACE);
        $this->goodsService = service('HealthService', SERVICE_NAMESPACE);
    }

    public function unassigned(Request $request)
    {
        $pageSize = $request->param('page_size', 15, 'intval');
        $pageSize = 15;
        $params = [
            'payment_status' => HealthServiceOrder::PAYMENT_STATUS_PAIED,
            'health_assistant_uid' => 0,
            'order_status' => HealthServiceOrder::ORDER_STATUS_WAIT_SHIP
        ];
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

    public function my_orders(Request $request)
    {
        $pageSize = $request->param('page_size', 15, 'intval');
        $pageSize = 15;
        $params = [
            'health_assistant_uid' => $this->user['id']
        ];
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

    public function detail(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order || $order['health_assistant_uid'] != $this->user['id']) {
            throw new Exception('订单不存在');
        }
        $items = $this->orderItemService->getByOrderId($order['id']);
        $quantity = 0;
        foreach ($items as &$item) {
            $quantity += $item['quantity'];
            $item['goods'] = $this->goodsService->getByPk($item['health_service_id']);
        }
        $order['quantity'] = $quantity;
        return $this->success([
            'order' => $order,
            'items' => $items
        ]);
    }

    public function take_order(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(empty($order) || $order['health_assistant_uid'] > 0) {
            throw new Exception('订单不存在');
        }
        $result = $this->orderService->onTakeOrder($orderSn, $this->user['id']);
        return $this->success();
    }

    public function op(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $op = $request->param('op');
        if(!in_array($op, ['acting'])) {
            throw new Exception('非法操作');
        }
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order || $order['health_assistant_uid'] != $this->user['id']) {
            throw new Exception('订单不存在');
        }
        $result = $this->orderService->op($orderSn, $op);
        return $this->success();
    }

    public function ship(Request $request)
    {
        $orderSn = $request->param('order_sn');
        $order = $this->orderService->getByOrderSn($orderSn);
        if(!$order || $order['health_assistant_uid'] != $this->user['id']) {
            throw new Exception('订单不存在');
        }
        $expressSn = $request->param('express_sn', '');
        $expressCode = $request->param('express_code', '');
        $this->orderService->ship($orderSn, $expressSn, $expressCode);
        $order = $this->orderService->getByOrderSn($orderSn);
        return $this->success(['order' => $order]);
    }
}