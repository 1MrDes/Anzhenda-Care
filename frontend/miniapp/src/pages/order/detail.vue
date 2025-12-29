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
				<text>订单状态：{{order.order_status_label}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-bottom-gray">
				<text>订单金额：{{order.order_money}}</text>
			</view>

			<view class="flex flex-row flex-left pd10 border-bottom-gray">
				<text>下单时间：{{order.create_time_str}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-bottom-gray" v-if="order.pay_time > 0">
				<text>付款时间：{{order.pay_time_str}}</text>
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
				<text>联系电话：{{order.contact_tel}}</text>
			</view>
			
			<view class="flex flex-row flex-left pd10 border-top-gray" v-if="order.remark">
				<text>备注：{{order.remark}}</text>
			</view>

			<view class="flex flex-row flex-left pd10 border-top-gray" v-if="order.health_assistant">
				<text @click="onMakePhoneCallTap(order.health_assistant.mobile)">陪诊师：{{order.health_assistant.nick}}(<text class="primary-color">{{order.health_assistant.mobile}}</text>)</text>
			</view>

			<template v-if="order.shipping_type==HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS && order.shipping_time>0">
				<view class="flex flex-row flex-left pd10 border-top-gray">
					<text>快递订单号：{{order.express_sn}}({{order.express_name}})</text>
					<button class="invisibility ml10" @click="onQueryExpress">
						<text class="primary-color font-common">查询物流进度</text>
					</button>
				</view>
				<view class="flex flex-row flex-left pd10 border-top-gray">
					<text>发货时间：{{order.shipping_time_str}}</text>
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
	
		<view class="empty-box"></view>
		<view class="flex flex-row flex-center bg-color-white bottom-wrapper">
			<view class="flex flex-row flex-center flex-1" v-if="order.payment_status == 0 && (order.order_status==HEALTH_ASSISTANT_ORDER_STATUS_UNCONFIRMED || order.order_status==HEALTH_ASSISTANT_ORDER_STATUS_CONFIRMED)">
				<button class="invisibility" style="width: 90%;" @click="onPayTap">
					<view class="flex flex-row flex-center primary-bg-color btn-round">
						<text class="font-common color-white">立即付款</text>
					</view>
				</button>
			</view>
			
			<view class="flex flex-row flex-center flex-1" v-if="order.order_status == HEALTH_ASSISTANT_ORDER_STATUS_ACTING">
				<button class="invisibility" style="width: 90%;" @click="onFinishTap">
					<view class="flex flex-row flex-center primary-bg-color btn-round">
						<text class="font-common color-white">确认完成</text>
					</view>
				</button>
			</view>
			
			<view class="flex flex-row flex-center flex-1">				
				<vm-navigator customClass="flex flex-row flex-center w100" 
					url="/pages/my/contact_us/contact_us">
					<view class="flex flex-row flex-center primary-bg-color btn-round" style="width: 180rpx;">
						<text class="font-common color-white">联系客服</text>
					</view>
				</vm-navigator>
			</view>
		</view>
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
				HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS: constants.HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS,
				HEALTH_ASSISTANT_ORDER_STATUS_UNCONFIRMED: constants.HEALTH_ASSISTANT_ORDER_STATUS_UNCONFIRMED,
				HEALTH_ASSISTANT_ORDER_STATUS_CONFIRMED: constants.HEALTH_ASSISTANT_ORDER_STATUS_CONFIRMED,
				HEALTH_ASSISTANT_ORDER_STATUS_ACTING: constants.HEALTH_ASSISTANT_ORDER_STATUS_ACTING,
				
				payTradeNo: '',
				orderSn: '',
				order: null,
				orderItem: null,
				
				msgTempIds: []
			}
		},
		onShow() {
			//#ifdef MP-ALIPAY
			if(this.payTradeNo) {
				this.queryPayResult(this.orderSn, this.payTradeNo);
			}
			//#endif
		},
		onLoad(options) {
			this.orderSn = options.sn || '';
			this.getOrder();
			
			const app = getApp();
			if(app.globalData.siteConfig) {
				this.msgTempIds.push(app.globalData.siteConfig.msg_template.order_status_notice);
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.msgTempIds.push(cfg.msg_template.order_status_notice);
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
			onQueryExpress() {
				const expressSn = this.order.express_sn;
				//#ifdef MP-WEIXIN
				uni.navigateToMiniProgram({
				  appId: 'wx6885acbedba59c14',
				  path: 'pages/result/result?nu='+expressSn+'&com=&querysource=third_xcx',
				  success(res) {
				    // 打开成功
				  }
				});
				//#endif
				//#ifndef MP-WEIXIN
				uni.setClipboardData({
					data: expressSn,
					success: function () {
						uni.showToast({
							title:'快单号已复制',
							icon:'success'
						})
					}
				});
				//#endif
			},
			onPayTap() {
				const that = this;
				HttpUtil.get({
					uri: api.PAY_TRADE_CREATE_URL,
					params: {
						order_type: 'HealthServiceOrder',
						order_sn: this.orderSn
					},
					success: (res) => {
						if(res.data.pay_params) {
							that.payTradeNo = res.data.pay_trade_no;
							let payment = {
							    provider: '',
							    orderInfo: '',
								timeStamp: '',
								nonceStr: '',
								package: '',
								signType: '',
								paySign: '',
								service: 3,
							    success: function (res2) {
									that.queryPayResult(res.data.order_sn, res.data.pay_trade_no);
							    },
							    fail: function (err) {
							        console.log(err);
							    }
							};
							//#ifdef MP-WEIXIN || MP-TOUTIAO
							payment.provider = 'wxpay';
							const payParams = JSON.parse(res.data.pay_params);
							payment.timeStamp = payParams.timeStamp;
							payment.nonceStr = payParams.nonceStr;
							payment.package = payParams.package;
							payment.signType = payParams.signType;
							payment.paySign = payParams.paySign;
							//#endif
							
							//#ifdef MP-ALIPAY
							payment.provider = 'alipay';
							payment.orderInfo = res.data.pay_params.trade_no;
							//#endif
							
							//#ifdef MP-BAIDU
							payment.provider = 'baidu';
							//#endif
							
							//#ifdef MP-TOUTIAO
							
							//#endif
							
							uni.requestPayment(payment);
						}
					},
					fail: (res) => {
						uni.showToast({
							title:res.msg,
							icon:'none'
						});
					}
				})
			},
			queryPayResult: function(orderSn, payTradeNo) {
				const that = this;
				HttpUtil.get({
					uri: api.PAY_TRADE_QUERY_URL,
					params: {
						pay_trade_no: payTradeNo
					},
					success: (res) => {
						that.getOrder();
						const systemInfo = uni.getSystemInfoSync();
						if(systemInfo.uniPlatform == 'mp-weixin') {
							uni.requestSubscribeMessage({
							  tmplIds: that.msgTempIds,
							  complete: () => {

							  }
							});
						}
					},
					fail: (res) => {
						
					}
				});
			},
			onFinishTap() {
				const that = this;
				uni.showModal({
					title: '提示',
					content: '您确定该订单已完成服务了吗？',
					success: function (res2) {
						if (res2.confirm) {
							HttpUtil.get({
								uri: api.HEALTH_SERVICE_ORDER_OP_URL,
								params: {
									order_sn: that.orderSn,
									op: 'finish',
									act: ''
								},
								success: (res) => {
									that.getOrder();
								},
								fail: (res) => {
									
								}
							});
						} else if (res2.cancel) {
							console.log('用户点击取消');
						}
					}
				});
			},
			getOrder: function() {
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_SERVICE_ORDER_DETAIL_URL,
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
						that.order['shipping_time_str'] = that.order.shipping_time > 0 ? util.dateFormat(that.order.shipping_time, 'Y-M-D h:m:s') : '';
					},
					fail: (res) => {
						uni.showToast({
							title: '发生错误，请重试',
							icon:'none'
						});
					}
				});
			}
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
