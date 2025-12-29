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
						<text>提现金额:{{item.amount}}</text>
					</view>
					
					<view class="flex flex-row flex-right flex-1">
						<text>状态:{{item.status_label}}</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pb10 pt10 border-top-gray w100">
					<view class="flex flex-row flex-left flex-1">
						<text>支付宝账户名:{{item.alipay_name}}</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pb10 pt10 border-top-gray w100">
					<view class="flex flex-row flex-left flex-1">
						<text>支付宝账号:{{item.alipay_email}}</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pb10 pt10 border-top-gray w100">
					<view class="flex flex-row flex-left flex-1">
						<text>申请时间:{{item.apply_time_str}}</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pb10 pt10 border-top-gray w100" v-if="item.pay_time > 0">
					<view class="flex flex-row flex-left flex-1">
						<text>打款时间:{{item.pay_time_str}}</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pb10 pt10 border-top-gray w100" v-if="item.pay_time > 0">
					<view class="flex flex-row flex-left flex-1">
						<text>打款平台:{{item.pay_type_label}}</text>
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
		
		<view class="empty-box"></view>
		<view class="flex flex-col flex-center bg-color-white bottom-wrapper">
			<view class="flex flex-row flex-center w100">
				<button class="invisibility" style="width: 90%;" @click="onApplyClick">
					<view class="flex flex-row flex-center primary-bg-color btn-round">
						<text class="font-common color-white">申请提现</text>
					</view>
				</button>
			</view>
		</view>
		
		<template v-if="formModelVisible">
			<u-modal :show="formModelVisible" 
					@confirm="onFormConfirm"
					@cancel="formModelVisible=false"
					@close="formModelVisible=false"
					ref="uModal" 
					title="申请提现"
					:closeOnClickOverlay="true"
					:showCancelButton="true">
				<u--form labelPosition="left" :labelWidth="140" ref="ref-form">
					<u-form-item label="支付宝账户名" prop="formData.alipay_name" :required="true" :borderBottom="false">
						<u--input v-model="formData.alipay_name" placeholder="请填写支付宝账户名" border="bottom"></u--input>
					</u-form-item>
					
					<u-form-item label="支付宝账号" prop="formData.alipay_email" :required="true" :borderBottom="false">
						<u--input v-model="formData.alipay_email" placeholder="请填写支付宝账号" border="bottom"></u--input>
					</u-form-item>
					
					<u-form-item label="提现金额" prop="formData.amount" :required="true" :borderBottom="false">
						<u--input v-model="formData.amount" placeholder="请填写提现金额" border="bottom"></u--input>
					</u-form-item>
				</u--form>
			</u-modal>
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
				
				currentTab: 'tab2',
				
				currentPage: 1,
				lastPage: 0,
				total: 0,
				pageSize: 10,
				dataList: [],	
				status: 'loadmore',
				loadingText: '努力加载中',
				loadmoreText: '轻轻上拉',
				nomoreText: '已加载全部数据',
				
				formModelVisible: false,
				
				formData: {
					alipay_name: '',
					alipay_email: '',
					amount: ''
				},
				
				msgTempIds: []
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
			const app = getApp();
			if(app.globalData.siteConfig) {
				this.msgTempIds.push(app.globalData.siteConfig.msg_template.withdraw_progress_notice);
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.msgTempIds.push(cfg.msg_template.withdraw_progress_notice);
			});
			
			this.getMemberAccount();
			this.requestData();
		},
		onReachBottom() {
			this.requestData();
		},
		methods: {
			onApplyClick() {
				this.formModelVisible = true;
			},
			onFormConfirm() {
				
				if(this.formData.alipay_name == '') {
					uni.showToast({
						title:'请填写支付宝账户名',
						icon:'none'
					});
					return;
				}
				if(this.formData.alipay_email == '') {
					uni.showToast({
						title:'请填写支付宝账号',
						icon:'none'
					});
					return;
				}
				if(this.formData.amount == '') {
					uni.showToast({
						title:'请填写提现金额',
						icon:'none'
					});
					return;
				}
				
				if(parseFloat(this.formData.amount) > parseFloat(this.member.withdraw_balance)) {
					uni.showToast({
						title:'您当前可提现金额为：' + this.member.withdraw_balance,
						icon:'none'
					});
					return;
				}
				
				this.formModelVisible = false;
				
				const that = this;
				uni.showLoading({
					title:'loading...',
					icon: 'none'
				});
				const url = api.USER_WITHDRAW_CASH_APPLY_URL;
				HttpUtil.post({
					uri: url,
					params: this.formData,
					success: (res) => {
						uni.hideLoading();
						uni.showToast({
							title:'申请成功，请等待审核',
							icon:'none'
						});
						that.currentPage = 1;
						that.lastPage = 0;
						that.total = 0;
						that.dataList = [];	
						that.status = 'loadmore';
						that.requestData();
						that.getMemberAccount();
						uni.$emit('onWithdrawCashApply');
						
						const systemInfo = uni.getSystemInfoSync();
						if(systemInfo.uniPlatform == 'mp-weixin') {
							uni.requestSubscribeMessage({
							  tmplIds: that.msgTempIds,
							  complete: () => {

							  }
							});
						}
					},
					fail: (res) => {
						uni.hideLoading();
						uni.showToast({
							title:res.msg,
							icon:'none'
						});
					},
					complete: ()=>{
						
					}
				});
			},
			onTabItemClick(index) {
				this.currentTab = index;
				if(index == 'tab1') {
					globalUtil.navigator({
						url: '/pages/my/wallet/index',
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
				const url = api.USER_WITHDRAW_CASH_LISTS_URL + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
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
							data.data[i]['apply_time_str'] = util.dateFormat(data.data[i].apply_time, 'Y-M-D h:m:s');
							data.data[i]['pay_time_str'] = data.data[i].pay_time > 0 ? util.dateFormat(data.data[i].pay_time, 'Y-M-D h:m:s') : '';
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
