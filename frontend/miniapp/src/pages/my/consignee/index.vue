<template>
	<view>
		<template v-if="dataListLength == 0">
			<view style="margin-top: 200rpx;">
				<u-empty mode="data" iconSize="270" textSize="16" />
			</view>
		</template>
		<template v-else>
			<vm-navigator customClass="flex flex-col ml10 mr10 mt10 pd10 round-box bg-color-white"
				:url="'/pages/my/consignee/form?id=' + item.id" v-for="(item, index) in dataList" :key="index">
				<view class="flex flex-row flex-left">
					<text class="font-big">{{item.region_name + item.address}}</text>
				</view>

				<view class="flex flex-row flex-left mt10">
					<text class="font-common color-black-light">{{item.name + ' ' + item.mobile}}</text>
				</view>
			</vm-navigator>
		</template>

		<view class="empty-box"></view>
		<view class="flex flex-row flex-center bg-color-white bottom-wrapper">
			<vm-navigator customClass="flex flex-row flex-center w100" url="/pages/my/consignee/form">
				<view class="flex flex-row flex-center primary-bg-color btn-round" style="width: 600rpx;">
					<text class="font-common color-white">+ 添加地址</text>
				</view>
			</vm-navigator>
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
				dataList: [],
				dataListLength: 0
			}
		},
		onShow() {
			const app = getApp();
			if (!app.globalData.userInfo) {
				globalUtil.navigator({
					url: '/pages/user/login',
					login: true,
					type: 'redirect'
				});
			}
		},
		onLoad() {
			this.requestData();
			uni.$on('onConsigneerCreate', () => {
				this.requestData();
			});
		},
		methods: {
			requestData() {
				const that = this;
				uni.showLoading({
					title: 'loading...',
					icon: 'none'
				})
				const url = api.CONSIGNEER_ALL_URL;
				HttpUtil.get({
					uri: url,
					success: (res) => {
						uni.hideLoading();
						that.dataList = res.data.consigneers;
						that.dataListLength = res.data.consigneers.length;
					},
					fail: () => {
						uni.hideLoading();
					}
				});
			}
		}
	}
</script>

<style>

</style>
