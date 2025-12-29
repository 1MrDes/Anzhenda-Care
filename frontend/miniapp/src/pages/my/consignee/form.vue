<template>
	<view>
		<u--form labelPosition="left" :labelWidth="140" ref="ref-form">
			<view class="flex flex-col bg-color-white md10 pd10 round-box el-form">
				<u-form-item label="联系人" prop="consigneer.name" :required="true" :borderBottom="false">
					<u--input v-model="consigneer.name" placeholder="请填写联系人" border="bottom"></u--input>
				</u-form-item>
				
				<u-form-item label="联系电话" prop="consigneer.mobile" :required="true" :borderBottom="false">
					<u--input v-model="consigneer.mobile" placeholder="请填写联系电话" border="bottom"></u--input>
				</u-form-item>
				
				<u-form-item label="所在地区" prop="consigneer.region_name" :required="true" :borderBottom="false">
					<u--input v-model="consigneer.region_name" placeholder="请选择地区" :readonly="true" border="bottom"></u--input>
					<button class="invisibility" @click="regionPickerVisible=true" slot="right">
						<text class="font-common primary-color">选择</text>
					</button>
				</u-form-item>
				
				<u-form-item label="详细地址" prop="consigneer.address" :required="true" :borderBottom="false">
					<u--input v-model="consigneer.address" placeholder="请填写详细地址" border="bottom"></u--input>
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
</template>

<script>
	import globalUtil from '@/common/global';
	import api from '@/common/api';
	import HttpUtil from '@/common/utils/http_util';

	let allRegions = [];

	export default {
		data() {
			return {
				consigneer: {id: 0, name: '', mobile: '', province_id: 0, city_id: 0, region_name: '', address: '', is_default: 0},
				regionPickerVisible: false,
				regionPickerColumns: [
					[],
					[]
				],
				regionPickerViewIndicatorStyle: `height: 40px;`,
				regionPickerViewValues: [0, 0]
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
		onLoad(options) {
			if(options.id) {
				this.consigneer.id = options.id;
				this.getData();
			}
			this.getRegions();
		},
		methods: {
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
				this.consigneer.province_id = allRegions[indexs[0]].id;
				this.consigneer.city_id = allRegions[indexs[0]].cities[indexs[1]].id;
				this.consigneer.region_name = allRegions[indexs[0]].name + allRegions[indexs[0]].cities[indexs[1]].name;
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
				this.consigneer.province_id = allRegions[indexs[0]].id;
				this.consigneer.city_id = allRegions[indexs[0]].cities[indexs[1]].id;
				this.consigneer.region_name = allRegions[indexs[0]].name + allRegions[indexs[0]].cities[indexs[1]].name;
				this.regionPickerVisible = false;
			},
			onSubmit() {
				if(this.consigneer.name == '') {
					uni.showToast({
						title:'请输入联系人',
						icon:'none'
					});
					return;
				}
				if(this.consigneer.mobile == '') {
					uni.showToast({
						title:'请输入联系电话',
						icon:'none'
					});
					return;
				}
				if(this.consigneer.city_id == 0) {
					uni.showToast({
						title:'请选择所在地区',
						icon:'none'
					});
					return;
				}
				if(this.consigneer.address == '') {
					uni.showToast({
						title:'请输入地址',
						icon:'none'
					});
					return;
				}
				HttpUtil.post({
					uri: api.CONSIGNEER_SAVE_URL,
					params: this.consigneer,
					success: (res)=>{
						uni.showToast({
							title: '保存成功',
							icon:'none'
						});
						uni.$emit('onConsigneerCreate');
						uni.navigateBack();
					},
					fail: (res)=>{
						uni.showToast({
							title:res.msg,
							icon:'none'
						});
					}
				})
			},
			getData() {
				const that = this;
				HttpUtil.post({
					uri: api.CONSIGNEER_INFO_URL,
					params: {id: this.consigneer.id},
					success: (res)=>{
						that.consigneer = res.data.consigneer;
					},
					fail: (res)=>{
						uni.showToast({
							title:res.msg,
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
