<template>
	<view class="flex flex-col" v-if="userInfo">
		<view style="margin-top: 200rpx;" v-if="dataList.length == 0">
			<u-empty mode="order" iconSize="270" textSize="16" />
		</view>
		
		<template v-else>
			<template v-for="(item, index) in dataList">
				<vm-navigator customClass="flex flex-col mt10 ml10 mr10 round-box bg-color-white font-common color-black-light el-order-item" 
					:url="'/pages/order/detail?sn=' + item.order_sn"
					:login="true"
					:subMsg="true"
					:msgTempIds="msgTempIds"
					:key="index">
					<view class="flex flex-col pd10 header">
						<view class="flex flex-row flex-left">
							<text class="color-white font-biger">{{item.order_item.health_service_name}}</text>
						</view>
						
						<view class="flex flex-row flex-left mt10" v-if="item.order_item.spec_sku_key_name">
							<text class="color-white font-common">{{item.order_item.spec_sku_key_name}}</text>
						</view>
					</view>
					
					<view class="flex flex-row flex-left pd10 border-bottom-gray">
						<text>订单号：{{item.order_sn}}</text>
					</view>
					
					<view class="flex flex-row flex-left pd10 border-bottom-gray">
						<text>订单状态：{{item.order_status_label}}</text>
					</view>
					
					<view class="flex flex-row flex-left pd10 border-bottom-gray">
						<text>下单时间：{{item.create_time_str}}</text>
					</view>
					
					<view class="flex flex-row flex-left pd10">
						<text>就诊医院：{{item.hospital_name}}</text>
					</view>
					
					<view class="flex flex-row flex-left pd10 border-top-gray" v-if="item.hospital_lab">
						<text>就诊科室：{{item.hospital_lab}}</text>
					</view>
				</vm-navigator>
			</template>
			
			<u-loadmore 
			        :status="status" 
			        :loading-text="loadingText" 
			        :loadmore-text="loadmoreText" 
			        :nomore-text="nomoreText" 
			    />
		</template>
	</view>
	
	<view class="flex flex-col flex-center bg-color-white" style="height: 100vh;" v-else>
		<view class="flex flex-col">
			<view class="flex flex-row flex-center">
				<image :src="STATIC_BASE_URL + 'images/jiuyi.jpeg'" mode="heightFix" style="height: 300rpx;"></image>
			</view>
			
			<view class="flex flex-row flex-center mt15">
				<text class="font-common">您还没有登录，请登录后查看订单</text>
			</view>
			
			<vm-navigator customClass="flex flex-row flex-center mt15" url="/pages/user/login">
				<u-button type="primary" shape="circle" :plain="true" text="立即登录"></u-button>
			</vm-navigator>
		</view>
	</view>
</template>

<script>
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';
	import util from '@/common/utils/util';
	
	export default {
		data() {
			return {
				STATIC_BASE_URL: api.STATIC_BASE_URL,
				userInfo: null,
				
				currentPage: 1,
				lastPage: 0,
				total: 0,
				pageSize: 10,
				dataList: [],	
				status: 'loadmore',
				loadingText: '努力加载中',
				loadmoreText: '轻轻上拉',
				nomoreText: '已加载全部数据',
				
				msgTempIds: []
			}
		},
		onLoad() {
			const app = getApp();
			this.userInfo = app.globalData.userInfo;
			if (this.userInfo) {
				this.requestData();
			}
			
			if(app.globalData.siteConfig) {
				this.msgTempIds.push(app.globalData.siteConfig.msg_template.order_status_notice);
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.msgTempIds.push(cfg.msg_template.order_status_notice);
			});
			
			uni.$on('onUserLogin', (userInfo) => {
				this.userInfo = userInfo;
				this.requestData();
			});
			
			uni.$on('onUserLogout', () => {
				this.userInfo = null;
				
				this.currentPage = 1;
				this.lastPage = 0;
				this.total = 0;
				this.dataList = [];	
				this.status = 'loadmore';
			});
			
			uni.$on('onHealthServiceOrderCreated', () => {
				this.currentPage = 1;
				this.lastPage = 0;
				this.total = 0;
				this.dataList = [];	
				this.status = 'loadmore';
				this.requestData();
			});
		},
		onReachBottom() {
			this.requestData();
		},
		onPullDownRefresh() {
			this.currentPage = 1;
			this.lastPage = 0;
			this.total = 0;
			this.dataList = [];	
			this.status = 'loadmore';
			this.requestData();
		},
		methods: {
			requestData() {
				if(this.status == 'nomore') {
					return;
				}
				const that = this;
				this.status = 'loading';
				uni.showLoading({
					title:'loading...',
					icon: 'none'
				});
				const url = api.HEALTH_SERVICE_ORDER_LISTS_URL + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
				HttpUtil.get({
					uri: url,
					success: (res) => {
						uni.hideLoading();
						let data = res.data;
						that.status = 'loadmore';
						if (that.currentPage >= data.last_page) {
							that.status = 'nomore';
						}
						that.currentPage = that.currentPage + 1;
						let dataList = that.dataList;
						for (let i = 0; i < data.data.length; i++) {
							let orderItem = data.data[i].items[0];
							//#ifndef MP-ALIPAY
							orderItem.spec_sku_key_name = orderItem.spec_sku_key_name.replaceAll('\\n', ' ');
							//#endif
							data.data[i]['order_item'] = orderItem;
							data.data[i]['create_time_str'] = util.dateFormat(data.data[i].create_time, 'Y-M-D h:m:s');
							data.data[i]['pay_time_str'] = data.data[i].pay_time > 0 ? util.dateFormat(data.data[i].pay_time, 'Y-M-D h:m:s') : '';
							dataList.push(data.data[i]);
						}
						that.dataList = dataList;
						that.total = data.total;
						that.lastPage = data.last_page;
					},
					fail: (res)=>{
						uni.hideLoading();
						uni.showToast({
							title:res.msg,
							icon:'none'
						});
					}
				});
			}
		}
	}
</script>

<style>
	.el-order-item .header {
		background-image: linear-gradient(to bottom right, #33b5bd, #76e9f0);
		border-top-left-radius: 24rpx;
		border-top-right-radius: 24rpx;
	}
</style>
