<template>
	<view class="flex flex-col" v-if="service">
		<view class="flex flex-col color-white relative" style="height: 200rpx;">
			<image :src="STATIC_BASE_URL + 'images/banner-service-detail.jpg'"
				style="height:200rpx;width: 100%;" class="absolute" />
			<view class="flex flex-col" style="height: 200rpx; position: absolute; left: 0; right: 0; top: 0;">
				<view class="flex flex-row flex-left flex-wrap pl10 pr10 mt10">
					<text class="font-large">{{service.name}}</text>
				</view>
				<view class="flex flex-row flex-left flex-wrap mt10 pl10 pr10 pb10">
					<text class="font-common">{{service.subheading}}</text>
				</view>
			</view>
		</view>

		<u-sticky>
			<view class="flex flex-row flex-center bg-color-white navs">
				<view class="flex flex-row flex-center flex-1 nav-item">
					<button class="invisibility" @click="onNavChange('service')">
						<view class="flex flex-row flex-center nav-item-btn-box">
							<text class="font-big" v-bind:class="{active:currentNav == 'service', inactive:currentNav != 'service'}">服务内容</text>
						</view>
					</button>
				</view>

				<view class="flex flex-row flex-center flex-1 nav-item">
					<button class="invisibility" @click="onNavChange('attention')">
						<view class="flex flex-row flex-center nav-item-btn-box">
							<text class="font-big"v-bind:class="{active:currentNav == 'attention', inactive:currentNav != 'attention'}">服务须知</text>
						</view>
					</button>
				</view>

				<view class="flex flex-row flex-center flex-1 nav-item">
					<button class="invisibility" @click="onNavChange('cancel')">
						<view class="flex flex-row flex-center nav-item-btn-box">
							<text class="font-big" v-bind:class="{active:currentNav == 'cancel', inactive:currentNav != 'cancel'}">取消订单</text>
						</view>
					</button>
				</view>
			</view>
		</u-sticky>

		<view class="md10 pd10 round-box bg-color-white" id="el-service">
			<u-parse :content="service.description"></u-parse>
		</view>

		<view class="flex flex-col md10 pd10 round-box bg-color-white font-common color-black-light" id="el-attention">
			<view class="flex flex-row flex-left flex-wrap pb10 border-bottom-gray">
				<text class="font-big color-black strong">服务须知</text>
			</view>

			<view class="flex flex-row flex-left flex-wrap pt10" v-if="cfg">
				<u-parse :content="cfg.service_buy_promotion"></u-parse>
			</view>
		</view>

		<view class="flex flex-col md10 pd10 round-box bg-color-white font-common color-black-light" id="el-cancel">
			<view class="flex flex-row flex-left flex-wrap pb10 border-bottom-gray">
				<text class="font-big color-black strong">取消订单</text>
			</view>
			<view class="flex flex-row flex-left flex-wrap pt10" v-if="cfg">
				<u-parse :content="cfg.service_order_cancel_promotion"></u-parse>
			</view>
		</view>

		<view class="empty-box"></view>
		<view class="flex flex-row flex-center bg-color-white bottom-wrapper">
			<button class="invisibility" style="width: 90%;" @click="onBuy">
				<view class="flex flex-row flex-center primary-bg-color btn-round">
					<text class="font-common color-white">立即下单<text v-if="specs.length==0">({{service.sale_price}}元)</text></text>
				</view>
			</button>
		</view>

		<u-popup :round="10" mode="bottom" :show="specsPopupVisible" @close="onCloseSpecsPopupClick">
			<view class="flex flex-col pd10">
				<view class="flex flex-col" v-for="(item, index) in specs" :key="index">
					<view class="flex flex-row flex-left pt10 pb10">
						<text class="font-common strong">{{item.spec.name}}</text>
					</view>

					<view class="flex flex-row flex-left flex-wrap pt10 pb10">
						<text class="flex flex-row flex-center spec-item-price-item"
							v-bind:class="{disable: item2.disable, checked: item2.checked}"
							v-for="(item2, index2) in item.spec_items" :key="index2"
							@click="onSpecPriceItemClick(index, index2, item2.spec_id, item2.id)">{{item2.item}}</text>
					</view>
				</view>

				<view class="flex flex-row flex-center">
					<button class="invisibility w100" :disabled="!specSku || specSku.stock==0" @click="onBuyConfirmClick">
						<view class="flex flex-row flex-center w100 btn-buy buy"
							v-bind:class="{disabled: !specSku || specSku.stock==0}">
							<text class="font-big color-white">立即下单<text v-if="specSku">({{specSku.price}}元)</text></text>
						</view>
					</button>
				</view>
			</view>
		</u-popup>
	</view>

	<!--加载中-->
	<view class="" v-else>
		<u-skeleton :loading="true" :animate="true"></u-skeleton>
	</view>
</template>

<script>
	import HttpUtil from '@/common/utils/http_util';
	import globalUtil from '@/common/global';
	import api from '@/common/api';

	export default {
		data() {
			return {
				STATIC_BASE_URL: api.STATIC_BASE_URL,
				id: 0,
				service: null,
				images: [],
				attrs: [],
				specs: [],
				specSkuList: [],
				specSku: null,

				currentNav: 'service',

				specsPopupVisible: false,
				
				cfg: null
			}
		},
		onLoad(options) {
			this.id = options.id || 0;
			this.getData();
			
			const app = getApp();
			if(app.globalData.siteConfig) {
				this.cfg = app.globalData.siteConfig;
			}
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.cfg = cfg
			});
		},
		onShareAppMessage(options) {

		},
		onShareTimeline() {

		},
		methods: {
			onNavChange(nav) {
				this.currentNav = nav;
				uni.pageScrollTo({
					selector: '#el-' + nav
				});
			},
			onShowSpecsPopupClick() {
				this.specsPopupVisible = true;
			},
			onCloseSpecsPopupClick() {
				this.specsPopupVisible = false;
			},
			onSpecPriceItemClick(specIndex, specItemIndex, specId, specItemId) {
				let goodsSpecs = this.specs;
				let spec = goodsSpecs[specIndex];
				for (let k = 0; k < spec.spec_items.length; k++) {
					let specItem = spec.spec_items[k];
					if (k == specItemIndex) {
						specItem.checked = !specItem.checked;
					} else {
						specItem.checked = false;
					}
					this.$set(spec.spec_items, k, specItem);
				}
				this.$set(this.specs, spec, specIndex);
				
				let checkedSpecItems = [];
				for (let i = 0; i < goodsSpecs.length; i++) {
					for (let k = 0; k < goodsSpecs[i].spec_items.length; k++) {
						if (goodsSpecs[i].spec_items[k].checked) {
							checkedSpecItems.push(goodsSpecs[i].spec_items[k].id);
							break;
						}
					}
				}
				let specSku = null;
				if (checkedSpecItems.length == goodsSpecs.length) {
					let specSkuKey = checkedSpecItems.join('_');
					for (let index = 0; index < this.specSkuList.length; index++) {
						if (this.specSkuList[index].key == specSkuKey) {
							specSku = this.specSkuList[index];
							break;
						}
					}
				}
				this.specSku = specSku;
			},
			onBuy() {
				const app = getApp();
				const userInfo = app.globalData.userInfo;
				if(!userInfo) {
					uni.navigateTo({
						url: '/pages/user/login'
					});
					return;
				}
				if (this.specs.length > 0) {
				    this.onShowSpecsPopupClick();
				} else {
					const items = [{
					  health_service_id: this.id,
					  health_service: null,
					  quantity: 1,
					  spec_sku_key: '',
					  spec_sku_key_name: '',
					  spec_sku: null
					}];
					const app = getApp();
					app.globalData.shopcartItems = items;
					globalUtil.navigator({
					  url: '/pages/order/buy',
					  login: true
					});
				}
			},
			onBuyConfirmClick() {
				if (this.specs.length > 0 && this.specSku == null) {
				    return;
				}
				this.onCloseSpecsPopupClick();
				const items = [{
				  health_service_id: this.id,
				  health_service: null,
				  quantity: 1,
				  spec_sku_key: this.specSku ? this.specSku.key : '',
				  spec_sku_key_name: this.specSku ? this.specSku.key_name : '',
				  spec_sku: this.specSku
				}];
				const app = getApp();
				app.globalData.shopcartItems = items;
				globalUtil.navigator({
				  url: '/pages/order/buy',
				  login: true
				});
			},
			getData: function() {
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_SERVICE_INFO_URL,
					params: {
						id: this.id
					},
					success: (res) => {
						let goodsSpecs = [];
						for (let key in res.data.goods_specs) {
							let items = [];
							for (let kk in res.data.goods_specs[key].spec_items) {
								const item = res.data.goods_specs[key].spec_items[kk];
								item['disable'] = false;
								item['checked'] = false;
								items.push(item);
							}
							goodsSpecs.push({
								spec: res.data.goods_specs[key].spec,
								spec_items: items
							});
						}
						that.service = res.data.goods;
						that.images = res.data.goods_images;
						that.attrs = res.data.goods_attrs;
						that.specs = goodsSpecs;
						that.specSkuList = res.data.goods_spec_sku_list;
						// uni.setNavigationBarTitle({
						// 	title:that.service.name
						// });
					}
				});
			}
		}
	}
</script>

<style>
	.navs, .navs .nav-item, .navs .nav-item .nav-item-btn-box {
		height: 100rpx;
	}

	.navs .nav-item .inactive {
		color: #525151;
	}

	.navs .nav-item .active {
		color: #33b5bd;
	}
	
	.spec-item-price-img {
		height: 200rpx;
		width: 200rpx;
	}

	.spec-item-price-info {
		height: 200rpx;
	}

	.spec-item-price-item {
		background-color: #f7f8fa;
		padding: 20rpx;
		border-radius: 12rpx;
		font-size: 24rpx;
		margin: 0 10rpx 10rpx 0;
	}

	.spec-item-price-item.disable {
		color: rgba(69, 90, 100, 0.6);
	}

	.spec-item-price-item.checked {
		color: rgba(240, 80, 6, 0.6);
		background-color: #fccea4;
	}

	.btn-buy {
		height: 80rpx;
	}

	.btn-buy.shopcart {
		background: linear-gradient(to right, #ffd01e, #ff8917);
	}

	.btn-buy.shopcart.disabled {
		background: linear-gradient(to right, #f3e8be, #f3b377);
	}

	.btn-buy.buy {
		background: linear-gradient(to right, #f8714c, #ee0a24);
	}

	.btn-buy.buy.disabled {
		background: linear-gradient(to right, #f8c4b6, #f88996);
	}
</style>
