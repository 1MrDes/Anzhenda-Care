<style lang="scss">
	/* 注意要写在第一行，同时给style标签加入lang="scss"属性 */
	@import "uview-ui/index.scss";
</style>
<script>
	import HttpUtil from "@/common/utils/http_util.js";
	import api from "@/common/api.js";

	export default {
		globalData: {
			launchScene: 1001,
			miniapp_id: '',
			openid: '',
			userInfo: null,
			siteConfig: null,
			shopcartItems: null
		},
		onLaunch: function() {
			console.log('App Launch');
			const accountInfo = uni.getAccountInfoSync();
			this.globalData.miniapp_id = accountInfo.miniProgram.appId;
			this.getSiteConfig();
			this.loginByToken();
			this.checkUpdate();
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		},
		methods: {
			checkUpdate: function() {
				const updateManager = uni.getUpdateManager();
				
				updateManager.onCheckForUpdate(function (res) {
				  // 请求完新版本信息的回调
				  console.log(res.hasUpdate);
				});
				
				updateManager.onUpdateReady(function (res) {
				  uni.showModal({
				    title: '更新提示',
				    content: '新版本已经准备好，是否重启应用？',
				    success(res) {
				      if (res.confirm) {
				        // 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
				        updateManager.applyUpdate();
				      }
				    }
				  });
				
				});
				
				updateManager.onUpdateFailed(function (res) {
				  // 新的版本下载失败
				});
			},
			getSiteConfig: function() {
				const that = this;
				HttpUtil.get({
					uri: api.SITE_CONFIG_URL,
					success: (res) => {
						that.globalData.siteConfig = res.data.site_config;
						uni.$emit('onSiteConfigLoaded', res.data.site_config);
					}
				});
			},
			loginByToken: function() {
				try {
					const accessToken = uni.getStorageSync('user_access_token');
					if (accessToken) {
						const that = this;
						HttpUtil.get({
							uri: api.USER_LOGIN_BY_TOKEN_URL,
							params: {
								access_token: accessToken
							},
							success: (res) => {
								that.globalData.userInfo = res.data.user;
								uni.$emit('onUserLogin', res.data.user);
							}
						});
					}
				} catch (e) {
					// error
				}
			}
		}
	}
</script>

<style>
	/*每个页面公共css */
	@import './common/style/common.css';
	@import './common/style/style.css';
	/* @import './common/style/iconfont.css'; */
	@font-face {
		font-family: 'iconfont';
		src: url('https://at.alicdn.com/t/c/font_3617724_8652di2rh6e.ttf?t=1663236178042') format('truetype');
	}
	.iconfont {
		font-family: iconfont;
	}
	
	page{
		background-color: #f8f8f8;
	}
</style>
