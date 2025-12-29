<template>
	<view class="pb20">
		<!-- <view class="status-bar"></view> -->
		<template v-for="(item, index) in dataList">
			<vm-navigator customClass="flex flex-row flex-center ml10 mr10 mb10 pd30 bg-color-white list-item" 
			:url="'/pages/health_service/detail?id=' + item.id"
			:key="index">
				<view class="flex flex-row flex-center">
					<image :src="item.default_image_url" class="img" />
				</view>
				<view class="flex flex-1 flex-col pl20 pr20">
					<view class="flex flex-row flex-left">
						<text class="strong font-big color-black">{{item.name}}</text>
					</view>
					
					<view class="flex flex-row flex-left mt10">
						<text class="font-common color-black-light">{{item.subheading}}</text>
					</view>
				</view>
				<view class="flex flex-row flex-center">
					
				</view>
			</vm-navigator>
		</template>
	</view>
</template>

<script>
	import HttpUtil from '@/common/utils/http_util';
	import api from '@/common/api';

	export default {
		data() {
			return {
				currentPage: 1,
				lastPage: 0,
				total: 0,
				pageSize: 10,
				loading: false,
				finished: false,
				dataList: []
			}
		},
		onLoad() {
			this.requestData();
		},
		onReachBottom() {
			this.requestData();
		},
		onShareAppMessage(options) {
			
		},
		onShareTimeline() {
			
		},
		methods: {
			requestData() {
				const that = this;
				if(that.finished) {
					return;
				}
				this.loading = true;
				uni.showLoading({
					title:'loading...',
					icon: 'none'
				})
				const url = api.HEALTH_SERVICE_LISTS_URL + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
				HttpUtil.get({
					uri: url,
					success: (res) => {
						uni.hideLoading();
						let data = res.data;
						that.loading = false;
						if (that.currentPage >= data.last_page) {
							that.finished = true;
						}
						that.currentPage = that.currentPage + 1;
						for (let i = 0; i < data.data.length; i++) {
							that.dataList.push(data.data[i]);
						}
						that.total = data.total;
						that.lastPage = data.last_page;
					},
					fail: ()=>{
						uni.hideLoading();
					}
				});
			}
		}
	}
</script>

<style>
	page {
		background-image: linear-gradient(#33b5bd, #F8F8F8);
	}
	.list-item {
		border-radius: 30rpx;
	}
	.list-item .img {
		height: 80rpx;
		width: 80rpx;
		border-radius: 40rpx;
	}
</style>
