<template>
	<!-- #ifdef MP-WEIXIN -->
	<view v-if="assistant && (assistant.status == HEALTH_ASSISTANT_STATUS_WAIT_AUDIT || assistant.status == HEALTH_ASSISTANT_STATUS_AUDIT_PASS)">
		<view class="flex flex-col flex-center h100" v-if="assistant.status == HEALTH_ASSISTANT_STATUS_WAIT_AUDIT">
			<view class="flex flex-row flex-center">
				<text>您的申请正在审核中，请耐心等待，或联系客服。</text>
			</view>
		</view>
		
		<template v-else>
			<u-sticky>
			    <view class="flex flex-row flex-center bg-color-white tabs">
					<view class="flex flex-row flex-center flex-1 pb15 pt15" 
						v-bind:class="{current: currentTab=='tab1'}"
						@click="onTabItemClick('tab1')">
						<text>抢单</text>
					</view>
					
					<view class="flex flex-row flex-center flex-1 pb15 pt15" 
						v-bind:class="{current: currentTab=='tab2'}"
						@click="onTabItemClick('tab2')">
						<text>我的订单</text>
					</view>
				</view>
			</u-sticky>
			
			<view style="margin-top: 200rpx;" v-if="dataList.length == 0">
				<u-empty mode="order" iconSize="270" textSize="16" />
			</view>
			
			<template v-else>
				<template v-for="(item, index) in dataList">
					<view class="flex flex-col mt10 ml10 mr10 round-box bg-color-white font-common color-black-light el-order-item" 
						:key="index">
						<view class="flex flex-col pd10 header">
							<view class="flex flex-row flex-left">
								<text class="color-white font-biger">{{item.order_item.health_service_name}}</text>
							</view>
							
							<view class="flex flex-row flex-left mt10" v-if="item.order_item.spec_sku_key_name">
								<text class="color-white font-common">{{item.order_item.spec_sku_key_name}}</text>
							</view>
						</view>
						
						<view class="flex flex-row flex-left pd10 border-bottom-gray">
							<text>订单号：{{item.order_sn}}</text>
						</view>
						
						<view class="flex flex-row flex-left pd10 border-bottom-gray">
							<text>就诊时间：{{item.clinic_time_str}}</text>
						</view>
						
						<view class="flex flex-row flex-left pd10">
							<text>就诊医院：{{item.hospital_name}}</text>
						</view>
						
						<view class="flex flex-row flex-left pd10 border-top-gray" v-if="item.hospital_lab">
							<text>就诊科室：{{item.hospital_lab}}</text>
						</view>
						
						<view class="flex flex-row flex-center color-white pt10 pb10 el-btn-take" @click="onTakeClick(index)">
							<text>接单</text>
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
		</template>
	</view>
	
	<view class="flex flex-col flex-center h100" v-else>
		<template v-if="member && member.realname_auth_status == 0">
			<view class="flex flex-row flex-center pl15 pr15">
				<text class="font-big color-black-light text-center">为维护客户与您的权益，您需要完成实名认证才可以申请成为陪诊师</text>
			</view>
			
			<view class="flex flex-row flex-center mt20 w100">				
				<vm-navigator customClass="flex flex-row flex-center w100" url="/pages/my/realname_verify/apply">
					<view class="flex flex-row flex-center primary-bg-color btn-round" style="width: 600rpx;">
						<text class="font-common color-white">去实名认证</text>
					</view>
				</vm-navigator>
			</view>
			
			<view class="flex flex-row flex-center mt20 w100">
				<vm-navigator customClass="font-common primary-color ml10"
								:url="seeDoctorAssistantPolicyUrl"
								slot="right">
					<text>{{'《'}}陪诊师合作协议{{'》'}}</text>
				</vm-navigator>
			</view>
		</template>
		
		<template v-else-if="member && member.mobile == ''">
			<view class="flex flex-row flex-center pl15 pr15">
				<text class="font-big color-black-light text-center">为维护客户与您的权益，您需要绑定手机号才可以申请成为陪诊师</text>
			</view>
			
			<view class="flex flex-row flex-center mt20 w100">				
				<vm-navigator customClass="flex flex-row flex-center w100" url="/pages/my/setting/index">
					<view class="flex flex-row flex-center primary-bg-color btn-round" style="width: 600rpx;">
						<text class="font-common color-white">去绑定手机号</text>
					</view>
				</vm-navigator>
			</view>
			
			<view class="flex flex-row flex-center mt20 w100">
				<vm-navigator customClass="font-common primary-color ml10"
								:url="seeDoctorAssistantPolicyUrl"
								slot="right">
					<text>{{'《'}}陪诊师合作协议{{'》'}}</text>
				</vm-navigator>
			</view>
		</template>
		
		<template v-else>
			<u--form labelPosition="left" :labelWidth="140">
				<view class="flex flex-col bg-color-white md10 round-box">
					<view class="flex flex-col pd10 el-header">
						<view class="flex flex-row flex-left">
							<text class="color-white font-large">申请陪诊师</text>
						</view>
					</view>
					
					<view class="flex flex-col pl10 pr10 pb10">
						<u-form-item label="服务地区" prop="formData.city_id" :borderBottom="false">
							<u--input v-model="formData.city" :readonly="true" border="bottom"></u--input>
							<button class="invisibility" @click="regionPickerVisible=true" slot="right">
								<text class="font-common primary-color">选择地区</text>
							</button>
						</u-form-item>
						
						<view class="flex flex-col flex-center bg-color-white">
							<view class="flex flex-row flex-center w100 mt20">
								<checkbox-group @change="onProtocolCheckboxChange">
									<checkbox :value="1" />
								</checkbox-group>
								<text class="font-common color-black-light">我已阅读并同意</text>
								<vm-navigator customClass="font-common primary-color ml10"
												:url="seeDoctorAssistantPolicyUrl"
												slot="right">
									<text>{{'《'}}陪诊师合作协议{{'》'}}</text>
								</vm-navigator>
							</view>
							<view class="flex flex-row flex-center mt10 w100">
								<button class="invisibility" style="width: 90%;" @click="onSubmit">
									<view class="flex flex-row flex-center primary-bg-color w100 btn-round">
										<text class="font-common color-white">提交申请</text>
									</view>
								</button>
							</view>
						</view>
					</view>
				</view>
			</u--form>
		</template>
		
		<!--  #ifndef MP-ALIPAY -->
		<u-picker :show="regionPickerVisible" ref="uRegionPicker" :columns="regionPickerColumns"
			@confirm="onRegionPickerConfirm" @change="onRegionPickerChange" @cancel="regionPickerVisible=false" />
		<!--  #endif -->
		<!--  #ifdef MP-ALIPAY -->
		<template v-if="regionPickerVisible">
			<u-popup :show="regionPickerVisible">
				<view class="flex flex-row flex-center pt15 pb15 w100">
					<view class="flex flex-row flex-left pl10">
						<text class="font-common color-black-light" @click="onRegionPickerViewCancel">取消</text>
					</view>
					<view class="flex flex-row flex-center flex-1">
						<text class="font-large color-black">选择地区</text>
					</view>
					<view class="flex flex-row flex-right pr10">
						<text class="font-common color-black-light" @click="onRegionPickerViewConfirm">确定</text>
					</view>
				</view>
				<picker-view 
					:indicator-style="regionPickerViewIndicatorStyle" 
					:value="regionPickerViewValues"
					@change="onRegionPickerViewChange" 
					class="picker-view">
					<picker-view-column>
						<view class="pt10 pb10" v-for="(item, index) in regionPickerColumns[0]" :key="index">{{item}}</view>
					</picker-view-column>
					<picker-view-column>
						<view class="pt10 pb10" v-for="(item, index) in regionPickerColumns[1]" :key="index">{{item}}</view>
					</picker-view-column>
				</picker-view>
			</u-popup>
		</template>
		<!--  #endif -->
	</view>
	<!--  #endif -->
	<!-- #ifndef MP-WEIXIN -->
	<view class="flex flex-col flex-center h100">
		<view class="flex flex-row flex-center w100">
			<vm-navigator customClass="flex flex-row flex-center w100" url="/pages/my/contact_us/contact_us">
				<view class="flex flex-row flex-center primary-bg-color btn-round" style="width: 600rpx;">
					<text class="font-common color-white">请联系客服</text>
				</view>
			</vm-navigator>
		</view>
	</view>
	<!--  #endif -->
</template>

<script>
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';
	import util from '@/common/utils/util';
	import constants from '@/common/constants';
	
	let allRegions = [];
	
	export default {
		data() {
			return {
				HEALTH_ASSISTANT_STATUS_WAIT_AUDIT: constants.HEALTH_ASSISTANT_STATUS_WAIT_AUDIT,
				HEALTH_ASSISTANT_STATUS_AUDIT_PASS: constants.HEALTH_ASSISTANT_STATUS_AUDIT_PASS,
				HEALTH_ASSISTANT_STATUS_AUDIT_REJECT: constants.HEALTH_ASSISTANT_STATUS_AUDIT_REJECT,
				STATIC_BASE_URL: api.STATIC_BASE_URL,
				assistant: null,
				member: null,
				formData: {province_id: 0, province: '', city_id: 0, city: ''},
				protocolCheckboxValue: false,
				seeDoctorAssistantPolicyUrl: '',
				
				regionPickerVisible: false,
				regionPickerColumns: [
					[],
					[]
				],
				regionPickerViewIndicatorStyle: `height: 40px;`,
				regionPickerViewValues: [0, 0],
				
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
					url: '/pages/user/login?from=' + encodeURIComponent('/pages/my/health_assistant/index'),
					login: true,
					type: 'redirect'
				});
			}
		},
		onLoad() {
			const app = getApp();
			this.seeDoctorAssistantPolicyUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.see_doctor_assistant_policy);
			
			this.getMyAssistant();
			this.getMemberAccount();
			uni.$on('onRealnameVerifySuccess', ()=>{
				this.getMemberAccount();
			});
			uni.$on('onMemberPhoneBind', ()=>{
				this.getMemberAccount();
			});
			this.getRegions();
			this.requestData();
		},
		onPullDownRefresh() {
			this.currentPage = 1;
			this.lastPage = 0;
			this.total = 0;
			this.dataList = [];	
			this.status = 'loadmore';
			this.requestData();
		},
		onReachBottom() {
			this.requestData();
		},
		methods: {
			onTabItemClick(index) {
				this.currentTab = index;
				if(index == 'tab2') {
					globalUtil.navigator({
						url: '/pages/my/health_assistant/order/index',
						type: 'redirect',
						login: true
					});
				}
			},
			getMyAssistant: function() {
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_ASSISTANT_MY_URL,
					success: (res) => {
						that.assistant = res.data.assistant
					}
				});
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
			onProtocolCheckboxChange(e) {
				const values = e.detail.value;
				this.protocolCheckboxValue = values.length > 0 ? true : false;
			},
			getRegions: function() {
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_HOSPITAL_REGIONS_URL,
					success: (res) => {
						allRegions = res.data.regions;
						let provinces = [], cities = [];
						for(let k = 0; k < allRegions.length; k++) {
							provinces.push(allRegions[k].name);
						}
						for(let m = 0; m < allRegions[0].cities.length; m++) {
							cities.push(allRegions[0].cities[m].name);
						}
						that.regionPickerColumns = [provinces, cities];
					}
				});
			},
			onRegionPickerConfirm(e) {
				const indexs = e.indexs;
				this.formData.province = allRegions[indexs[0]].name;
				this.formData.province_id = allRegions[indexs[0]].id;
				this.formData.city = allRegions[indexs[0]].cities[indexs[1]].name;
				this.formData.city_id = allRegions[indexs[0]].cities[indexs[1]].id;
				this.regionPickerVisible = false;
			},
			onRegionPickerChange(e) {
				const {
					columnIndex,
					value,
					values, // values为当前变化列的数组内容
					index,
					// 微信小程序无法将picker实例传出来，只能通过ref操作
					picker = this.$refs.uRegionPicker
				} = e;
				// 当第一列值发生变化时，变化第二列(后一列)对应的选项
				if (columnIndex === 0) {
				    // picker为选择器this实例，变化第二列对应的选项					
					let cities = [];
					for(let m = 0; m < allRegions[index].cities.length; m++) {
						cities.push(allRegions[index].cities[m].name);
					}
					picker.setColumnValues(1, cities);
				}
			},
			onRegionPickerViewChange(e) {
				const indexes = e.detail.value;
				let cities = [];
				const index = indexes[0];
				for(let m = 0; m < allRegions[index].cities.length; m++) {
					cities.push(allRegions[index].cities[m].name);
				}
				this.$set(this.regionPickerColumns, 1, cities);
				this.regionPickerViewValues = indexes;
			},
			onRegionPickerViewCancel() {
				this.regionPickerVisible = false;
			},
			onRegionPickerViewConfirm() {
				const indexs = this.regionPickerViewValues;
				this.formData.province = allRegions[indexs[0]].name;
				this.formData.province_id = allRegions[indexs[0]].id;
				this.formData.city = allRegions[indexs[0]].cities[indexs[1]].name;
				this.formData.city_id = allRegions[indexs[0]].cities[indexs[1]].id;
				this.regionPickerVisible = false;
			},
			onSubmit() {
				if(!this.protocolCheckboxValue) {
					uni.showToast({
						title:'您需要同意服务协议才可以申请',
						icon:'none'
					});
					return;
				}
				if(this.formData.city_id == 0) {
					uni.showToast({
						title:'请先选择服务地区',
						icon:'none'
					});
					return;
				}
				uni.showLoading({
					title:'loading...'
				});
				const that = this;
				HttpUtil.post({
					uri: api.HEALTH_ASSISTANT_APPLY_URL,
					params: this.formData,
					success: (res) => {
						uni.hideLoading();
						that.getMyAssistant();
						uni.showToast({
							title:'申请成功，请等待审核',
							icon:'none'
						});
					},
					fail: (res) => {
						uni.hideLoading();
						uni.showToast({
							title:res.msg,
							icon:'none'
						});
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
				const url = api.HEALTH_ASSISTANT_ORDER_UNASSIGNED_URL + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
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
							let orderItem = data.data[i].items[0];
							orderItem.spec_sku_key_name = orderItem.spec_sku_key_name.replaceAll('\\n', ' ');
							data.data[i]['order_item'] = orderItem;
							data.data[i]['clinic_time_str'] = util.dateFormat(data.data[i].clinic_time, 'Y-M-D h:m:s');
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
			},
			onTakeClick(index) {
				const item = this.dataList[index];
				const that = this;
				uni.showModal({
					title: '提示',
					content: '确定要接该订单吗？',
					success: function (res2) {
						if (res2.confirm) {
							HttpUtil.get({
								uri: api.HEALTH_ASSISTANT_ORDER_TAKE_ORDER_URL,
								params: {
									order_sn: item.order_sn
								},
								success: (res) => {
									uni.showToast({
										title:'接单成功',
										icon:'none'
									});
									that.dataList.splice(index, 1);
								},
								fail: (res) => {
									uni.showToast({
										title:res.msg,
										icon:'none'
									});
								}
							});
						} else if (res2.cancel) {
							console.log('用户点击取消');
						}
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
	.el-header {
		background-image: linear-gradient(to bottom right, #33b5bd, #76e9f0);
		border-top-left-radius: 24rpx;
		border-top-right-radius: 24rpx;
	}
	
	.el-order-item .header {
		background-image: linear-gradient(to bottom right, #33b5bd, #76e9f0);
		border-top-left-radius: 24rpx;
		border-top-right-radius: 24rpx;
	}
	
	.el-btn-take {
		background-image: linear-gradient(to bottom right, #33b5bd, #76e9f0);
		border-bottom-left-radius: 24rpx;
		border-bottom-right-radius: 24rpx;
	}
</style>
