<template>
	<view class="flex flex-col flex-center contact-page-wrapper">
		<template v-if="siteConfig">
			<view class="flex flex-row flex-center">
				<image :src="STATIC_BASE_URL + 'images/logo.png'" class="logo"></image>
			</view>
			
			<view class="flex flex-col pd20 mt30 round-box primary-bg-color color-white" style="width: 80%;">
				<view class="flex flex-row" v-if="siteConfig.customer_service_tel">
					<view class="flex flex-row flex-center">
						<view class="iconfont icon-dianhua inline color-white" style="font-size: 60rpx;">&#xe8ad;</view>
					</view>
					
					<view class="flex flex-col flex-1 pl20">
						<view class="flex flex-row flex-left">
							<text class="font-big">客服电话</text>
						</view>
						
						<view class="flex flex-row flex-left mt10">
							<text class="font-common" @click="onMakePhoneCallTap(siteConfig.customer_service_tel)">{{siteConfig.customer_service_tel}}</text>
						</view>
					</view>
				</view>
				
				<view class="flex flex-row mt20" v-if="siteConfig.customer_service_wxid">
					<view class="flex flex-row flex-center">
						<view class="iconfont icon-weixin inline color-white" style="font-size: 60rpx;">&#xe658;</view>
					</view>
					
					<view class="flex flex-col flex-1 pl20">
						<view class="flex flex-row flex-left">
							<text class="font-big">客服微信</text>
						</view>
						
						<view class="flex flex-row flex-left mt10">
							<text class="font-common" @click="onShowWxidPopupTap">{{siteConfig.customer_service_wxid}}</text>
						</view>
					</view>
				</view>
				
				<view class="flex flex-row mt20" v-if="siteConfig.service_email">
					<view class="flex flex-row flex-center">
						<view class="iconfont icon-youxiang inline color-white" style="font-size: 60rpx;">&#xe70c;</view>
					</view>
					
					<view class="flex flex-col flex-1 pl20">
						<view class="flex flex-row flex-left">
							<text class="font-big">客服邮箱</text>
						</view>
						
						<view class="flex flex-row flex-left mt10">
							<text class="font-common" @click="onCopyTap(siteConfig.service_email)">{{siteConfig.service_email}}</text>
						</view>
					</view>
				</view>
				
				<!-- #ifdef MP-WEIXIN || MP-KUAISHOU || MP-BAIDU -->
				<view class="flex flex-row mt20">
					<view class="flex flex-row flex-center">
						<view class="iconfont icon-kefu inline color-white" style="font-size: 60rpx;">&#xe605;</view>
					</view>
					
					<view class="flex flex-col flex-1 pl20">
						<view class="flex flex-row flex-left">
							<text class="font-big">在线客服</text>
						</view>
						
						<view class="flex flex-row flex-left mt10">
							<u-button type="primary" size="small" text="点击联系客服" openType="contact"></u-button>
						</view>
					</view>
				</view>
				<!-- #endif -->
			</view>
		</template>
		
		<template v-if="wxidPopupVisible">
			<u-overlay :show="wxidPopupVisible">
				<view class="flex flex-col flex-center" style="height: 100vh;">
					<view class="flex flex-row flex-center round-box pd10 bg-color-white customer-service-wxid-box">
						<image :show-menu-by-longpress="true" :src="STATIC_BASE_URL + 'images/customer_service_wxid.jpeg'"></image>
					</view>
					
					<view class="flex flex-row flex-center mt10">
						<text class="iconfont inline color-white" 
							style="font-size: 60rpx;" 
							@click.stop="wxidPopupVisible = false">&#xe600;</text>
					</view>
				</view>
			</u-overlay>
		</template>
	</view>
</template>

<script>
	import api from '@/common/api';
	
	export default {
		data() {
			return {
				STATIC_BASE_URL: api.STATIC_BASE_URL,
				siteConfig: null,
				wxidPopupVisible: false
			}
		},
		onLoad() {
			const app = getApp();
			if(app.globalData.siteConfig) {
				this.siteConfig = app.globalData.siteConfig;
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.siteConfig = cfg;
			});
		},
		methods: {
			onMakePhoneCallTap(phoneNumber) {
				uni.makePhoneCall({
					phoneNumber: phoneNumber
				});
			},
			onCopyTap(content) {
				uni.setClipboardData({
					data: content,
					success: function () {
						uni.showToast({
							title:'已复制到剪贴板',
							icon:'success'
						})
					}
				});
			},
			onShowWxidPopupTap() {
				this.onCopyTap(this.siteConfig.customer_service_wxid);
				this.wxidPopupVisible = true;
			}
		}
	}
</script>

<style>
	.contact-page-wrapper {
/* 		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0; */
		/* height: 100vh; */
		padding-top: 100rpx;
	}
	.logo {
		height: 190rpx;
		width: 190rpx;
		border-radius: 95rpx;
	}
	
	.customer-service-wxid-box image {
		height: 360rpx;
		width: 360rpx;
	}
</style>
