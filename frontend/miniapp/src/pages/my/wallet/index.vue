<template>
	<view>
		<view class="pd10 bg-color-white text-left font-common color-black-light mb10" v-if="member">
			<text>余额：￥{{member.balance}}</text>
			<text class="ml10">可提现金额：￥{{member.withdraw_balance}}</text>
			<text class="ml10">冻结金额：￥{{member.withdrawing_balance}}</text>
		</view>
		
		<u-sticky>
		    <view class="flex flex-row flex-center bg-color-white tabs">
				<view class="flex flex-row flex-center flex-1 pb15 pt15" 
					v-bind:class="{current: currentTab=='tab1'}"
					@click="onTabItemClick('tab1')">
					<text>账户明细</text>
				</view>
				
				<view class="flex flex-row flex-center flex-1 pb15 pt15" 
					v-bind:class="{current: currentTab=='tab2'}"
					@click="onTabItemClick('tab2')">
					<text>提现</text>
				</view>
			</view>
		</u-sticky>
		
		<view style="margin-top: 200rpx;" v-if="dataList.length == 0">
			<u-empty mode="data" iconSize="270" textSize="16" />
		</view>
		
		<template v-else>
			<view class="flex flex-col mt10 ml10 mr10 pd10 bg-color-white font-common color-black-light round-box"
					v-for="(item, index) in dataList"
					:key="index">
				<view class="flex flex-row flex-center pb10 w100">
					<view class="flex flex-row flex-left flex-1">
						<text>{{item.op_label}}:{{item.amount}}</text>
					</view>
					
					<view class="flex flex-row flex-right flex-1">
						<text>余额:{{item.balance}}</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pb10 pt10 border-top-gray w100">
					<view class="flex flex-row flex-left flex-1">
						<text>时间:{{item.time_str}}</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pb10 pt10 border-top-gray w100" v-if="item.remark">
					<view class="flex flex-row flex-left flex-1">
						<text>备注:{{item.remark}}</text>
					</view>
				</view>
			</view>
			
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
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';
	import util from '@/common/utils/util';
	
	export default {
		data() {
			return {
				member: null,
				
				currentTab: 'tab1',
				
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
					type: 'redirect'
				});
			}
		},
		onLoad() {
			this.getMemberAccount();
			this.requestData();
			uni.$on('onWithdrawCashApply', () => {
				this.currentPage = 1;
				this.lastPage = 0;
				this.total = 0;
				this.dataList = [];	
				this.status = 'loadmore';
				this.getMemberAccount();
				this.requestData();
			});
		},
		onReachBottom() {
			this.requestData();
		},
		methods: {
			onTabItemClick(index) {
				this.currentTab = index;
				if(index == 'tab2') {
					globalUtil.navigator({
						url: '/pages/my/withdraw_cash/index',
						type: 'redirect',
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
			requestData() {
				if(this.status == 'nomore') {
					return;
				}
				const that = this;
				this.status = 'loading';
				uni.showLoading({
					title:'loading...',
					icon: 'none'
				});
				const url = api.USER_ACCOUNT_LISTS_URL + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
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
							data.data[i]['time_str'] = util.dateFormat(data.data[i].dateline, 'Y-M-D h:m:s');
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
	.tabs .current {
		background-color: #4fced6;
		color: #fff;
	}
</style>
