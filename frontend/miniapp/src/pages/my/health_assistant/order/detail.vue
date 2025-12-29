<template>
	<view v-if="order">
		<view class="flex flex-col md10 round-box bg-color-white font-common color-black-light el-order">
			<view class="flex flex-col pd10 header">
				<view class="flex flex-row flex-left">
					<text class="color-white font-biger">{{orderItem.health_service_name}}</text>
				</view>
				
				<view class="flex flex-row flex-left mt10" v-if="orderItem.spec_sku_key_name">
					<text class="color-white font-common">{{orderItem.spec_sku_key_name}}</text>
				</view>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-bottom-gray">
				<text>订单号：{{order.order_sn}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-bottom-gray">
				<text>订单金额：{{order.order_money}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-bottom-gray">
				<text>订单状态：{{order.order_status_label}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-bottom-gray">
				<text>下单时间：{{order.create_time_str}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-bottom-gray" v-if="order.clinic_time > 0">
				<text>就诊时间：{{order.clinic_time_str}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10">
				<text>就诊医院：{{order.hospital_name}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-top-gray" v-if="order.hospital_lab">
				<text>就诊科室：{{order.hospital_lab}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-top-gray">
				<text>联系人：{{order.contact_name}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-top-gray">
				<text @click="onMakePhoneCallTap(order.contact_tel)">联系电话：<text class="primary-color">{{order.contact_tel}}</text></text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-top-gray" v-if="order.remark">
				<text>备注：{{order.remark}}</text>
			</view>
			
			<template v-if="order.shipping_type==HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS && order.express_sn">
				<view class="flex flex-row flex-left pd10 border-top-gray" v-if="order.express_name">
					<text>快递公司：{{order.express_name}}</text>
				</view>
				<view class="flex flex-row flex-left pd10 border-top-gray" v-if="order.express_sn">
					<text>快递单号：{{order.express_sn}}</text>
				</view>
			</template>
		</view>
	
		<template v-if="order.shipping_type==HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS">
			<view class="flex flex-col bg-color-white md10 round-box el-order">
				<view class="flex flex-col pd10 header">
					<view class="flex flex-row flex-left">
						<text class="color-white font-common">收货地址</text>
					</view>
				</view>
				
				<view class="flex flex-col pd10">
					<view class="flex flex-row flex-left flex-centerV">
						<text class="font-big strong">{{order.consignee}}</text>
						<text class="font-common color-black-light ml10">{{order.mobile}}</text>
					</view>
					
					<view class="flex flex-row flex-left flex-centerV mt10">
						<text class="font-common color-black-light">{{order.region_name + ' ' + order.address}}</text>
					</view>
				</view>
			</view>
		</template>
		
		<template v-if="HEALTH_ASSISTANT_ORDER_STATUS_SHIPPED == order.order_status || HEALTH_ASSISTANT_ORDER_STATUS_ACTING == order.order_status">
			<view class="empty-box"></view>
			<view class="flex flex-row flex-center bg-color-white bottom-wrapper">
				<view class="flex flex-row flex-center flex-1" v-if="HEALTH_ASSISTANT_ORDER_STATUS_SHIPPED == order.order_status">
					<button class="invisibility" style="width: 90%;" @click="onOpTap('acting')">
						<view class="flex flex-row flex-center primary-bg-color btn-round">
							<text class="font-common color-white">开始服务</text>
						</view>
					</button>
				</view>
				
				<view class="flex flex-row flex-center flex-1" v-if="order.shipping_type==HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS && HEALTH_ASSISTANT_ORDER_STATUS_ACTING == order.order_status && order.express_sn == ''">
					<vm-navigator customClass="flex flex-row flex-center primary-bg-color btn-round" 
								style="width:200rpx;"
								:url="'/pages/my/health_assistant/order/ship?order_sn=' + orderSn">
						<text class="font-common color-white">填写快递单</text>
					</vm-navigator>
				</view>
			</view>
		</template>
	</view>
</template>

<script>
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';
	import util from '@/common/utils/util';
	import constants from '@/common/constants';
	
	export default {
		data() {
			return {
				HEALTH_ASSISTANT_ORDER_STATUS_SHIPPED: constants.HEALTH_ASSISTANT_ORDER_STATUS_SHIPPED,
				HEALTH_ASSISTANT_ORDER_STATUS_ACTING: constants.HEALTH_ASSISTANT_ORDER_STATUS_ACTING,
				HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_OFFLINE: constants.HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_OFFLINE,
				HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS: constants.HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS,
				
				orderSn: '',
				order: null,
				orderItem: null,
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
		onLoad(options) {
			this.orderSn = options.sn || '';
			this.getOrder();
			uni.$on('onHealthAssistantOrderShip', ()=>{
				this.getOrder();
			});
		},
		onPullDownRefresh() {
			this.getOrder();
		},
		methods: {
			onMakePhoneCallTap(phoneNumber) {
				uni.makePhoneCall({
					phoneNumber: phoneNumber
				});
			},
			onOpTap(op) {
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_ASSISTANT_ORDER_OP_URL,
					params: {
						order_sn: that.orderSn,
						op: op
					},
					success: (res) => {
						that.getOrder();
					},
					fail: (res) => {
						uni.showToast({
							title: '发生错误，请重试',
							icon:'none'
						});
					}
				});
			},
			getOrder: function() {
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_ASSISTANT_ORDER_DETAIL_URL,
					params: {
						order_sn: that.orderSn
					},
					success: (res) => {
						that.order = res.data.order;
						that.orderItem = res.data.items[0];
						that.orderItem.spec_sku_key_name = that.orderItem.spec_sku_key_name.replaceAll('\\n', ' ');
						
						that.order['create_time_str'] = util.dateFormat(that.order.create_time, 'Y-M-D h:m:s');
						that.order['pay_time_str'] = that.order.pay_time > 0 ? util.dateFormat(that.order.pay_time, 'Y-M-D h:m:s') : '';
						that.order['clinic_time_str'] = that.order.clinic_time > 0 ? util.dateFormat(that.order.clinic_time, 'Y-M-D h:m:s') : '';
					},
					fail: (res) => {
						uni.showToast({
							title: '发生错误，请重试',
							icon:'none'
						});
					}
				});
			},
			
		}
	}
</script>

<style>
	.el-order .header {
		background-image: linear-gradient(to bottom right, #33b5bd, #76e9f0);
		border-top-left-radius: 24rpx;
		border-top-right-radius: 24rpx;
	}
</style>
