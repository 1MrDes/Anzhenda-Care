<template>
	<view>
		<view class="flex flex-col flex-centerH color-white">
			<view class="flex flex-col color-white relative">
				<image :src="STATIC_BASE_URL + 'images/banner-my.jpg'"
						class="w100" 
						mode="widthFix" />
				<view class="flex flex-col flex-center" style="position: absolute; left: 0; right: 0; top: 0; bottom: 0;">
					<template v-if="userInfo">
						<view class="flex flex-row flex-centerV flex-left w100">
							<view class="flex flex-row flex-center pl10">
								<image :src="userInfo.avatar_url ? userInfo.avatar_url : STATIC_BASE_URL + 'images/avatar.png'"
									class="avatar" />
							</view>
					
							<view class="flex flex-1 flex-row flex-left pl10 font-common">
								<text class="color-white">{{userInfo.nick}}</text>
							</view>
						</view>
					</template>
					<template v-else>
						<view class="flex flex-row flex-centerV flex-left w100">
							<view class="flex flex-row flex-center pl10">
								<vm-navigator hover-class="none" open-type="redirect" url="/pages/user/login">
									<image :src="STATIC_BASE_URL + 'images/avatar.png'" class="avatar" />
								</vm-navigator>
							</view>
					
							<view class="flex flex-1 flex-row flex-left pl10 font-common color-white">
								<vm-navigator hover-class="none" open-type="redirect" url="/pages/user/login">立即登录
								</vm-navigator>
							</view>
						</view>
					</template>
				</view>
			</view>
		</view>

		<view class="pd10">
			<view class="round-box bg-color-white pt5 pb5">
				<u-cell title="消息" :border="false" :clickable="false" :isLink="true" arrow-direction="right"
					name="message" @click="onCellItemClick">
					<view slot="right-icon">
						<template v-if="message.unread > 0">
							<u-badge type="warning" max="99" :value="message.unread"></u-badge>
						</template>
					</view>
				</u-cell>
			</view>
			
			<view class="round-box bg-color-white pt5 pb5 mt10">
				<u-cell title="钱包" :border="false" :clickable="false" :isLink="true" arrow-direction="right"
					name="wallet" @click="onCellItemClick">
				</u-cell>
			</view>

			<view class="round-box bg-color-white pt5 pb5 mt10" v-if="seeDoctorAssistantPolicyUrl || cityPartnerPolicyUrl">
				<u-cell title="陪诊师" :border="cityPartnerPolicyUrl ? true : false" :clickable="false" :isLink="true" arrow-direction="right"
					name="health_assistant" @click="onCellItemClick"></u-cell>
				<u-cell title="城市合伙人" :border="false" :clickable="false" :isLink="true" arrow-direction="right"
						:url="cityPartnerPolicyUrl" v-if="cityPartnerPolicyUrl"></u-cell>
			</view>
			
			<view class="round-box bg-color-white pt5 pb5 mt10">
				<u-cell title="设置" :border="false" :clickable="false" :isLink="true" arrow-direction="right"
					url="/pages/my/setting/index"></u-cell>
			</view>

			<view class="round-box bg-color-white pt5 pb5 mt10">
				<u-cell title="联系我们" :clickable="false" :isLink="true" arrow-direction="right"
					url="/pages/my/contact_us/contact_us"></u-cell>

				<!-- #ifdef MP-WEIXIN -->
				<u-cell title="意见反馈" :clickable="false" :isLink="true" arrow-direction="right">
					<u-button type="primary" size="mini" openType="feedback" slot="right-icon">进入</u-button>
				</u-cell>
				<!-- #endif -->

				<!-- #ifndef MP-WEIXIN -->
				<u-cell title="意见反馈" :clickable="false" :isLink="true" arrow-direction="right" name="feedback"
					@click="onCellItemClick"></u-cell>
				<!-- #endif -->

				<u-cell title="关于我们" :border="false" :clickable="false" :isLink="true" arrow-direction="right"
					url="/pages/my/about/about"></u-cell>
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
				userInfo: null,
				member: null,
				message: {
					unread: 0
				},
				seeDoctorAssistantPolicyUrl: '',
				cityPartnerPolicyUrl: ''
			}
		},
		onShow() {
			const app = getApp();
			this.userInfo = app.globalData.userInfo;
			if (this.userInfo) {
				this.getMemberAccount();
				this.getUnreadMessage();
			}
		},
		onLoad() {
			const app = getApp();
			this.userInfo = app.globalData.userInfo;
			if (this.userInfo) {
				this.getMemberAccount();
				this.getUnreadMessage();
			}

			if(app.globalData.siteConfig) {
				if(app.globalData.siteConfig.see_doctor_assistant_policy) {
					this.seeDoctorAssistantPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.see_doctor_assistant_policy);
				}
				if(app.globalData.siteConfig.city_partner_policy) {
					this.cityPartnerPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.city_partner_policy);
				}
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				if(cfg.see_doctor_assistant_policy) {
					this.seeDoctorAssistantPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.see_doctor_assistant_policy);
				}
				if(cfg.city_partner_policy) {
					this.cityPartnerPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.city_partner_policy);
				}
			});
			
			uni.$on('onUserLogin', (userInfo) => {
				this.userInfo = userInfo;
				this.getMemberAccount();
				this.getUnreadMessage();
			});

			uni.$on('onUserLogout', () => {
				this.userInfo = null;
				this.member = null;
				this.message.unread = 0;
			});
			
			uni.$on('onRefreshMessages', () => {
				this.getUnreadMessage();
			});
		},
		methods: {
			onCellItemClick(e) {
				const name = e.name;
				if (name == 'message') {
					globalUtil.navigator({
						url: '/pages/my/message/index',
						login: true
					});
				} else if (name == 'feedback') {
					const app = getApp();
					const url = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.feedback_url);
					globalUtil.navigator({
						url: url
					});
				} else if(name == 'wallet') {
					globalUtil.navigator({
						url: '/pages/my/wallet/index',
						login: true
					});
				} else if(name == 'health_assistant') {
					globalUtil.navigator({
						url: '/pages/my/health_assistant/index',
						login: true
					});
				}
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
			getUnreadMessage: function() {
				const that = this;
				HttpUtil.get({
					uri: api.MESSAGE_UNREAD_URL,
					success: (res) => {
						that.message.unread = res.data.count
					}
				});
			}
		}
	}
</script>

<style>
	/* 	page {
		background-image: linear-gradient(#33b5bd, #F8F8F8);
	} */

	.avatar {
		height: 120rpx;
		width: 120rpx;
		border-radius: 60rpx;
	}
</style>
