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

		<view class="mt30 text-center">
			<text class="font-common color-black-light">版本号：{{systemInfo ? systemInfo.appVersion : ''}}</text>
		</view>

		<view class="flex flex-row flex-center font-common mt30 pt30">
			<vm-navigator :url="termsUrl">
				<text class="primary-color" :decode="true">用户协议</text>
			</vm-navigator>
			<text class="ml10 mr10">|</text>
			<vm-navigator :url="privacyUrl">
				<text class="primary-color" :decode="true">隐私政策</text>
			</vm-navigator>
		</view>
		
		<view class="flex flex-row flex-center font-common mt10" v-if="seeDoctorAssistantPolicyUrl || cityPartnerPolicyUrl">
			<vm-navigator :url="seeDoctorAssistantPolicyUrl">
				<text class="primary-color" :decode="true">陪诊师合作协议</text>
			</vm-navigator>
			<template v-if="cityPartnerPolicyUrl">
				<text class="ml10 mr10">|</text>
				<vm-navigator :url="cityPartnerPolicyUrl">
					<text class="primary-color" :decode="true">城市合伙人合作协议</text>
				</vm-navigator>
			</template>
		</view>
		
		<view class="mt10 text-center">
			<text class="font-small color-black-light">
				Copyright © 2021-{{currentYear}} Vanmai Technology All Rights Reserved.
			</text>
		</view>
	</view>
</template>

<script>
	import api from '@/common/api';
	import util from '@/common/utils/util';
	
	export default {
		data() {
			return {
				STATIC_BASE_URL: api.STATIC_BASE_URL,
				systemInfo: null,
				accountInfo: null,
				termsUrl: '',
				privacyUrl: '',
				seeDoctorAssistantPolicyUrl: '',
				cityPartnerPolicyUrl: '',
				currentYear: ''
			}
		},
		onLoad() {
			this.systemInfo = uni.getSystemInfoSync();
			this.accountInfo = uni.getAccountInfoSync();
			this.currentYear = util.dateFormat(util.nowtime(), 'Y');
			
			const app = getApp();
			if(app.globalData.siteConfig) {
				this.termsUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.terms_conditions_url);
				this.privacyUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.privacy_policy_url);
				this.seeDoctorAssistantPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.see_doctor_assistant_policy);
				if(app.globalData.siteConfig.city_partner_policy) {
					this.cityPartnerPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.city_partner_policy);
				}
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.termsUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.terms_conditions_url);
				this.privacyUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.privacy_policy_url);
				this.seeDoctorAssistantPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.see_doctor_assistant_policy);
				if(cfg.city_partner_policy) {
					this.cityPartnerPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(cfg.city_partner_policy);
				}
			});
		},
		methods: {

		}
	}
</script>

<style>
	page {
		background-color: #ffffff;
	}
</style>
