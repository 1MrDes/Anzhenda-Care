<?php


namespace apps\health_assist\app\api\controller;


use apps\health_assist\core\service\HealthServiceAttributeService;
use apps\health_assist\core\service\HealthServiceCategoryService;
use apps\health_assist\core\service\HealthServiceImageService;
use apps\health_assist\core\service\HealthServiceService;
use apps\health_assist\core\service\HealthServiceSpecItemService;
use apps\health_assist\core\service\HealthServiceSpecService;
use apps\health_assist\core\service\HealthServiceSpecSkuService;
use apps\health_assist\core\service\HealthServiceTypeAttributeService;
use think\Exception;
use apps\health_assist\app\Request;
use vm\com\logic\RegionLogic;

class HealthServiceController extends BaseHealthAssistApiController
{
    /**
     * @var HealthServiceService
     */
    private $goodsService;

    /**
     * @var HealthServiceImageService
     */
    private $goodsImageService;

    /**
     * @var HealthServiceAttributeService
     */
    private $goodsAttributeService;

    /**
     * @var HealthServiceTypeAttributeService
     */
    private $goodsTypeAttributeService;

    /**
     * @var HealthServiceSpecSkuService
     */
    private $goodsSpecPriceService;

    /**
     * @var HealthServiceSpecItemService
     */
    private $goodsSpecItemService;

    /**
     * @var HealthServiceSpecService
     */
    private $goodsSpecService;

    /**
     * @var HealthServiceCategoryService
     */
    private $goodsCategoryService;

    /**
     * @var RegionLogic
     */
    private $regionLogic;

    protected function init()
    {
        parent::init();
        $this->goodsService = service('HealthService', SERVICE_NAMESPACE);
        $this->goodsCategoryService = service('HealthServiceCategory', SERVICE_NAMESPACE);
        $this->goodsImageService = service('HealthServiceImage', SERVICE_NAMESPACE);
        $this->goodsAttributeService = service('HealthServiceAttribute', SERVICE_NAMESPACE);
        $this->goodsTypeAttributeService = service('HealthServiceTypeAttribute', SERVICE_NAMESPACE);
        $this->goodsSpecPriceService = service('HealthServiceSpecSku', SERVICE_NAMESPACE);
        $this->goodsSpecItemService = service('HealthServiceSpecItem', SERVICE_NAMESPACE);
        $this->goodsSpecService = service('HealthServiceSpec', SERVICE_NAMESPACE);
        $this->regionLogic = logic('Region', '\vm\com\logic\\');
        $this->regionLogic->init([
            'rpc_server' => env('rpc_base.host') . '/region',
            'rpc_provider' => env('rpc_base.provider'),
            'rpc_token' => env('rpc_base.token'),
        ]);
    }

    public function lists(Request $request)
    {
        $pageSize = $request->param('page_size', 15, 'intval');
        $pageSize = 20;
        $categoryId = $request->param('category_id', 0, 'intval');
        $tag = $request->param('tag', '');
        $params = [
            'is_deleted' => 0,
            'on_sale' => 1
        ];
        if($categoryId > 0) {
            $params['category_id'] = $categoryId;
        }
        if($tag) {
            $params['tag'] = $tag;
        }
        $sortOrder = [
            'sort_order' => 'ASC',
            'id' => 'DESC'
        ];
        $res = $this->goodsService->search($params, $pageSize, $sortOrder);
        if($res['data']) {
            foreach ($res['data'] as &$rs) {
                $rs = $this->goodsService->format($rs);
                $rs = arrayOnly($rs, [
                    'id',
                    'name',
                    'subheading',
                    'default_image_url',
                    'market_price',
                    'sale_price',
                    'short_name',
                    'virtual_sales'
                ]);
            }
        }
        return $this->success($res);
    }

    public function info(Request $request)
    {
        $id = $request->param('id', 0, 'intval');
        $goods = $this->goodsService->getByPk($id);
        if(empty($goods)) {
            throw new Exception('商品不存在');
        }
        $goods = $this->goodsService->format($goods);

        $goods['categories'] = [];
        if(!empty($goods['category'])) {
            $categoryIds = explode(',', $goods['category']);
            foreach ($categoryIds as $categoryId) {
                $category = $this->goodsCategoryService->getByPk($categoryId);
                if($category) {
                    $goods['categories'][] = $category;
                }
            }
        }

        $goodsImages = $this->goodsImageService->getByHealthServiceId($id);
        $goodsAttrs = $this->goodsAttributeService->getByHealthServiceId($id);
        if($goodsAttrs) {
            foreach ($goodsAttrs as &$goodsAttr) {
                $attr = $this->goodsTypeAttributeService->getByPk($goodsAttr['attr_id']);
                $goodsAttr['attr_name'] = $attr['name'];
            }
        }
        $goodsSpecs = [];
        $goodsSpecSkuList = $this->goodsSpecPriceService->getByHealthServiceId($id);
        if(!empty($goodsSpecSkuList)) {
            foreach ($goodsSpecSkuList as $goodsSpecSku) {
                $specItemIds = explode('_', $goodsSpecSku['key']);
                foreach ($specItemIds as $specItemId) {
                    $specItem = $this->goodsSpecItemService->getByPk($specItemId);
                    $spec = $this->goodsSpecService->getByPk($specItem['spec_id']);
                    if(!isset($goodsSpecs['spec_' . $specItem['spec_id']])) {
                        $goodsSpecs['spec_' . $specItem['spec_id']] = [
                            'spec' => $spec,
                            'spec_items' => []
                        ];
                    }
                    $goodsSpecs['spec_' . $specItem['spec_id']]['spec_items']['item_' . $specItem['id']] = $specItem;
                }
            }
        }

        return $this->success([
            'goods' => $goods,
            'goods_images' => $goodsImages,
            'goods_attrs' => $goodsAttrs,
            'goods_specs' => $goodsSpecs,
            'goods_spec_sku_list' => $goodsSpecSkuList
        ]);
    }

    public function batch_info(Request $request)
    {
        $ids = $request->param('ids');
        $goodsList = [];
        if($ids) {
            $ids = explode('|', $ids);
            foreach ($ids as $id) {
                $goods = $this->goodsService->getByPk($id);
                $goods = $this->goodsService->format($goods);
                $goodsList[] = $goods;
            }
        }
        return $this->success(['goods_list' => $goodsList]);
    }
}