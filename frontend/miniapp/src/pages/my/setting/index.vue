<template>
	<view class="pd10">
		<!-- #ifdef MP-WEIXIN -->
		<view class="round-box bg-color-white pt5 pb5">
			<u-cell-group title="账号信息">		
				<u-cell title="手机号" :border="true">
					<view slot="right-icon">
						<button class="invisibility" open-type="getPhoneNumber" @getphonenumber="onGetPhoneNumber">
							<text class="font-small color-blue">
								{{member && member.mobile ? member.mobile : '点击绑定'}}
							</text>
						</button>
					</view>
				</u-cell>
				
				<u-cell title="实名认证" :border="false">
					<view slot="right-icon">
						<button class="invisibility" @click="onApplyRealnameVerifyTap">
							<text class="font-small color-blue">
								{{member && member.realname_auth_status ? '已认证' : '申请认证'}}
							</text>
						</button>
					</view>
				</u-cell>
			</u-cell-group>
		</view>
		<!--  #endif -->
		<view class="round-box bg-color-white pt5 pb5 mt10">
			<u-cell title="收货地址" :border="false" :clickable="false" :isLink="true" arrow-direction="right" name="consignee" @click="onCellItemClick" />
		</view>
			
		<view class="mt10" v-if="userInfo">
			<u-button type="primary" shape="circle" :plain="true" text="退出登录" @click="onLogoutTap"></u-button>
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
				userInfo: null,
				member: null
			}
		},
		onLoad() {
			const app = getApp();
			this.userInfo = app.globalData.userInfo;
			if (this.userInfo) {
				this.getMemberAccount();
			}
			
			uni.$on('onUserLogin', (userInfo) => {
				this.userInfo = userInfo;
				this.getMemberAccount();
			});
			
			uni.$on('onRealnameVerifySuccess', ()=>{
				this.getMemberAccount();
			});
		},
		methods: {
			onCellItemClick(e) {
				const name = e.name;
				if (name == 'consignee') {
					globalUtil.navigator({
						url: '/pages/my/consignee/index',
						login: true
					});
				}
			},
			onApplyRealnameVerifyTap() {
				globalUtil.navigator({
					url: '/pages/my/realname_verify/apply',
					login: true
				});
			},
			getMemberAccount: function() {
				const that = this;
				HttpUtil.get({
					uri: api.MEMBER_ACCOUNT_URL,
					success: (res) => {
						that.member = res.data.member
					}
				});
			},
			onGetPhoneNumber(e) {
				const app = getApp();
				if (!app.globalData.userInfo) {
					globalUtil.navigator({
						url: '/pages/user/login',
					});
					return;
				}
				//#ifdef MP-WEIXIN
				this.decryptWeappPhoneNumber(e);
				//#endif
				
				//#ifndef MP-WEIXIN
				uni.showToast({
					title:'请联系客服',
					icon:'none'
				});
				uni.navigateTo({
					url:'/pages/my/contact_us/contact_us'
				});
				//#endif
			},
			decryptWeappPhoneNumber(e) {
				if (e.detail.errMsg == 'getPhoneNumber:ok') {
					let phoneData = {
						iv: e.detail.iv,
						encrypted_data: e.detail.encryptedData,
						code: e.detail.code || '',
						login_code: ''
					};
					const that = this;
					uni.login({
						provider: 'weixin',
						success: function(res) {
							if (res.errMsg == "login:ok") {
								phoneData.login_code = res.code;
								HttpUtil.post({
									uri: api.WEAPP_BIND_PHONE_NUMBER_URL,
									params: phoneData,
									success: (res) => {
										that.member.mobile = res.data.mobile;
										uni.showToast({
											title: '绑定成功',
											icon: 'none'
										});
										uni.$emit('onMemberPhoneBind');
									},
									fail: (res) => {
										uni.showToast({
											title: res.msg,
											icon: 'none'
										});
									}
								});
							} else {
								uni.showToast({
									title: '发生错误，请重试',
									icon: 'none'
								})
							}
						}
					});
				}
			},
			onLogoutTap() {
				const app = getApp();
				app.globalData.userInfo = null;
				this.userInfo = null;
				this.member = null;
				uni.$emit('onUserLogout');
				try {
					uni.removeStorageSync('user_access_token');
				} catch (e) {
					// error
				}
				uni.showToast({
					title:'退出成功',
					icon: 'none'
				})
			}
		}
	}
</script>

<style>

</style>
