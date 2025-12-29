<template>
	<view class="flex flex-col">
		<template v-if="dataList.length == 0">
			<view style="margin-top: 200rpx;">
				<u-empty mode="data" iconSize="270" textSize="16" />
			</view>
		</template>
		<template v-else>
			<template v-for="(item, index) in dataList">
				<view class="flex flex-col md10 pd10 round-box bg-color-white" 
					:key="index"
					@click="onItemClick(index)">
					<view class="flex flex-row flex-left pb10 border-bottom-gray">
						<text class="font-big strong">{{item.title}}</text>
					</view>
					
					<view class="flex flex-row flex-left pb10 pt10 border-bottom-gray">
						<text class="font-common color-black-light">{{item.publish_time_str}}</text>
					</view>
					
					<view class="flex flex-row flex-left pt10">
						<text class="font-common color-black-light">{{item.content}}</text>
					</view>
				</view>
			</template>
			
			<u-loadmore 
			        :status="status" 
			        :loading-text="loadingText" 
			        :loadmore-text="loadmoreText" 
			        :nomore-text="nomoreText" 
			    />
		</template>
	</view>
</template>

<script>
	import HttpUtil from '@/common/utils/http_util';
	import util from '@/common/utils/util';
	import api from '@/common/api';
	
	export default {
		data() {
			return {
				currentPage: 1,
				lastPage: 0,
				total: 0,
				pageSize: 10,
				dataList: [],	
				status: 'loadmore',
				loadingText: '努力加载中',
				loadmoreText: '轻轻上拉',
				nomoreText: '已加载全部数据'
			}
		},
		onShow() {
			const app = getApp();
			if(!app.globalData.userInfo) {
				globalUtil.navigator({
					url: '/pages/user/login',
					login: true,
					type: 'redirect'
				});
			}
		},
		onLoad() {
			this.requestData();
		},
		onUnload() {
			uni.$emit('onRefreshMessages');
		},
		onReachBottom() {
			this.requestData();
		},
		methods: {
			onItemClick(index) {
				const item = this.dataList[index];
				if(item.url.weapp) {
					uni.navigateTo({
						url: item.url.weapp
					})
				}
			},
			requestData() {
				const that = this;
				this.status = 'loading';
				uni.showLoading({
					title:'loading...',
					icon: 'none'
				})
				const url = api.MESSAGE_LISTS_URL + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
				HttpUtil.get({
					uri: url,
					success: (res) => {
						uni.hideLoading();
						let data = res.data;
						that.status = 'loadmore';
						if (that.currentPage >= data.last_page) {
							that.status = 'nomore';
						}
						that.currentPage = that.currentPage + 1;
						for (let i = 0; i < data.data.length; i++) {
							data.data[i]['publish_time_str'] = util.dateFormat(data.data[i].publish_time, 'Y-M-D h:m:s');
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

</style>
