<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceOrderItem;
use think\Exception;
use think\facade\Validate;
use vm\com\BaseService;
use vm\com\logic\FileLogic;

class HealthServiceOrderItemService extends BaseService
{
    private $rule = [
        'order_id' => 'number|>:0',
        'health_service_id' => 'number|>:0',
        'quantity' => 'number|>:0'
    ];

    private $msg = [
        'order_id' => '订单ID必填',
        'health_service_id' => '服务ID必填',
        'quantity' => '原始订单金额必填'
    ];

    private $fileLogic = null;
    /**
     * @return FileLogic
     */
    private function getFileLogic()
    {
        if($this->fileLogic !== null) {
            return $this->fileLogic;
        }
        $this->fileLogic = logic('File', 'vm\com\logic\\');
        $this->fileLogic->init([
            'rpc_server' => env('rpc_file.host') . '/file',
            'rpc_provider' => env('rpc_file.provider'),
            'rpc_token' => env('rpc_file.token'),
        ]);
        return $this->fileLogic;
    }

    /**
     * @inheritDoc
     * @return HealthServiceOrderItem
     */
    protected function getModel()
    {
        return new HealthServiceOrderItem();
    }

    private function genSn()
    {
        while(true) {
            $sn = date('ymdHis') . rand_string(8, 1);
            if(!$this->getModel()->info(['sub_order_sn' => $sn])) {
                return $sn;
            }
        }
    }

    public function create(array $data)
    {
        $validate   = Validate::rule($this->rule, $this->msg);
        $result = $validate->check($data);
        if(!$result) {
            throw new Exception($validate->getError());
        }

        $data['sub_order_sn'] = $this->genSn();
        $data['dateline'] = time();
        $data['last_update'] = time();
        return parent::create($data);
    }

    public function getBySubOrderSn($sn)
    {
        $item = $this->getModel()->info(['sub_order_sn' => $sn]);
        return $this->format($item);
    }

    public function getByOrderId($orderId)
    {
        $res = $this->getAll([
            'order_id' => $orderId,
            'is_deleted' => 0
        ]);
        foreach ($res as &$rs) {
            $rs = $this->format($rs);
        }
        return $res;
    }

    public function format(array $item)
    {
        if($item['health_service_image_id'] > 0) {
            $file = $this->getFileLogic()->file($item['health_service_image_id']);
            $item['health_service_image_url'] = $file['url'];
        } else {
            $item['health_service_image_url'] = '';
        }
        return $item;
    }
}