<template>
	<view>
		<u--form labelPosition="left" :labelWidth="140" ref="ref-form">
			<view class="flex flex-col bg-color-white md10 pd10 round-box el-form">				
				<u-form-item label="快递公司" prop="shipFormData.express_name" :required="true" :borderBottom="false">
					<u--input v-model="shipFormData.express_name" placeholder="请选择快递公司" :readonly="true" border="bottom"></u--input>
					<button class="invisibility" @click="expressPickerVisible=true" slot="right">
						<text class="font-common primary-color">选择</text>
					</button>
				</u-form-item>
				
				<u-form-item label="快递单号" prop="shipFormData.express_sn" :required="true" :borderBottom="false">
					<u--input v-model="shipFormData.express_sn" placeholder="请填写快递单号" border="bottom"></u--input>
				</u-form-item>
			</view>
		</u--form>
		
		<view class="empty-box"></view>
		<view class="flex flex-col flex-center bg-color-white bottom-wrapper">
			<view class="flex flex-row flex-center mt10 w100">
				<button class="invisibility" style="width: 90%;" @click="onSubmit">
					<view class="flex flex-row flex-center primary-bg-color btn-round">
						<text class="font-common color-white">保存</text>
					</view>
				</button>
			</view>
		</view>
		
		<u-action-sheet :actions="expressPickerActions"
						title="快递公司" 
						:show="expressPickerVisible"
						:safeAreaInsetBottom="true"
						:closeOnClickOverlay="true" 
						:closeOnClickAction="true"
						cancelText="取消"
						@close="expressPickerVisible=false"
						@select="onExpressPickerSelect"></u-action-sheet>
	</view>
</template>

<script>
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';
	
	export default {
		data() {
			return {
				shipFormData: {order_sn: '', express_sn: '', express_code: '', express_name: ''},
				expressList: [],
				expressPickerActions: [],
				expressPickerVisible: false
			}
		},
		onLoad(options) {
			this.shipFormData.order_sn = options.order_sn;
			this.getExpressCompany();
		},
		methods: {
			onExpressPickerSelect(item) {
				this.shipFormData.express_name = item.name;
				this.shipFormData.express_code = item.code;
				this.expressPickerVisible = false;
			},
			getExpressCompany: function() {
				const that = this;
				HttpUtil.get({
					uri: api.EXPRESS_COMPANY_URL,
					success: (res) => {
						let expressPickerActions = [];
						for(let key in res.data.express_company) {
							expressPickerActions.push({
								code: res.data.express_company[key].code,
								name: res.data.express_company[key].name
							});
						}
						that.expressPickerActions = expressPickerActions;
					},
					fail: (res) => {
						uni.showToast({
							title: '发生错误，请重试',
							icon:'none'
						});
					}
				});
			},
			onSubmit() {
				if(this.shipFormData.express_sn == '' || this.shipFormData.express_code == '') {
					uni.showToast({
						title: '快递公司和快递单号不能为空',
						icon:'none'
					});
					return;
				}
				const that = this;
				HttpUtil.post({
					uri: api.HEALTH_ASSISTANT_ORDER_SHIP_URL,
					params: this.shipFormData,
					success: (res) => {
						uni.showToast({
							title: '保存成功',
							icon:'none'
						});
						uni.$emit('onHealthAssistantOrderShip');
						uni.navigateBack();
					},
					fail: (res) => {
						uni.showToast({
							title: res.msg,
							icon:'none'
						});
					}
				});
			}
		}
	}
</script>

<style>

</style>
