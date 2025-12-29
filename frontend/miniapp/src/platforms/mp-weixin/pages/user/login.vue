<template>
	<view>
		<image :src="STATIC_BASE_URL + 'images/bg-wave.png'" mode="widthFix" class="w100" />

		<view class="flex flex-row flex-center mt30">
			<image :src="STATIC_BASE_URL + 'images/logo-login.jpg'" mode="heightFix" style="height: 100rpx;" />
		</view>

		<view class="flex flex-row flex-center mt30">
			<text class="font-big color-black-light">
				您的知心健康管家
			</text>
		</view>

		<template v-if="canIUseGetUserProfile">
			<view class="flex flex-row flex-center mt30">
				<button class="invisibility" style="width: 90%;" @click="onGetUserProfile">
					<view class="flex flex-row flex-center primary-bg-color btn-login">
						<text class="font-big color-white">立即登录</text>
					</view>
				</button>
				<!-- <template v-if="canIUseGetUserProfile">
					<button class="invisibility" style="width: 90%;" @click="onGetUserProfile">
						<view class="flex flex-row flex-center primary-bg-color btn-login">
							<text class="font-big color-white">立即登录</text>
						</view>
					</button>
				</template>
			
				<template v-else>
					<button class="invisibility" style="width: 90%;" open-type="getUserInfo" @getuserinfo="getUserInfo">
						<view class="flex flex-row flex-center primary-bg-color btn-login">
							<text class="font-big color-white">立即登录</text>
						</view>
					</button>
				</template> -->
			</view>
		</template>
		<template v-else>
			<view class="flex flex-col mt30">
				<view class="flex flex-row flex-center">
					<button class="invisibility" style="width: 90%;" open-type="chooseAvatar"
						@chooseavatar="onChooseAvatar">
						<image :src="user.avatar_url ? user.avatar_url : STATIC_BASE_URL + 'images/avatar.png'"
							mode="widthFix" class="user-avatar"></image>
					</button>
				</view>
				
				<view class="flex flex-row flex-center mt10">
					<input v-model="user.nick" type="nickname" placeholder="请填写昵称" placeholder-style="font-size:10px;color:#dddddd;" class="el-input" @input="onInput" @blur="onInputBlur" />
				</view>
				
				<view class="flex flex-row flex-center mt10">
					<button class="invisibility" style="width: 90%;" @click="onLogin">
						<view class="flex flex-row flex-center primary-bg-color btn-login">
							<text class="font-big color-white">立即登录</text>
						</view>
					</button>
				</view>
			</view>
		</template>
		
		<view class="flex flex-row flex-center font-common mt30">
			<text>登录即表示您已阅读并同意</text>
			<vm-navigator :url="termsUrl">
				<text class="color-blue" :decode="true">用户协议</text>
			</vm-navigator>
			<text class="ml10 mr10">与</text>
			<vm-navigator :url="privacyUrl">
				<text class="color-blue" :decode="true">隐私政策</text>
			</vm-navigator>
		</view>
	</view>
</template>

<script>
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';
	import util from '@/common/utils/util';

	export default {
		data() {
			return {
				STATIC_BASE_URL: api.STATIC_BASE_URL,
				canIUseGetUserProfile: false,
				user: {
					code: '',
					nick: '',
					avatar_url: ''
				},
				from: '',
				termsUrl: '',
				privacyUrl: ''
			}
		},
		onLoad(options) {
			const version = uni.getSystemInfoSync().SDKVersion;
			if (util.compareVersion(version, '2.27.0') <= 0) {
				this.canIUseGetUserProfile = true;
			}
			if(options.from) {
				this.from = decodeURIComponent(options.from);
			}
			
			const app = getApp();
			if(app.globalData.siteConfig) {
				this.termsUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.terms_conditions_url);
				this.privacyUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.privacy_policy_url);
			}

			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.termsUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.terms_conditions_url);
				this.privacyUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.privacy_policy_url);
			});
		},
		methods: {
			urlTobase64(url, type) {
				const ext = util.fileext(url);
				const that = this;
				uni.getFileSystemManager().readFile({
					filePath: url,
					encoding: 'base64',
					success: res => {
						const base64Img = 'data:image/'+ext+';base64,' + res.data; //不加上这串字符，在页面无法显示
						if(type == 'user-avatar') {
							that.user.avatar_url = base64Img;
						}
					}
				});
			},
			onChooseAvatar(e) {
				const filePath = e.detail.avatarUrl;
				this.urlTobase64(filePath, 'user-avatar');
			},
			onInput(e) {
				this.user.nick = e.detail.value;
			},
			onInputBlur(e) {
				this.user.nick = e.detail.value;
			},
			getUserInfo(e) {
				const that = this;
				that.user.nick = e.detail.userInfo.nickName;
				that.user.avatar_url = e.detail.userInfo.avatarUrl;
				that.onLogin();
			},
			onGetUserProfile() {
				const that = this;
				uni.getUserProfile({
					desc: '用于完善会员资料',
					success: (res) => {
						that.user.nick = res.userInfo.nickName;
						that.user.avatar_url = res.userInfo.avatarUrl;
						that.onLogin();
					}
				});
			},
			onLogin() {
				const that = this;
				if(that.user.nick.length == 0) {
					uni.showToast({
						title: '请填写昵称',
						icon:'none'
					});
					return;
				}
				if(that.user.avatar_url.length == 0) {
					uni.showToast({
						title: '请上传头像',
						icon:'none'
					});
					return;
				}
				uni.login({
					provider: 'weixin',
					success: function(res) {
						if (res.errMsg == "login:ok") {
							that.user.code = res.code;
							uni.showLoading({
								title: '登录中',
								icon: 'loading'
							});
							HttpUtil.post({
								uri: api.WEAPP_LOGIN_AUTH_URL,
								params: that.user,
								success: (res)=>{
									let app = getApp();
									app.globalData.userInfo = res.data.user;
									uni.$emit('onUserLogin', res.data.user);
									
									try {
										uni.setStorageSync('user_access_token', res.data.user.access_token);
									} catch (e) {
										// error
									}
									
									if(that.from) {
										uni.redirectTo({
											url: that.from
										});
									} else {
										uni.showToast({
											title: '登录成功',
											icon:'none'
										});
										uni.navigateBack();
									}
								},
								fail: (res)=>{
									uni.showToast({
										title: res.msg,
										icon:'none'
									});
								},
								complete: () => {
									uni.hideLoading();
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
		}
	}
</script>

<style>
	page {
		background-color: #ffffff;
	}

	.btn-login {
		height: 100rpx;
		border-radius: 50rpx;
	}
	
	.el-input {
		height: 80rpx;
		line-height: 80rpx;
		border-radius: 40rpx;
		width: 80%;
		padding: 0rpx 30rpx;
		border: 1rpx solid #33b5bd;
	}

	.user-avatar {
		height: 120rpx;
		width: 120rpx;
		border-radius: 60rpx;
	}
</style>
