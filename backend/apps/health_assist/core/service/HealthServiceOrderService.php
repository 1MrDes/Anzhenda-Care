<?php


namespace apps\health_assist\core\service;

use apps\health_assist\core\model\HealthAssistant;
use apps\health_assist\core\model\HealthServiceOrder;
use apps\health_assist\core\model\SystemNotice;
use apps\health_assist\core\model\UserAccountBook;
use apps\health_assist\core\model\UserSystemNotice;
use apps\health_assist\core\payment\IPayOrder;
use think\Exception;
use think\facade\Log;
use think\facade\Validate;
use vm\com\BaseService;
use vm\org\lock\DisLockFactory;

class HealthServiceOrderService extends BaseService implements IPayOrder
{
    /**
     * @var HealthServiceOrderItemService
     */
    private $orderItemService;

    /**
     * @var HealthServiceService
     */
    private $healthServiceService;

    /**
     * @var HealthServiceSpecSkuService
     */
    private $healthServiceSpecSkuService;

    /**
     * @var HealthServiceSpecService
     */
    private $healthServiceSpecService;

    /**
     * @var HealthServiceSpecItemService
     */
    private $healthServiceSpecItemService;

    /**
     * @var PayTradeService
     */
    private $payTradeService;

    /**
     * @var HealthHospitalService
     */
    private $healthHospitalService;

    /**
     * @var HealthAssistantService
     */
    private $healthAssistantService;

    protected function init()
    {
        parent::init();
        $this->orderItemService = service('HealthServiceOrderItem', SERVICE_NAMESPACE);
        $this->healthServiceService = service('HealthService', SERVICE_NAMESPACE);
        $this->healthServiceSpecSkuService = service('HealthServiceSpecSku', SERVICE_NAMESPACE);
        $this->healthServiceSpecService = service('HealthServiceSpec', SERVICE_NAMESPACE);
        $this->healthServiceSpecItemService = service('HealthServiceSpecItem', SERVICE_NAMESPACE);
        $this->payTradeService = service('PayTrade', SERVICE_NAMESPACE);
        $this->healthHospitalService = service('HealthHospital', SERVICE_NAMESPACE);
        $this->healthAssistantService = service('HealthAssistant', SERVICE_NAMESPACE);
    }

    /**
     * @return ConsigneerService
     */
    private function getConsigneerService()
    {
        return service('Consigneer', SERVICE_NAMESPACE);
    }

    /**
     * @return SystemNoticeService
     */
    private function getSystemNoticeService()
    {
        return service('SystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return UserSystemNoticeService
     */
    private function getUserSystemNoticeService()
    {
        return service('UserSystemNotice', SERVICE_NAMESPACE);
    }

    /**
     * @return SiteConfigService
     */
    private function getSiteConfigService()
    {
        return service('SiteConfig', SERVICE_NAMESPACE);
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return service('User', SERVICE_NAMESPACE);
    }

    /**
     * @inheritDoc
     * @return HealthServiceOrder
     */
    protected function getModel()
    {
        return new HealthServiceOrder();
    }

    private function genSn()
    {
        while (true) {
            $sn = date('ymdHis') . rand_string(8, 1);
            if (!$this->getModel()->info(['order_sn' => $sn])) {
                return $sn;
            }
        }
    }

    public function createOrder(array $order, array $orderItems)
    {
        $validate   = Validate::rule([
            'buyer_id' => 'number|>:0',
            'hospital_name' => 'require',
            'contact_name' => 'require',
            'contact_tel' => 'require',
        ], [
            'buyer_id' => '买家ID必填',
            'hospital_name' => '医院名称必填',
            'contact_name' => '联系人必填',
            'contact_tel' => '联系电话必填',
        ]);
        if(!$validate->check($order)) {
            throw new Exception($validate->getError());
        }

        $lockKey = 'lock_submit_order';
        $lock = DisLockFactory::instance();
        $lock->lock($lockKey, 5000);
        try {
            $orderMoney = 0.00;
            $originalOrderMoney = 0.00;

            $items = [];
            foreach ($orderItems as $orderItem) {
                $specSku = null;
                if(!empty($orderItem['spec_sku_key'])) {
                    $specSku = $this->healthServiceSpecSkuService->info([
                        'health_service_id' => $orderItem['health_service_id'],
                        'key' => $orderItem['spec_sku_key']
                    ]);
                    if(empty($specSku)) {
                        throw new Exception('服务已失效，请重新下单');
                    }
                    $orderItem['spec_sku_key_name'] = $specSku['key_name'];
                } else {
                    $orderItem['spec_sku_key_name'] = '';
                }
                $healthService = $this->healthServiceService->getByPk($orderItem['health_service_id']);
                // 库存
                if((empty($specSku) && $healthService['stock'] == 0)
                    || (!empty($specSku) && $specSku['stock'] == 0)) {
                    throw new Exception('库存不足');
                }
                // 价格
                $salePrice = $healthService['sale_price'];
                if(!empty($specSku)) {
                    $salePrice = $specSku['price'];
                }
                $orderMoney += $orderItem['quantity'] * $salePrice;
                $originalOrderMoney += $orderItem['quantity'] * $salePrice;
                $orderItem['health_service_name'] = $healthService['name'];
                $orderItem['health_service_price'] = $salePrice;
                $orderItem['health_service_image_id'] = $specSku && $specSku['spec_file_id'] > 0 ? $specSku['spec_file_id'] : $healthService['default_image'];

                $item = [
                    'id' => $healthService['id'],
                    'stock' => $healthService['stock'] - 1,
                    'virtual_sales' => $healthService['virtual_sales'] + 1,
                    'real_sales' => $healthService['real_sales'] + 1
                ];
                $this->healthServiceService->updateByPk($item);
                if($specSku) {
                    $this->healthServiceSpecSkuService->decrease('stock', [
                        'id' => $specSku['id'],
                    ]);
                }
                $items[] = $orderItem;

                $order['shipping_type'] = $healthService['shipping_type'];
            }

            if($order['shipping_type'] == HealthServiceOrder::SHIPPING_TYPE_EXPRESS) {
                if($order['consigneer_id'] == 0) {
                    throw new Exception('收货地址不能为空');
                }
                $consigneer = $this->getConsigneerService()->getByPk($order['consigneer_id']);
                $consigneer = $this->getConsigneerService()->format($consigneer);
                $order['consignee'] = $consigneer['name'];
                $order['region_id'] = $consigneer['city_id'];
                $order['region_name'] = $consigneer['region_name'];
                $order['address'] = $consigneer['address'];
                $order['mobile'] = $consigneer['mobile'];
            }

            $order['order_subject'] = '陪诊服务';
            $order['order_sn'] = $this->genSn();
//            $order['store_id'] = $healthService['store_id'];
            $order['order_status'] = HealthServiceOrder::ORDER_STATUS_UNCONFIRMED;
            $order['order_money'] = $orderMoney;
            $order['original_order_money'] = $originalOrderMoney;
            $order['create_time'] = time();
            $order['last_update'] = time();
            $order['payment_status'] = HealthServiceOrder::PAYMENT_STATUS_UNPAIED;
            $order['shipping_status'] = HealthServiceOrder::SHIPPING_STATUS_WAIT_SHIP;

            $order['hospital_city_id'] = 0;
            if($order['hospital_id'] > 0) {
                $hospital = $this->healthHospitalService->getByPk($order['hospital_id']);
                $order['hospital_city_id'] = $hospital['city_id'];
            }

            $orderId = parent::create($order);

            foreach ($items as $orderItem) {
                $specSkuKeyNames = [];
                if(!empty($orderItem['spec_sku_key'])) {
                    $specSkuKeys = explode('_', $orderItem['spec_sku_key']);
                    for ($k = 0; $k < count($specSkuKeys); $k++) {
                        $specItem = $this->healthServiceSpecItemService->getByPk($specSkuKeys[$k]);
                        $spec = $this->healthServiceSpecService->getByPk($specItem['spec_id']);
                        $specSkuKeyNames[] = $spec['name'] . '：' . $specItem['item'];
                    }
                }

                $this->orderItemService->create([
                    'order_id' => $orderId,
                    'health_service_id' => $orderItem['health_service_id'],
                    'health_service_name' => $orderItem['health_service_name'],
                    'health_service_price' => $orderItem['health_service_price'],
                    'health_service_image_id' => $orderItem['health_service_image_id'],
                    'quantity' => $orderItem['quantity'],
                    'spec_sku_key' => $orderItem['spec_sku_key'],
                    'spec_sku_key_name' => implode('\n', $specSkuKeyNames)
                ]);
            }

            //TODO: 发送提醒

            return $order['order_sn'];
        } catch (Exception $e) {
            Log::error('FILE:' . $e->getFile());
            Log::error('LINE:' . $e->getLine());
            Log::error('MSG:' . $e->getMessage());
            throw new Exception('发生错误');
        } finally {
            $lock->unlock($lockKey);
        }
    }

    public function getByOrderSn($orderSn)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if(!empty($order['trade_no'])
            && in_array($order['order_status'], [
                HealthServiceOrder::ORDER_STATUS_UNCONFIRMED,
                HealthServiceOrder::ORDER_STATUS_CONFIRMED
            ]) && $order['payment_status'] == HealthServiceOrder::PAYMENT_STATUS_UNPAIED) {
            try {
                $this->payTradeService->query($order['trade_no']);
            } catch (\Exception $e) {

            }
            $order = $this->getModel()->info(['order_sn' => $orderSn]);
        }
        $order = $this->format($order);
//        $order['items'] = $this->orderItemService->getByOrderId($order['id']);
        return $order;
    }

    public function format(array $order)
    {
        switch ($order['order_status']) {
            case HealthServiceOrder::ORDER_STATUS_CANCELLED:
                $order['order_status_label'] = '已取消';
                break;
            case HealthServiceOrder::ORDER_STATUS_UNCONFIRMED:
                $order['order_status_label'] = '待确认';
                break;
            case HealthServiceOrder::ORDER_STATUS_REFUNDED:
                $order['order_status_label'] = '已退款';
                break;
            case HealthServiceOrder::ORDER_STATUS_FINISHED:
                $order['order_status_label'] = '已完成';
                break;
            case HealthServiceOrder::ORDER_STATUS_CONFIRMED:
                $order['order_status_label'] = '已确认';
                break;
            case HealthServiceOrder::ORDER_STATUS_WAIT_SHIP:
                $order['order_status_label'] = '待派单';
                break;
            case HealthServiceOrder::ORDER_STATUS_SHIPPED:
                $order['order_status_label'] = '已派单';
                break;
            case HealthServiceOrder::ORDER_STATUS_REFUNDING:
                $order['order_status_label'] = '退款中';
                break;
            case HealthServiceOrder::ORDER_STATUS_ACTING:
                $order['order_status_label'] = '服务中';
                break;
            default:
                $order['order_status_label'] = 'N/A';
                break;
        }

        switch ($order['payment_status']) {
            case HealthServiceOrder::PAYMENT_STATUS_UNPAIED:
                $order['payment_status_label'] = '未付款';
                break;
            case HealthServiceOrder::PAYMENT_STATUS_PAIED:
                $order['payment_status_label'] = '已付款';
                break;
            default:
                $order['payment_status_label'] = 'N/A';
                break;
        }

        switch ($order['shipping_status']) {
            case HealthServiceOrder::PAYMENT_STATUS_UNPAIED:
                $order['shipping_status_label'] = '待发货';
                break;
            case HealthServiceOrder::SHIPPING_STATUS_SHIPPED:
                $order['shipping_status_label'] = '已发货';
                break;
            case HealthServiceOrder::SHIPPING_STATUS_REFUNDED:
                $order['shipping_status_label'] = '已退款';
                break;
            default:
                $order['shipping_status_label'] = 'N/A';
                break;
        }

        switch ($order['refund_status']) {
            case HealthServiceOrder::REFUND_STATUS_AUDITING:
                $order['refund_status_label'] = '审核中';
                break;
            case HealthServiceOrder::REFUND_STATUS_GOODS_RETURNING:
                $order['refund_status_label'] = '退货中';
                break;
            case HealthServiceOrder::REFUND_STATUS_PAYING:
                $order['refund_status_label'] = '打款中';
                break;
            case HealthServiceOrder::REFUND_STATUS_FINISHED:
                $order['refund_status_label'] = '已完成';
                break;
            default:
                $order['refund_status_label'] = 'N/A';
                break;
        }

        $order['express_name'] = 'N/A';
        if(!empty($order['express_code'])) {
            $sites = config('site');
            $order['express_name'] = isset($sites['express_company'][$order['express_code']]) ? $sites['express_company'][$order['express_code']]['name'] : 'N/A';
        }

        return $order;
    }

    /**
     * 陪诊师接单
     * @param string $orderSn
     * @param int $healthAssistantUid
     * @return bool
     * @throws Exception
     */
    public function onTakeOrder($orderSn, $healthAssistantUid)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if($order && in_array($order['order_status'], [
                HealthServiceOrder::ORDER_STATUS_WAIT_SHIP,
                HealthServiceOrder::ORDER_STATUS_SHIPPED,
                HealthServiceOrder::ORDER_STATUS_ACTING
            ]) && $order['payment_status'] == HealthServiceOrder::PAYMENT_STATUS_PAIED) {
            $assistant = $this->healthAssistantService->getByUserId($healthAssistantUid);
            if($assistant['status'] != HealthAssistant::STATUS_AUDIT_PASS
                || $order['hospital_city_id'] != $assistant['city_id']) {
                throw new Exception('您的服务范围不包括该就诊医院');
            }
            $result = $this->update([
                'health_assistant_uid' => $healthAssistantUid,
                'order_status' => HealthServiceOrder::ORDER_STATUS_SHIPPED
            ], [
                'order_sn' => $orderSn,
                'health_assistant_uid' => 0,
                'order_status' => HealthServiceOrder::ORDER_STATUS_WAIT_SHIP
            ]);
            if($result) {
                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '订单已派单',
                    'content' => '您的订单已分配陪诊师，点击查看详情。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                return true;
            } else {
                throw new Exception('抢单失败');
            }
        }
        throw new Exception('无法抢单');
    }

    /**
     * 取消订单
     * @param string $orderSn
     * @return bool
     * @throws Exception
     */
    public function cancel($orderSn)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if($order && in_array($order['order_status'], [
                HealthServiceOrder::ORDER_STATUS_UNCONFIRMED,
                HealthServiceOrder::ORDER_STATUS_CONFIRMED
            ]) && $order['payment_status'] == HealthServiceOrder::PAYMENT_STATUS_UNPAIED) {
            $data = [
                'id' => $order['id'],
                'order_status' => HealthServiceOrder::ORDER_STATUS_CANCELLED
            ];
            return $this->updateByPk($data);
        }
        throw new Exception('无法取消');
    }

    /**
     * 确认收货
     * @param string $orderSn
     * @return bool
     * @throws Exception
     */
    public function receiveGoods($orderSn)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if($order
            && in_array($order['order_status'], [HealthServiceOrder::ORDER_STATUS_SHIPPED])
            && $order['payment_status'] == HealthServiceOrder::PAYMENT_STATUS_PAIED) {
            $data = [
                'id' => $order['id'],
                'order_status' => HealthServiceOrder::ORDER_STATUS_FINISHED,
                'finished_time' => time()
            ];
            if($this->updateByPk($data)) {
                //TODO: 给分享者计算佣金

                return true;
            }
        }
        throw new Exception('无法完成操作');
    }

    public function op($orderSn, $op, $act = '')
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if($op == 'refund'
            && in_array($order['order_status'], [
                HealthServiceOrder::ORDER_STATUS_WAIT_SHIP,
//                HealthServiceOrder::ORDER_STATUS_SHIPPED,
                HealthServiceOrder::ORDER_STATUS_REFUNDING
            ])
            && $order['payment_status'] == HealthServiceOrder::PAYMENT_STATUS_PAIED) {
            $msgTitle = '';
            $msgContent = '';

            $data = [
                'id' => $order['id']
            ];
            if($act == 'confirm' && $order['refund_status'] == HealthServiceOrder::REFUND_STATUS_AUDITING) {
                $data['order_status'] = HealthServiceOrder::ORDER_STATUS_REFUNDING;
                $data['refund_status'] = HealthServiceOrder::REFUND_STATUS_GOODS_RETURNING;
                $msgTitle = '订单退款';
                $msgContent = '您的订单退款申请已通过审核，点击查看详情';
            } else if($act == 'return_goods' && $order['refund_status'] == HealthServiceOrder::REFUND_STATUS_GOODS_RETURNING) {
                $data['order_status'] = HealthServiceOrder::ORDER_STATUS_REFUNDING;
                $data['refund_status'] = HealthServiceOrder::REFUND_STATUS_PAYING;
                $data['shipping_status'] = HealthServiceOrder::SHIPPING_STATUS_REFUNDED;
            } else if($act == 'pay' && $order['refund_status'] == HealthServiceOrder::REFUND_STATUS_PAYING) {
                $data['order_status'] = HealthServiceOrder::ORDER_STATUS_REFUNDED;
                $data['refund_status'] = HealthServiceOrder::REFUND_STATUS_FINISHED;
                $msgTitle = '订单退款';
                $msgContent = '您的订单金额已按原路退回到您的账户，点击查看详情';
            } else if($act == 'start') {
                $data['refund_status'] = HealthServiceOrder::REFUND_STATUS_AUDITING;
                $msgTitle = '订单退款';
                $msgContent = '您的订单已发起退款，点击查看详情';
            }
            if($this->updateByPk($data)) {
                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => $msgTitle,
                    'content' => $msgContent,
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                return true;
            }
        } else if($op == 'cancel') {
            if($this->cancel($orderSn)) {
                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '订单已取消',
                    'content' => '您的订单已取消，点击查看详情',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                return true;
            }
        } else if($op == 'finish' && in_array($order['order_status'], [HealthServiceOrder::ORDER_STATUS_ACTING])) {
            $data = [
                'order_status' => HealthServiceOrder::ORDER_STATUS_FINISHED,
                'finished_time' => time()
            ];
            $where = [
                'id' => $order['id'],
                'order_status' => HealthServiceOrder::ORDER_STATUS_ACTING,
            ];
            if($this->update($data, $where)) {
                // 给陪诊师计算佣金
                $healthAssistantSalaryRatio = $this->getSiteConfigService()->getValueByCode('health_assistant_salary_ratio');
                // 无城市合伙人
                $salaryAmount = $order['order_money'] * (floatval($healthAssistantSalaryRatio['direct']) / 100);
                // 有城市合伙人
//                $salaryAmount = $order['order_money'] * (floatval($healthAssistantSalaryRatio['indirect']) / 100);

                $this->getUserService()->increaseWithdrawBalance($order['health_assistant_uid'], $salaryAmount, true, '陪诊服务佣金', UserAccountBook::IID_TYPE_HEALTH_SERVICE_ASSISTANT_SALARY, $order['id']);

                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '订单确认完成',
                    'content' => '您服务的的订单已确认完成，点击查看详情。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['health_assistant_uid'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/my/health_assistant/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['health_assistant_uid'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);

                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '订单已完成',
                    'content' => '您的订单已完成服务，如有疑问请联系客服。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                return true;
            }
        } else if($op == 'acting' && in_array($order['order_status'], [HealthServiceOrder::ORDER_STATUS_SHIPPED])) {
            $data = [
                'order_status' => HealthServiceOrder::ORDER_STATUS_ACTING,
            ];
            $where = [
                'id' => $order['id'],
                'order_status' => HealthServiceOrder::ORDER_STATUS_SHIPPED,
            ];
            if($this->update($data, $where)) {
                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '订单开始服务',
                    'content' => '您的订单已开始服务。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                return true;
            }
        } else if($op == 'confirm' && in_array($order['order_status'], [HealthServiceOrder::ORDER_STATUS_UNCONFIRMED])) {
            $data = [
                'order_status' => HealthServiceOrder::ORDER_STATUS_CONFIRMED,
            ];
            $where = [
                'id' => $order['id'],
                'order_status' => HealthServiceOrder::ORDER_STATUS_UNCONFIRMED,
            ];
            if($this->update($data, $where)) {
                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '订单已确认',
                    'content' => '您的订单已平台已确认接单。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                return true;
            }
        }
        throw new Exception('操作失败');
    }

    /**
     * 修改付款金额
     * @param string $orderSn
     * @param float $orderMoney
     * @return bool
     * @throws Exception
     */
    public function modifyOrderMoney($orderSn, $orderMoney)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if($order && in_array($order['order_status'], [
                HealthServiceOrder::ORDER_STATUS_UNCONFIRMED,
                HealthServiceOrder::ORDER_STATUS_CONFIRMED
            ]) && $order['payment_status'] == HealthServiceOrder::PAYMENT_STATUS_UNPAIED) {
            $data = [
                'id' => $order['id'],
                'order_money' => $orderMoney
            ];
            if(parent::updateByPk($data)) {
                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '修改订单金额',
                    'content' => '您的订单金额已修改，点击查看详情。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);

                return true;
            }
        }
        throw new Exception('无法完成操作');
    }

    /**
     * 修改收货信息
     * @param string $orderSn
     * @param array $consigneer
     * @return bool
     * @throws Exception
     */
    public function modifyConsigneer($orderSn, array $consigneer)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if($order && in_array($order['order_status'], [
                HealthServiceOrder::ORDER_STATUS_UNCONFIRMED,
                HealthServiceOrder::ORDER_STATUS_CONFIRMED,
                HealthServiceOrder::ORDER_STATUS_WAIT_SHIP
            ])) {
            $data = [
                'id' => $order['id'],
                'consignee' => $consigneer['name'],
                'mobile' => $consigneer['mobile'],
                'region_id' => $consigneer['region_id'],
                'region_name' => $consigneer['region_name'],
                'address' => $consigneer['address'],
            ];
            return parent::updateByPk($data);
        }
        throw new Exception('无法完成操作');
    }

    /**
     * 发货
     * @param string $orderSn           --订单号
     * @param string $expressSn         --物流单号
     * @param string $expressCode       --物流公司编码
     * @return bool
     * @throws Exception
     */
    public function ship($orderSn, $expressSn, $expressCode)
    {
        $order = $this->getModel()->info(['order_sn' => $orderSn]);
        if($order
            && $order['shipping_type'] == HealthServiceOrder::SHIPPING_TYPE_EXPRESS
            && empty($order['express_code'])
            && $order['payment_status'] == HealthServiceOrder::PAYMENT_STATUS_PAIED) {
            $data = [
                'id' => $order['id'],
//                'order_status' => HealthServiceOrder::ORDER_STATUS_SHIPPED,
                'shipping_status' => HealthServiceOrder::SHIPPING_STATUS_SHIPPED,
                'shipping_time' => time(),
                'express_sn' => $expressSn,
                'express_code' => $expressCode
            ];
            if($this->updateByPk($data)) {
                $systemNoticeId = $this->getSystemNoticeService()->create([
                    'title' => '订单已发货',
                    'content' => '您的订单已发货，点击查看详情。',
                    'type' => SystemNotice::TYPE_SINGLE,
                    'status' => SystemNotice::STATUS_PULLED,
                    'recipient_id' => $order['buyer_id'],
                    'manager_id' => 0,
                    'url' => json_encode([
                        'weapp' => '/pages/order/detail?sn=' . $orderSn,
                        'web' => '',
                        'app' => ''
                    ])
                ]);
                $this->getUserSystemNoticeService()->create([
                    'system_notice_id' => $systemNoticeId,
                    'recipient_id' => $order['buyer_id'],
                    'status' => UserSystemNotice::STATUS_WAIT_READ,
                    'pull_time' => time()
                ]);
                return  true;
            }
        }
        throw new Exception('发生错误');
    }

    /**
     * 创建支付交易后回调
     * @param $id
     * @param $tradeNo
     * @return bool
     */
    public function onPayTradeCreated($id, $tradeNo)
    {
        return $this->updateByPk([
            'id' => $id,
            'trade_no' => $tradeNo
        ]);
    }

    /**
     * 支付成功后回调
     * @param string $tradeNo
     * @param string $transactionId
     * @param int $payTime
     * @param string $paymentName
     * @return bool
     * @throws Exception
     */
    public function onPaySuccess($tradeNo, $transactionId, $payTime, $paymentName)
    {
        $order = $this->info([
            'trade_no' => $tradeNo
        ]);
        if (!$order) {
            throw new Exception('订单不存在');
        }
        return $this->payConfirm($order['id'], $payTime, $paymentName, $transactionId);
    }

    /**
     * 后台确认已付款
     * @param $orderId              --订单ID
     * @param $payTime              --付款时间
     * @param $payPlatformName      --支付平台名称
     * @param $payPlatformTradeNo   --支付平台流水号
     * @return bool
     * @throws
     */
    public function payConfirm($orderId, $payTime, $payPlatformName, $payPlatformTradeNo)
    {
        $order = $this->getByPk($orderId);
        if (!in_array($order['order_status'], [
                HealthServiceOrder::ORDER_STATUS_UNCONFIRMED,
                HealthServiceOrder::ORDER_STATUS_CONFIRMED
            ]) || $order['payment_status'] != HealthServiceOrder::PAYMENT_STATUS_UNPAIED) {
            throw new Exception('只有已确认待付款订单才可确认付款');
        }
        $data = [
            'id' => $orderId,
            'order_status' => HealthServiceOrder::ORDER_STATUS_WAIT_SHIP,
            'shipping_status' => HealthServiceOrder::SHIPPING_STATUS_WAIT_SHIP,
            'payment_status' => HealthServiceOrder::PAYMENT_STATUS_PAIED,
            'pay_time' => $payTime,
            'transaction_id' => $payPlatformTradeNo,
            'payment_name' => $payPlatformName
        ];
        if ($this->updateByPk($data)) {
//            $queueHandler = null;
//            try {
//                // 发短信、邮件
//                $musicConfig = config('music.');
//
//                $queueHandler = QueueFactory::instance();
//                $queuePrefix = $queueHandler->getOption('prefix');
//                $queueHandler->setOption('prefix', 'vm_sms:queue:');
//
//                $mailBody = '<h1>订单付款通知</h1><br />'
//                    . '<p>客户姓名：' . $order['consignee'] . '</p>'
//                    . '<p>客户手机：<a href="tel:' . $order['mobile'] . '">' . $order['mobile'] . '</a></p>'
//                    . '<p>订单号：' . $order['order_sn'] . '</p>'
//                    . '<p>订单金额：' . $order['original_order_money'] . '</p>'
//                    . '<p>实付金额：' . $order['order_money'] . '</p>'
//                    . '<p>付款状态：已付款</p>'
//                    . '<p><a href="' . str_replace('{order_sn}', $order['order_sn'], $musicConfig['urls']['op']['order_detail']) . '">查看详情</a></p>';
//                $mail = [
//                    'to' => $musicConfig['service_email'],
//                    'toName' => '客服',
//                    'subject' => '订单付款通知【' . $order['order_sn'] . '】',
//                    'body' => $mailBody
//                ];
//                $queueHandler->set('vm_mails', $mail);
//            } catch (Exception $e) {
//
//            } finally {
//                if($queueHandler) {
//                    $queueHandler->setOption('prefix', $queuePrefix);
//                }
//            }
            return true;
        }
        throw new Exception('操作失败');
    }
}