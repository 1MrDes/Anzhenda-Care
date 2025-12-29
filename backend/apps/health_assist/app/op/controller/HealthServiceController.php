<?php


namespace apps\health_assist\app\op\controller;

use apps\health_assist\core\service\HealthServiceAttributeService;
use apps\health_assist\core\service\HealthServiceCategoryService;
use apps\health_assist\core\service\HealthServiceImageService;
use apps\health_assist\core\service\HealthServiceService;
use apps\health_assist\core\service\HealthServiceSpecItemService;
use apps\health_assist\core\service\HealthServiceSpecService;
use apps\health_assist\core\service\HealthServiceSpecSkuService;
use apps\health_assist\core\service\HealthServiceTypeService;
use think\Exception;
use apps\health_assist\app\Request;

class HealthServiceController extends BaseHealthAssistOpController
{
    /**
     * @var HealthServiceService
     */
    private $healthServiceService;

    /**
     * @var HealthServiceCategoryService
     */
    private $categoryService;

    /**
     * @var HealthServiceTypeService
     */
    private $goodsTypeService;

    /**
     * @var HealthServiceImageService
     */
    private $goodsImageService;

    /**
     * @var HealthServiceSpecService
     */
    private $goodsSpecService;

    /**
     * @var HealthServiceSpecItemService
     */
    private $goodsSpecItemService;

    /**
     * @var HealthServiceSpecSkuService
     */
    private $goodsSpecSkuService;

    /**
     * @var HealthServiceAttributeService
     */
    private $goodsAttributeService;

    protected function init()
    {
        parent::init();
        $this->healthServiceService = service('HealthService', SERVICE_NAMESPACE);
        $this->categoryService = service('HealthServiceCategory', SERVICE_NAMESPACE);
        $this->goodsTypeService = service('HealthServiceType', SERVICE_NAMESPACE);
        $this->goodsImageService = service('HealthServiceImage', SERVICE_NAMESPACE);
        $this->goodsSpecService = service('HealthServiceSpec', SERVICE_NAMESPACE);
        $this->goodsSpecItemService = service('HealthServiceSpecItem', SERVICE_NAMESPACE);
        $this->goodsSpecSkuService = service('HealthServiceSpecSku', SERVICE_NAMESPACE);
        $this->goodsAttributeService = service('HealthServiceAttribute', SERVICE_NAMESPACE);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 10);
        $keywords = $request->param('keywords', '');
        $params = [
            'is_deleted' => 0
        ];
        if(!empty($keywords)) {
            $params['keywords'] = $keywords;
        }
        $res = $this->healthServiceService->pageListByParams($params, $pageSize);
        foreach ($res['data'] as &$rs) {
            $rs = $this->healthServiceService->format($rs);
        }
        return $this->success($res);
    }

    public function save(Request $request)
    {
        $goods = $request->param('goods');
        $goodsImages = $request->param('goods_images', []);
        $goodsSpecs = $request->param('goods_specs', []);
        $specSkuList = $request->param('goods_spec_sku_list', []);
        $goodsAttrs = $request->param('goods_attrs', []);
        $this->healthServiceService->save($goods, $goodsImages, $goodsSpecs, $specSkuList, $goodsAttrs);
        return $this->success();
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $goods = $this->healthServiceService->getByPk($id);
        if(empty($goods)) {
            throw new Exception('商品不存在');
        }
        $goods = $this->healthServiceService->format($goods);

        $goods['categories'] = [];
        if(!empty($goods['category'])) {
            $categoryIds = explode(',', $goods['category']);
            foreach ($categoryIds as $categoryId) {
                $goods['categories'][] = $this->categoryService->getByPk($categoryId);
            }
        }

        if($goods['type_id'] > 0) {
            $goods['health_service_type'] = $this->goodsTypeService->getByPk($goods['type_id']);
        } else {
            $goods['health_service_type'] = [
                'id' => 0,
                'name' => ''
            ];
        }
        $goodsImages = $this->goodsImageService->getByHealthServiceId($id);
        $goodsAttrs = $this->goodsAttributeService->getAll(['health_service_id' => $id]);
        $goodsSpecs = $this->goodsSpecService->findAll($id);
        $goodsSpecSkuList = $this->goodsSpecSkuService->getByHealthServiceId($id);
        if(!empty($goodsSpecSkuList)) {
            foreach ($goodsSpecSkuList as &$goodsSpecSku) {
                $values = [];
                $specItemIds = explode('_', $goodsSpecSku['key']);
                foreach ($specItemIds as $specItemId) {
                    $specItem = $this->goodsSpecItemService->getByPk($specItemId);
                    $spec = $this->goodsSpecService->getByPk($specItem['spec_id']);
//                    if(!isset($values[$spec['uuid']])) {
//                        $values[$spec['uuid']] = [];
//                    }
                    $values['spec_' . $spec['uuid']] = $specItem['item'];
                }
                $goodsSpecSku['values'] = $values;
            }
        }
        return $this->success([
            'goods' => $goods,
            'goods_images' => $goodsImages,
            'goods_specs' => $goodsSpecs,
            'goods_spec_sku_list' => $goodsSpecSkuList,
            'goods_attrs' => $goodsAttrs
        ]);
    }

    public function delete(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $this->healthServiceService->deleteByPk($id);
        return $this->success();
    }

    public function toggle_sale_status(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $saleStatus = $request->param('sale_status', 0, 'intval');
        $this->healthServiceService->updateByPk([
            'id' => $id,
            'on_sale' => $saleStatus
        ]);
        return $this->success();
    }
}