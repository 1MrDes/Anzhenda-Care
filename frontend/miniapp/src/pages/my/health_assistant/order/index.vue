<template>
	<view>
		<u-sticky>
		    <view class="flex flex-row flex-center bg-color-white tabs">
				<view class="flex flex-row flex-center flex-1 pb15 pt15" 
					v-bind:class="{current: currentTab=='tab1'}"
					@click="onTabItemClick('tab1')">
					<text>抢单</text>
				</view>
				
				<view class="flex flex-row flex-center flex-1 pb15 pt15" 
					v-bind:class="{current: currentTab=='tab2'}"
					@click="onTabItemClick('tab2')">
					<text>我的订单</text>
				</view>
			</view>
		</u-sticky>
		
		<view style="margin-top: 200rpx;" v-if="dataList.length == 0">
			<u-empty mode="order" iconSize="270" textSize="16" />
		</view>
		
		<template v-else>
			<template v-for="(item, index) in dataList">
				<vm-navigator customClass="flex flex-col mt10 ml10 mr10 round-box bg-color-white font-common color-black-light el-order-item" 
					:url="'/pages/my/health_assistant/order/detail?sn=' + item.order_sn"
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
						<text>就诊时间：{{item.clinic_time_str}}</text>
					</view>
					
					<view class="flex flex-row flex-left pd10">
						<text>就诊医院：{{item.hospital_name}}</text>
					</view>
					
					<view class="flex flex-row flex-left pd10 border-top-gray" v-if="item.hospital_lab">
						<text>就诊科室：{{item.hospital_lab}}</text>
					</view>
				</vm-navigator>
			</template>
		</template>
		
		<u-loadmore 
		        :status="status" 
		        :loading-text="loadingText" 
		        :loadmore-text="loadmoreText" 
		        :nomore-text="nomoreText" 
		    />
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
				
				currentPage: 1,
				lastPage: 0,
				total: 0,
				pageSize: 10,
				dataList: [],	
				status: 'loadmore',
				loadingText: '努力加载中',
				loadmoreText: '轻轻上拉',
				nomoreText: '已加载全部数据',
				
				currentTab: 'tab2',
				
				msgTempIds: []
			}
		},
		onShow() {
			const app = getApp();
			if(!app.globalData.userInfo) {
				globalUtil.navigator({
					url: '/pages/user/login',
					login: true,
					type: 'redirect'
				});
			}
		},
		onLoad() {
			const app = getApp();
			if(app.globalData.siteConfig) {
				this.msgTempIds.push(app.globalData.siteConfig.msg_template.order_status_notice);
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.msgTempIds.push(cfg.msg_template.order_status_notice);
			});
			
			this.requestData();
		},
		onReachBottom() {
			this.requestData();
		},
		methods: {
			onTabItemClick(index) {
				this.currentTab = index;
				if(index == 'tab1') {
					globalUtil.navigator({
						url: '/pages/my/health_assistant/index',
						type: 'redirect',
						login: true
					});
				}
			},
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
				const url = api.HEALTH_ASSISTANT_ORDER_MY_ORDERS_URL + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
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
						for (let i = 0; i < data.data.length; i++) {
							let orderItem = data.data[i].items[0];
							orderItem.spec_sku_key_name = orderItem.spec_sku_key_name.replaceAll('\\n', ' ');
							data.data[i]['order_item'] = orderItem;
							data.data[i]['create_time_str'] = util.dateFormat(data.data[i].create_time, 'Y-M-D h:m:s');
							data.data[i]['pay_time_str'] = data.data[i].pay_time > 0 ? util.dateFormat(data.data[i].pay_time, 'Y-M-D h:m:s') : '';
							data.data[i]['clinic_time_str'] = data.data[i].clinic_time > 0 ? util.dateFormat(data.data[i].clinic_time, 'Y-M-D h:m:s') : '';
							that.dataList.push(data.data[i]);
						}
						that.total = data.total;
						that.lastPage = data.last_page;
					},
					fail: ()=>{
						uni.hideLoading();
					}
				});
			}
		}
	}
</script>

<style>
	.tabs .current {
		background-color: #4fced6;
		color: #fff;
	}
	
	.el-order-item .header {
		background-image: linear-gradient(to bottom right, #33b5bd, #76e9f0);
		border-top-left-radius: 24rpx;
		border-top-right-radius: 24rpx;
	}
</style>
