<template>
	<view>
		<u--form labelPosition="left" :labelWidth="140" ref="form-shopcard">
			<view class="flex flex-col bg-color-white md10 round-box el-shopcart">
				<view class="flex flex-col pd10 el-shopcart-header" v-if="shopcartItem && shopcartItem.health_service">
					<view class="flex flex-row flex-left">
						<text class="color-white font-large">{{shopcartItem.health_service.name}}</text>
					</view>
					<view class="flex flex-row flex-left mt5" v-if="shopcartItem.spec_sku_key_name_format">
						<text class="color-white font-common">{{shopcartItem.spec_sku_key_name_format}}</text>
					</view>
				</view>
			
				<view class="flex flex-col pl10 pr10">
					<u-form-item label="医院" prop="order.hospital_name" :required="true" :borderBottom="false">
						<u--input v-model="order.hospital_name" placeholder="请填写医院名称" :readonly="true" border="bottom"></u--input>
						<vm-navigator customClass="font-common primary-color" 
										url="/pages/health_hospital/select"
										slot="right">选择</vm-navigator>
					</u-form-item>
			
					<u-form-item label="科室" prop="order.hospital_lab" :borderBottom="false">
						<!--  #ifndef MP-ALIPAY -->
						<u--input v-model="order.hospital_lab" placeholder="请填写科室名称" border="bottom"></u--input>
						<button class="invisibility" :disabled="order.hospital_id==0" @click="getHospitalLabs" slot="right">
							<text class="font-common primary-color">选择</text>
						</button>
						<!--  #endif -->
						<!--  #ifdef MP-ALIPAY -->
						<view>
							<uni-data-picker 
								placeholder="请选择科室" 
								popup-title="请选择就诊科室" 
								:localdata="hospitalLabColumns" 
								v-model="order.hospital_lab_id"
								:map="{text:'name',value:'id'}"
								@change="onHospitalLabPickerChange">
							</uni-data-picker>
						</view>
						<!--  #endif -->
					</u-form-item>
					
					<u-form-item label="就诊时间" prop="order.clinic_time_str" :borderBottom="false">
						<!--  #ifndef MP-ALIPAY -->
						<u--input v-model="order.clinic_time_str" placeholder="请选择就诊时间" :readonly="true" border="bottom"></u--input>
						<button class="invisibility" @click="onShowDatetimePicker" slot="right">
							<text class="font-common primary-color">选择</text>
						</button>
						<!--  #endif -->
						
						<!--  #ifdef MP-ALIPAY -->
							<view>
								<uni-datetime-picker
									type="datetime"
									v-model="order.clinic_time_str"
									@change="onDatetimePickerChange"
								/>
							</view>
						<!--  #endif -->
					</u-form-item>
					
					<u-form-item label="联系人" prop="order.contact_name" :required="true" :borderBottom="false">
						<u--input v-model="order.contact_name" placeholder="请填写联系人" border="bottom"></u--input>
					</u-form-item>
					
					<u-form-item label="联系电话" prop="order.contact_tel" :required="true" :borderBottom="false">
						<u--input v-model="order.contact_tel" placeholder="请填写联系电话" border="bottom"></u--input>
					</u-form-item>
				</view>
			</view>
		
			<view class="ml10 mr10 mb10 pd10 round-box bg-color-white">
				<u--textarea v-model="order.remark" placeholder="请输入备注信息" :height="120"></u--textarea>
			</view>
		</u--form>
		
		<template v-if="shopcartItem && shopcartItem.health_service && shopcartItem.health_service.shipping_type == HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS">
			<view class="flex flex-col bg-color-white md10 round-box el-shopcart">
				<view class="flex flex-col pd10 el-shopcart-header">
					<view class="flex flex-row flex-left">
						<text class="color-white font-common">收货地址</text>
					</view>
				</view>
				
				<view class="flex flex-row flex-center pt15 pb15" 
						@click="consigneerPickerVisible=true"
						v-if="order.consigneer_id==0">
					<text class="font-common color-black-light">+ 选择收货地址</text>
				</view>
				
				<view class="flex flex-row flex-center pd10" v-else>
					<view class="flex flex-col flex-1">
						<view class="flex flex-row flex-left flex-centerV">
							<text class="font-big strong">{{order.consigneer.name}}</text>
							<text class="font-common color-black-light ml10">{{order.consigneer.mobile}}</text>
						</view>
						
						<view class="flex flex-row flex-left flex-centerV mt10">
							<text class="font-common color-black-light">{{order.consigneer.region_name + ' ' + order.consigneer.address}}</text>
						</view>
					</view>
					
					<view class="flex flex-row flex-center pl10">
						<button class="invisibility" @click="consigneerPickerVisible=true">
							<view class="iconfont inline color-black-light" style="font-size: 40rpx;">&#xec7c;</view>
						</button>
					</view>
				</view>
			</view>
		</template>

		<view class="empty-box" style="height: calc(100px + env(safe-area-inset-bottom));"></view>
		<view class="flex flex-col flex-center bg-color-white bottom-wrapper border-top-gray">
			<view class="flex flex-row flex-center w100">
				<checkbox-group @change="onProtocolCheckboxChange">
					<checkbox :value="1" />
				</checkbox-group>
				<text class="font-common color-black-light">我已阅读并同意</text>
				<vm-navigator customClass="font-common primary-color ml10"
								:url="healthServiceBuyProtocolUrl"
								slot="right">服务协议</vm-navigator>
			</view>
			<view class="flex flex-row flex-center mt10 w100">
				<button class="invisibility" style="width: 90%;" @click="onSubmitOrder">
					<view class="flex flex-row flex-center primary-bg-color btn-round">
						<text class="font-common color-white">提交订单</text>
					</view>
				</button>
			</view>
		</view>

		<u-action-sheet :actions="consigneerPickerActions" 
						title="收货地址" 
						:show="consigneerPickerVisible"
						:safeAreaInsetBottom="true"
						:closeOnClickOverlay="true" 
						:closeOnClickAction="true"
						cancelText="取消"
						@close="consigneerPickerVisible=false"
						@select="onConsigneerPickerSelect"></u-action-sheet>
		<!--  #ifndef MP-ALIPAY -->
			<u-datetime-picker
			                :show="datetimePickerVisible"
			                v-model="currentTime"
			                mode="datetime"
							@confirm="onDatetimePickerConfirm"
							@cancel="datetimePickerVisible=false"
			        ></u-datetime-picker>
			<u-picker :show="hospitalLabPickerVisible" ref="uHospitalLabPicker" :columns="hospitalLabPickerColumns"
				@confirm="onHospitalLabConfirm" @change="onHospitalLabChange" @cancel="hospitalLabPickerVisible=false" />
		<!--  #endif -->
		
	</view>
</template>

<script>
	import util from '@/common/utils/util';
	import HttpUtil from '@/common/utils/http_util';
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import constants from '@/common/constants';

	let hospitalLabs = [];
	let consigneers = [];

	export default {
		data() {
			return {
				HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_OFFLINE: constants.HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_OFFLINE,
				HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS: constants.HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS,
				
				shopcartItem: null,
				order: {
					hospital_id: 0,
					hospital_name: '',
					hospital: null,
					hospital_lab_id: '',
					hospital_lab: '',
					clinic_time: 0,
					clinic_time_str: '',
					contact_name: '',
					contact_tel: '',
					remark: '',
					consigneer_id: 0,
					consigneer: null
				},
				hospitalLabPickerVisible: false,
				hospitalLabPickerColumns: [
					[],
					[]
				],
				hospitalLabColumns: [],
				datetimePickerVisible: false,
				currentTime: Number(new Date()),
				currentDate: util.dateFormat(((new Date().getTime())/1000 + 3600*24), 'Y年M月D日 h:m:s'),
				
				protocolCheckboxValue: false,
				healthServiceBuyProtocolUrl: '',
				
				consigneerPickerVisible: false,
				consigneerPickerActions: [],
				
				msgTempIds: []
			}
		},
		onLoad() {
			const app = getApp();
			const shopcartItems = app.globalData.shopcartItems;
			if (shopcartItems) {
				this.shopcartItem = shopcartItems[0];
				this.getServiceData();
			}

			this.getConsigneers();

			this.healthServiceBuyProtocolUrl = '/pages/web/page?url=' + encodeURIComponent(app.globalData.siteConfig.health_service_buy_protocol_url);

			if(app.globalData.siteConfig) {
				this.msgTempIds.push(app.globalData.siteConfig.msg_template.order_status_notice);
			}
			
			uni.$on('onSiteConfigLoaded', (cfg)=>{
				this.msgTempIds.push(cfg.msg_template.order_status_notice);
			});
			
			uni.$on('onHealthHospitalChecked', (hospital) => {
				this.order.hospital_id = hospital.id;
				this.order.hospital_name = hospital.name;
				this.order.hospital = hospital;
				this.getHospitalLabs();
			});
			
			uni.$on('onConsigneerCreate', ()=>{
				this.getConsigneers();
			});
		},
		onShow() {
			const app = getApp();
			if (!app.globalData.userInfo) {
				uni.redirectTo({
					url: '/pages/user/login'
				});
			}
		},
		methods: {
			getConsigneers() {
				const that = this;
				const url = api.CONSIGNEER_ALL_URL;
				HttpUtil.get({
					uri: url,
					success: (res) => {
						let consigneerPickerActions = [];
						for(let k = 0; k < res.data.consigneers.length; k++) {
							consigneerPickerActions.push({
								index: k,
								name: res.data.consigneers[k].name + ' ' + res.data.consigneers[k].mobile,
								subname: res.data.consigneers[k].region_name + res.data.consigneers[k].address
							});
						}
						consigneerPickerActions.push({
							index: res.data.consigneers.length,
							name: '+ 添加地址'
						});
						that.consigneerPickerActions = consigneerPickerActions;
						consigneers = res.data.consigneers;
					}
				});
			},
			onConsigneerPickerSelect(item) {
				if(item.index == consigneers.length) {
					globalUtil.navigator({
						url: '/pages/my/consignee/form',
						login: true
					});
				} else {
					this.order.consigneer_id = consigneers[item.index].id;
					this.order.consigneer = consigneers[item.index];
					this.consigneerPickerVisible = false;
				}
			},
			onProtocolCheckboxChange(e) {
				const values = e.detail.value;
				this.protocolCheckboxValue = values.length > 0 ? true : false;
			},
			onDatetimePickerConfirm(e) {
				this.order.clinic_time = e.value / 1000;
				this.order.clinic_time_str = util.dateFormat(this.order.clinic_time, 'Y年M月D日 h:m:s');
				this.datetimePickerVisible = false;
			},
			onShowDatetimePicker() {
				this.datetimePickerVisible = true;
			},
			onDatetimePickerChange(value) {
				this.order.clinic_time_str = value;
				this.order.clinic_time = util.dateToTimestamp(value);
			},
			onHospitalLabPickerChange(e) {
				const values = e.detail.value;
				let labs = '';
				if(values.length > 0) {
					labs += values[0].text;
					if(values.length > 1) {
						labs += '-' + values[1].text;
					}
				}
				this.order.hospital_lab = labs;
			},
			onHospitalLabChange(e) {
				const {
					columnIndex,
					value,
					values, // values为当前变化列的数组内容
					index,
					// 微信小程序无法将picker实例传出来，只能通过ref操作
					picker = this.$refs.uHospitalLabPicker
				} = e;
				// 当第一列值发生变化时，变化第二列(后一列)对应的选项
				if (columnIndex === 0) {
					// picker为选择器this实例，变化第二列对应的选项					
					let secondCols = [];
					for (let m = 0; m < hospitalLabs[index].children.length; m++) {
						secondCols.push(hospitalLabs[index].children[m].name);
					}
					picker.setColumnValues(1, secondCols);
				}
			},
			onHospitalLabConfirm(e) {
				const indexs = e.indexs;
				let hospital_lab = hospitalLabs[indexs[0]].name;
				if(hospitalLabs[indexs[0]].children && hospitalLabs[indexs[0]].children.length > 0) {
					hospital_lab += '-' + hospitalLabs[indexs[0]].children[indexs[1]].name;
				}
				this.order.hospital_lab = hospital_lab;
				this.hospitalLabPickerVisible = false;
			},
			getHospitalLabs: function() {
				if (this.order.hospital_id == 0) {
					// uni.showToast({
					// 	title: '请先选择医院',
					// 	icon: 'error'
					// });
					return;
				}
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_HOSPITAL_LABS_URL,
					params: {
						hospital_id: this.order.hospital_id
					},
					success: (res) => {
						if (res.data.labs.length == 0) {
							uni.showToast({
								title: '暂未收录该医院科室',
								icon: 'none'
							});
						} else {
							hospitalLabs = res.data.labs;
							// #ifndef MP-ALIPAY
							let firstCols = [],
								secondCols = [];
							for (let k = 0; k < hospitalLabs.length; k++) {
								firstCols.push(hospitalLabs[k].name);
								if (k == 0) {
									for (let m = 0; m < hospitalLabs[k].children.length; m++) {
										secondCols.push(hospitalLabs[k].children[m].name);
									}
								}
							}
							that.hospitalLabPickerColumns = [firstCols, secondCols];
							that.hospitalLabPickerVisible = true;
							// #endif
							// #ifdef MP-ALIPAY
							that.hospitalLabColumns = hospitalLabs;
							// #endif
						}
					}
				});
			},
			getServiceData: function() {
				const that = this;
				HttpUtil.get({
					uri: api.HEALTH_SERVICE_INFO_URL,
					params: {
						id: this.shopcartItem.health_service_id
					},
					success: (res) => {
						that.shopcartItem.health_service = res.data.goods;
						let specs = [];
						for (let key in res.data.goods_specs) {
							specs.push(res.data.goods_specs[key]);
						}
						let specSkuKeyName = [];
						if (that.shopcartItem.spec_sku_key_name) {
							that.shopcartItem.spec_sku_key_name = that.shopcartItem.spec_sku_key_name
								.split("\n");
							for (let k = 0; k < that.shopcartItem.spec_sku_key_name.length; k++) {
								specSkuKeyName.push(specs[k].spec.name + ':' + that.shopcartItem
									.spec_sku_key_name[k]);
							}
						}
						that.shopcartItem.spec_sku_key_name_format = specSkuKeyName.join('  ');
					}
				});
			},
			onSubmitOrder: function() {
				if(!this.protocolCheckboxValue) {
					uni.showToast({
						title: '您需要同意服务协议才能提交订单',
						icon: 'none'
					});
					return;
				}
				if(this.order.hospital_id == 0) {
					uni.showToast({
						title: '请选择医院',
						icon: 'none'
					});
					return;
				}
				if(this.order.contact_name == '') {
					uni.showToast({
						title: '请输入联系人名称',
						icon: 'none'
					});
					return;
				}
				if(this.order.contact_tel == '') {
					uni.showToast({
						title: '请输入联系电话',
						icon: 'none'
					});
					return;
				}
				
				if(this.shopcartItem.health_service.shipping_type == constants.HEALTH_ASSISTANT_ORDER_SHIPPING_TYPE_EXPRESS
					&& this.order.consigneer_id == 0) {
					uni.showToast({
						title: '请选择收货地址',
						icon: 'none'
					});
					return;
				}
				
				const that = this;
				let postData = this.order;
				postData['order_items'] = JSON.stringify([this.shopcartItem]);
				
				HttpUtil.post({
					uri: api.HEALTH_SERVICE_ORDER_CREATE_URL,
					params: postData,
					success: (res) => {
						uni.$emit('onHealthServiceOrderCreated');
						//#ifdef MP-ALIPAY
						globalUtil.navigator({
							url : '/pages/order/detail?sn=' + res.data.order_sn,
							login: true,
							type: 'redirect'
						});
						return;
						//#endif
						if(res.data.pay_params) {
							let payment = {
							    provider: '',
							    orderInfo: '',
								timeStamp: '',
								nonceStr: '',
								package: '',
								signType: '',
								paySign: '',
								service: 3,
							    success: function (res2) {
							        that.onPaySuccess(res.data.order_sn, res.data.pay_trade_no);
							    },
							    fail: function (err) {
							        globalUtil.navigator({
							        	url : '/pages/order/detail?sn=' + res.data.order_sn,
							        	login: true,
										type: 'redirect'
							        });
							    }
							};
							//#ifdef MP-WEIXIN || MP-TOUTIAO
							payment.provider = 'wxpay';
							const payParams = JSON.parse(res.data.pay_params);
							payment.timeStamp = payParams.timeStamp;
							payment.nonceStr = payParams.nonceStr;
							payment.package = payParams.package;
							payment.signType = payParams.signType;
							payment.paySign = payParams.paySign;
							//#endif
							
							//#ifdef MP-ALIPAY
							payment.provider = 'alipay';
							//#endif
							
							//#ifdef MP-BAIDU
							payment.provider = 'baidu';
							//#endif
							
							//#ifdef MP-TOUTIAO
							
							//#endif
							
							uni.requestPayment(payment);
						} else {
							globalUtil.navigator({
								url : '/pages/order/detail?sn=' + res.data.order_sn,
								login: true,
								type: 'redirect'
							});
						}
					},
					fail: (res) => {
						uni.showToast({
							title: res.msg,
							icon: 'none'
						});
					}
				})
			},
			onPaySuccess: function(orderSn, payTradeNo) {
				const that = this;
				HttpUtil.get({
					uri: api.PAY_TRADE_QUERY_URL,
					params: {
						pay_trade_no: payTradeNo
					},
					success: (res) => {
						const systemInfo = uni.getSystemInfoSync();
						if(systemInfo.uniPlatform == 'mp-weixin') {
							uni.requestSubscribeMessage({
							  tmplIds: that.msgTempIds,
							  complete: () => {
								globalUtil.navigator({
									url : '/pages/order/detail?sn=' + orderSn,
									login: true,
									type: 'redirect'
								});
							  }
							});
						} else {
							globalUtil.navigator({
								url : '/pages/order/detail?sn=' + orderSn,
								login: true,
								type: 'redirect'
							});
						}
					},
					fail: (res) => {
						uni.showToast({
							title: '发生错误，请重试',
							icon:'none'
						});
						globalUtil.navigator({
							url : '/pages/order/detail?sn=' + orderSn,
							login: true,
							type: 'redirect'
						});
					}
				});
			}
		}
	}
</script>

<style>
	.el-shopcart .el-shopcart-header {
		background-image: linear-gradient(to bottom right, #33b5bd, #76e9f0);
		border-top-left-radius: 24rpx;
		border-top-right-radius: 24rpx;
	}
</style>
