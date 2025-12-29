<?php


namespace apps\health_assist\core\service;


use apps\health_assist\core\model\HealthService;
use vm\com\BaseService;
use vm\com\logic\FileLogic;
use vm\com\logic\WechatMiniAppLogic;

class HealthServiceService extends BaseService
{
    /**
     * @var HealthServiceImageService
     */
    protected $healthServiceImageService;

    /**
     * @var HealthServiceSpecSkuService
     */
    private $healthServiceSpecSkuService;

    /**
     * @var HealthServiceSpecService
     */
    private $healthServiceSpecService;

    /**
     * @var HealthServiceAttributeService
     */
    private $healthServiceAttributeService;

    /**
     * @var HealthServiceTagService
     */
    private $healthServiceTagService;

    /**
     * @var WechatMiniAppLogic
     */
    private $wxMiniLogic;

    /**
     * @var SiteConfigService
     */
    private $siteConfigService;

    protected function init()
    {
        parent::init();
        $this->healthServiceImageService = service('HealthServiceImage', SERVICE_NAMESPACE);
        $this->healthServiceSpecService = service('HealthServiceSpec', SERVICE_NAMESPACE);
        $this->healthServiceSpecSkuService = service('HealthServiceSpecSku', SERVICE_NAMESPACE);
        $this->healthServiceAttributeService = service('HealthServiceAttribute', SERVICE_NAMESPACE);
        $this->healthServiceTagService = service('HealthServiceTag', SERVICE_NAMESPACE);
        $this->siteConfigService = service('SiteConfig', SERVICE_NAMESPACE);
        $this->wxMiniLogic = logic('WechatMiniApp', 'vm\com\logic\\');
        $this->wxMiniLogic->init([
            'app_id' => $this->siteConfigService->getValueByCode('weapp_app_id'),
            'app_secret' => $this->siteConfigService->getValueByCode('weapp_app_secret'),
            'app_token' => $this->siteConfigService->getValueByCode('weapp_app_token'),
            'encode_aeskey' => $this->siteConfigService->getValueByCode('weapp_app_encoding_aeskey'),
        ]);
    }

    /**
     * @return HealthServiceSpecItemService
     */
    public function getHealthServiceSpecItemService()
    {
        return service('HealthServiceSpecItem', SERVICE_NAMESPACE);
    }

    /**
     * @return HealthServiceCategoryService
     */
    public function getHealthServiceCategoryService()
    {
        return service('HealthServiceCategory', SERVICE_NAMESPACE);
    }

    /**
     * @return HealthServiceBelongCategoryService
     */
    public function getHealthServiceBelongCategoryService()
    {
        return service('HealthServiceBelongCategory', SERVICE_NAMESPACE);
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
     * @inheritDoc
     * @return HealthService
     */
    protected function getModel()
    {
        return new HealthService();
    }

    public function deleteByPk($id)
    {
        $data = [
            'id' => $id,
            'is_deleted' => 1,
            'update_time' => time()
        ];
        return $this->updateByPk($data);
    }

    private function genSn()
    {
        while (true) {
            $sn = date('YmdHis') . rand_string(6, 1);
            if (!$this->getModel()->info(['sn' => $sn])) {
                return $sn;
            }
        }
    }

    public function save(array $healthService, array $images, array $specs, array $specSkuList, array $attrs)
    {
        $healthServiceId = 0;
        $tags = empty($healthService['tags']) ? [] : explode(',', $healthService['tags']);
        unset($healthService['tags']);
        $tagIds = [];
        if (!empty($tags)) {
            $this->healthServiceTagService->saveTags($tags);
            foreach ($tags as $v) {
                $tag = $this->healthServiceTagService->info(['name' => $v]);
                $tagIds[] = $tag['id'];
            }
        }
        $healthService['tag_ids'] = implode(',', $tagIds);

        $categories = empty($healthService['categories']) ? [] : $healthService['categories'];
        unset($healthService['categories']);
        $healthService['category'] = [];
        $categoryService = $this->getHealthServiceCategoryService();
        foreach ($categories as $category) {
            $healthService['category'][] = $category['id'];
            $parents = $categoryService->getParents($category['id']);
            if(!empty($parents)) {
                foreach ($parents as $parent) {
                    $healthService['category'][] = $parent['id'];
                }
            }
        }
        $healthService['category'] = array_unique($healthService['category']);
        $healthService['category'] = implode(',', $healthService['category']);

        if ($healthService['id'] == 0) {
            $healthService['sn'] = $this->genSn();
            $healthService['add_time'] = time();
            $healthServiceId = $this->create($healthService);
        } else {
            $healthService['update_time'] = time();
            $this->updateByPk($healthService);
            $healthServiceId = $healthService['id'];
            $this->healthServiceImageService->deleteByHealthServiceId($healthServiceId);
        }
        // 分类
        $belongCategoryService = $this->getHealthServiceBelongCategoryService();
        $belongCategoryService->delete([
            'health_service_id' => $healthServiceId
        ]);
        foreach ($categories as $category) {
            $belongCategoryService->create([
                'health_service_id' => $healthServiceId,
                'category_id' => $category['id']
            ]);
        }

        // 图片
        if (!empty($images)) {
            foreach ($images as $item) {
                $data = [
                    'health_service_id' => $healthServiceId,
                    'image_id' => $item['image_id']
                ];
                $this->healthServiceImageService->create($data);
            }
        }
        // 规格
        $specItemService = $this->getHealthServiceSpecItemService();
        $lastSpecs = $this->healthServiceSpecService->findAll($healthServiceId);
        $lastSpecPrices = $this->healthServiceSpecSkuService->getByHealthServiceId($healthServiceId);
        $lastSpecItems = $specItemService->getAll(['health_service_id' => $healthServiceId]);
        $newSpecIds = [];
        $newSpecPriceIds = [];
        $newSpecItemIds = [];
        if(!empty($specs)) {
            $specsTemp = [];
            foreach ($specs as $spec) {
                $specValue = [];
                $specValueNames = [];
                foreach ($specSkuList as $specSku) {
                    foreach ($specSku['values'] as $key => $val) {
                        list(, $uuid) = explode('_', $key);
                        if ($spec['uuid'] == $uuid) {
                            if (!isset($specValueNames[$uuid])) {
                                $specValueNames[$uuid] = [];
                            }
                            if (!in_array($val, $specValueNames[$uuid])) {
                                $specValue[] = [
                                    'name' => $val
                                ];
                                $specValueNames[$uuid][] = $val;
                            }
                        }
                    }
                }
                $spec['value'] = json_encode($specValue);
                $spec['health_service_id'] = $healthServiceId;
                $spec['id'] = $this->healthServiceSpecService->save($spec);
                $specsTemp[$spec['uuid']] = $spec;
                $newSpecIds[] = $spec['id'];
            }
            $specSkuListTemp = [];
            if (!empty($specSkuList)) {
                foreach ($specSkuList as $specSku) {
                    $values = $specSku['values'];
                    unset($specSku['values']);
                    $keys = [];
                    $keyNames = [];
                    foreach ($values as $key => $val) {
                        list(, $uuid) = explode('_', $key);
                        $specItemId = $specItemService->save([
                            'health_service_id' => $healthServiceId,
                            'spec_id' => $specsTemp[$uuid]['id'],
                            'item' => $val
                        ]);
                        $keys[] = $specItemId;
                        $keyNames[] = $val;
                        $newSpecItemIds[] = $specItemId;
                    }
                    $specSku['key'] = implode('_', $keys);
                    $specSku['key_name'] = implode("\n", $keyNames);
                    $specSku['health_service_id'] = $healthServiceId;
                    $specSku['id'] = $this->healthServiceSpecSkuService->save($specSku);
                    $specSkuListTemp[] = $specSku;
                    $newSpecPriceIds[] = $specSku['id'];
                }
            }
            $sData = [];
            $sData['id'] = $healthServiceId;
            $sData['stock'] = 0;
            $sData['sale_price'] = $specSkuListTemp[0]['price'];
            $sData['default_spec_sku_key'] = $specSkuListTemp[0]['key'];
            foreach ($specSkuListTemp as $val) {
                $sData['stock'] += $val['stock'];
                if ($val['price'] < $sData['sale_price']) {
                    $sData['sale_price'] = $val['price'];
                    $sData['default_spec_sku_key'] = $val['key'];
                }
            }
            $sData['spec_num'] = count($specSkuListTemp);
            $this->updateByPk($sData);
        } else {
            $sData = [];
            $sData['id'] = $healthServiceId;
            $sData['spec_num'] = 0;
            $sData['default_spec_sku_key'] = '';
            $this->updateByPk($sData);
        }
        foreach ($lastSpecs as $item) {
            if(!in_array($item['id'], $newSpecIds)) {
                $this->healthServiceSpecService->deleteByPk($item['id']);
            }
        }
        foreach ($lastSpecPrices as $item) {
            if(!in_array($item['id'], $newSpecPriceIds)) {
                $this->healthServiceSpecSkuService->deleteByPk($item['id']);
            }
        }
        foreach ($lastSpecItems as $item) {
            if(!in_array($item['id'], $newSpecItemIds)) {
                $specItemService->deleteByPk($item['id']);
            }
        }

        // 属性
        $this->healthServiceAttributeService->delete(['health_service_id' => $healthServiceId]);
        if (!empty($attrs)) {
            $this->healthServiceAttributeService->saveHealthServiceAttrs($healthServiceId, $attrs);
        }
    }

    public function format(array $healthService)
    {
        $healthService['default_image_url'] = '';
        if ($healthService['default_image'] > 0) {
            $file = $this->getFileLogic()->file($healthService['default_image']);
            $healthService['default_image_url'] = $file['url'];
        }
        $healthService['tags'] = '';
        if (!empty($healthService['tag_ids'])) {
            $tags = [];
            $tagIds = explode(',', $healthService['tag_ids']);
            foreach ($tagIds as $tagId) {
                $tag = $this->healthServiceTagService->getByPk($tagId);
                $tags[] = $tag['name'];
            }
            $healthService['tags'] = implode(',', $tags);
        }
        return $healthService;
    }

    public function submitPages2Weapp()
    {
        $page = 1;
        while (true) {
            $res = $this->pageList(100, false, ['page' => $page]);
            if (!$res || !$res['data']) {
                break;
            }
            $pages = [];
            foreach ($res['data'] as $rs) {
                $pages[] = [
                    'path' => 'pages/health_service/detail',
                    'query' => 'id=' . $rs['id']
                ];
            }
            $this->wxMiniLogic->submitPages($pages);
            $page++;
            sleep(1);
        }
    }

    public function search(array $params, $pageSize, array $sortOrder = [])
    {
        if (isset($params['tag'])) {
            $tag = $this->healthServiceTagService->info(['name' => $params['tag']]);
            if ($tag) {
                $params['tag_id'] = $tag['id'];
            }
            unset($params['tag']);
        }
        return $this->getModel()->pageListByParams($params, $pageSize, [], $sortOrder);
    }

}