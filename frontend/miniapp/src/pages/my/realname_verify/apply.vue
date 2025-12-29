<template>
	<view>
		<u--form labelPosition="left" :labelWidth="160" ref="form-realname-auth">
			<view class="flex flex-col bg-color-white md10 pd10 round-box">
				<u-form-item label="姓名" prop="formData.name" :borderBottom="false">
					<u--input v-model="formData.name" placeholder="请填写姓名" border="bottom"></u--input>
				</u-form-item>
				
				<u-form-item label="身份证号码" prop="formData.cardno" :borderBottom="false">
					<u--input v-model="formData.cardno" placeholder="请填写身份证号码" border="bottom"></u--input>
				</u-form-item>
				
				<view class="flex flex-row flex-left pd10">
					<text class="font-common color-black-light">正面照：</text>
				</view>
				<view class="flex flex-row flex-left pl10 pr10 pb10">
					<image :src="formData.file_url ? formData.file_url : STATIC_BASE_URL + 'images/image.png'" 
							style="height: 240rpx; width: 240rpx;"
							 @click="onUploadFileTap" />
				</view>
				<view class="flex flex-row flex-left pd10">
					<text class="font-common color-gray">*请拍摄与身份证照片一致的正面照</text>
				</view>
			</view>
		</u--form>
		
		<view class="empty-box"></view>
		<view class="flex flex-col flex-center bg-color-white bottom-wrapper">
			<view class="flex flex-row flex-center mt10 w100">
				<button class="invisibility" style="width: 90%;" @click="onSubmit">
					<view class="flex flex-row flex-center primary-bg-color btn-round">
						<text class="font-common color-white">提交申请({{siteConfig ? siteConfig.service_prices.idcard_verify + '元/次' : ''}})</text>
					</view>
				</button>
			</view>
		</view>
	</view>
</template>

<script>
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';
	
	export default {
		data() {
			return {
				STATIC_BASE_URL: api.STATIC_BASE_URL,
				formData: {name: '', cardno: '', file_id: 0, file_url: ''},
				siteConfig: null
			}
		},
		onShow() {
			const app = getApp();
			if (!app.globalData.userInfo) {
				globalUtil.navigator({
					url: '/pages/user/login',
				});
				return;
			}
		},
		onLoad() {
			const app = getApp();
			this.siteConfig = app.globalData.siteConfig;
		},
		methods: {
			onUploadFileTap() {
				const that = this;
				uni.chooseImage({
					count: 1, //默认9
					sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
					sourceType: ['camera'], //从相册选择
					success: function (res2) {
						HttpUtil.uploadFile({
							filePath: res2.tempFilePaths[0],
							uri: api.FILE_UPLOAD_FORM_URL,
							success: (res) => {
								that.formData.file_id = res.data.id;
								that.formData.file_url = res.data.url;
							},
							fail: (res) => {
								uni.showToast({
									title: res.msg,
									icon:'error'
								});
							}
						});
					}
				});

			},
			onSubmit() {
				const that = this;
				if(this.formData.name == '') {
					uni.showToast({
						title: '请填写姓名',
						icon:'none'
					});
					return;
				}
				if(this.formData.cardno == '') {
					uni.showToast({
						title: '请填写身份证号码',
						icon:'none'
					});
					return;
				}
				if(this.formData.file_id == 0) {
					uni.showToast({
						title: '请上传正面照',
						icon:'none'
					});
					return;
				}
				uni.showLoading({
					title:'loading...',
				});
				HttpUtil.post({
					uri: api.REALNAME_VERIFY_TASK_CREATE_URL,
					params: that.formData,
					success: (res) => {
						uni.hideLoading();
						if(res.data.pay_params) {
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
							        that.onPaySuccess(res.data.order_sn, res.data.pay_trade_no);
							    },
							    fail: function (err) {
							        
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
						uni.hideLoading();
						uni.showToast({
							title: res.msg,
							icon:'error'
						});
					},
					compelete: ()=>{
						
					}
				});
			},
			onPaySuccess: function(orderSn, payTradeNo) {
				const that = this;
				HttpUtil.get({
					uri: api.PAY_TRADE_QUERY_URL,
					params: {
						pay_trade_no: payTradeNo
					},
					success: (res) => {
						if(res.data.success == 1) {
							that.queryResult(orderSn);
						} else {
							uni.showToast({
								title: '付款失败，请重试',
								icon:'none'
							});
						}
					},
					fail: (res) => {
						uni.showToast({
							title: '发生错误，请重试',
							icon:'none'
						});
					}
				});
			},
			queryResult: function(sn) {
				const that = this;
				uni.showLoading({
					title:'loading...'
				});
				HttpUtil.get({
					uri: api.REALNAME_VERIFY_TASK_QUERY_URL,
					params: {
						sn: sn
					},
					success: (res) => {
						uni.hideLoading();
						if(res.data.verify_result == 10) {
							uni.showToast({
								title: '认证成功',
								icon:'none'
							});
							uni.$emit('onRealnameVerifySuccess');
							uni.navigateBack();
						} else {
							uni.showToast({
								title: '认证失败，请重试',
								icon:'none'
							});
						}
					},
					fail: (res) => {
						uni.hideLoading();
						uni.showToast({
							title: '发生错误，请重试',
							icon:'none'
						});
					},
					compelete: ()=>{
						
					}
				});
			}
		}
	}
</script>

<style>

</style>
