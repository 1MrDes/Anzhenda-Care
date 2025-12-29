<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthServiceSpecSku;
use vm\com\BaseService;
use vm\com\logic\FileLogic;

class HealthServiceSpecSkuService extends BaseService
{

    /**
     * @return HealthServiceSpecSku
     */
    protected function getModel()
    {
        return new HealthServiceSpecSku();
    }

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
     * @return HealthServiceSpecItemService
     */
    public function getHealthServiceSpecItemService()
    {
        return service('HealthServiceSpecItem', SERVICE_NAMESPACE);
    }

    public function create(array $data)
    {
        if(empty($data['sku'])) {
            $data['sku'] = $this->genSku($data['health_service_id']);
        }
        return parent::create($data);
    }

    private function genSku($healthServiceId)
    {
        while (true) {
            $sku = rand_string(20, 1);
            if(!$this->info(['sku' => $sku, 'health_service_id' => $healthServiceId])) {
                return $sku;
            }
        }
    }

    public function save(array $data)
    {
        if($last = $this->info(['health_service_id' => $data['health_service_id'], 'key' => $data['key']])) {
            $data['id'] = $last['id'];
            $this->updateByPk($data);
            return $data['id'];
        } else {
            return $this->create($data);
        }
    }

    public function saveHealthServiceSpecPrices($healthServiceId, array $specPrices)
    {
        if(empty($specPrices)) {
            return $this->delete(['health_service_id' => $healthServiceId]);
        }
        $oldSpecPrices = $this->getAll(['health_service_id' => $healthServiceId]);
        $specItemService = $this->getHealthServiceSpecItemService();
        foreach ($specPrices as $item) {
            $last = $this->info([
                'health_service_id' => $healthServiceId,
                'key' => $item['key']
            ]);
            if($last) {
                $this->updateByPk($item);
            } else {
                $item['health_service_id'] = $healthServiceId;
                if(empty($item['sku'])) {
                    $item['sku'] = $this->genSku($healthServiceId);
                }
                $item['bar_code'] = '';
                $item['key_name'] = '';
                $specItemIds = explode('_', $item['key']);
                foreach ($specItemIds as $itemId) {
                    $specItem = $specItemService->getByPk($itemId);
                    $item['key_name'] .= (empty($item['key_name']) ? "" : "\n") . $specItem['item'];
                }
                $id = $this->create($item);
                $item['id'] = $id;
            }
        }
        if(!empty($oldSpecPrices)) {
            foreach ($oldSpecPrices as $oldSpecPrice) {
                $isExists = false;
                foreach ($specPrices as $specPrice) {
                    if($specPrice['key'] == $oldSpecPrice['key']) {
                        $isExists = true;
                        break;
                    }
                }
                if(!$isExists) {
                    $this->deleteByPk($oldSpecPrice['id']);
                }
            }
        }
    }

    public function getByHealthServiceId($healthServiceId)
    {
        $items = $this->getAll(['health_service_id' => $healthServiceId]);
        if(!empty($items)) {
            foreach ($items as &$item) {
                if($item['spec_file_id'] > 0) {
                    $file = $this->getFileLogic()->file($item['spec_file_id']);
                    $item['spec_file_url'] = $file ? $file['url'] : '';
                } else {
                    $item['spec_file_url'] = '';
                }
            }
        }
        return $items;
    }

    public function deleteByHealthServiceId($healthServiceId)
    {
        return $this->delete(['health_service_id' => $healthServiceId]);
    }
}