<template>
	<view>
		<view class="flex flex-row flex-centerV flex-left pd10 bg-color-white">
			<text class="font-common color-black-light">当前城市：{{location.city}}</text>
			<button class="invisibility ml10" @click="onChangeCityTap">
				<view class="iconfont inline color-black-light" style="font-size: 40rpx;">&#xe64c;</view>
			</button>
		</view>
		
		<view class="flex flex-row flex-center pd10 bg-color-white">
			<u-search placeholder="可输入医院名称搜索" 
					borderColor="#ececec" 
					shape="round" 
					:clearabled="true" 
					:showAction="false"
					:height="90"
					v-model="keywords"
					@search="onSearch"></u-search>
		</view>

		<template v-for="(item, index) in dataList">
			<view class="flex flex-row flex-center md10 pd10 bg-color-white list-item" :key="index"
				@click="onItemClick(index)">
				<view class="flex flex-row flex-center">
					<image :src="item.logo_url" class="logo" />
				</view>

				<view class="flex flex-1 flex-col flex-centerH pl20 pr20">
					<view class="flex flex-row flex-left">
						<text class="strong font-big color-black">{{item.name}}</text>
					</view>

					<view class="flex flex-row flex-left mt10">
						<text class="font-common color-black-light">{{item.address}}</text>
					</view>
				</view>

				<view class="flex flex-row flex-center">
					<radio :value="item.id"></radio>
				</view>
			</view>
		</template>

		<u-loadmore :status="status" :loading-text="loadingText" :loadmore-text="loadmoreText"
			:nomore-text="nomoreText" />
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
	import HttpUtil from '@/common/utils/http_util';
	import api from '@/common/api';

	let allRegions = [];

	export default {
		data() {
			return {
				location: {
					province: '',
					city: '全国',
					city_id: 0
				},
				keywords: '',

				currentPage: 1,
				lastPage: 0,
				total: 0,
				pageSize: 10,
				loading: false,
				finished: false,
				dataList: [],
				status: 'loadmore',
				loadingText: '努力加载中',
				loadmoreText: '上拉加载更多数据',
				nomoreText: '已加载全部数据',

				regionPickerVisible: false,
				regionPickerColumns: [
					[],
					[]
				],
				regionPickerViewIndicatorStyle: `height: 40px;`,
				regionPickerViewValues: [0, 0]
			}
		},
		onLoad() {
			this.getLocation();
			this.getRegions();
		},
		onReachBottom() {
			this.requestData();
		},
		methods: {
			getLocation: function() {
				const that = this;
				if (!uni.canIUse('getLocation')) {
					that.requestData();
					return;
				}
				uni.getLocation({
					type: 'wgs84',
					success: function(res2) {
						HttpUtil.get({
							uri: api.REGION_GEO_DECODER_URL,
							params: {
								lng: res2.longitude,
								lat: res2.latitude
							},
							success: (res) => {
								that.location.province = res.data.province;
								that.location.city = res.data.city;
								that.requestData();
							}
						});
					}
				});
			},
			onChangeCityTap() {
				this.regionPickerVisible = true;
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
				this.location.province = allRegions[indexs[0]].name;
				this.location.city = allRegions[indexs[0]].cities[indexs[1]].name;
				this.location.city_id = allRegions[indexs[0]].cities[indexs[1]].id;
				this.regionPickerVisible = false;
				
				this.currentPage = 1;
				this.lastPage = 0;
				this.total = 0;
				this.finished = false;
				this.dataList = [];
				this.status = 'loadmore';
				this.requestData();
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
				this.location.province = allRegions[indexs[0]].name;
				this.location.city = allRegions[indexs[0]].cities[indexs[1]].name;
				this.location.city_id = allRegions[indexs[0]].cities[indexs[1]].id;
				this.regionPickerVisible = false;
				
				this.currentPage = 1;
				this.lastPage = 0;
				this.total = 0;
				this.finished = false;
				this.dataList = [];
				this.status = 'loadmore';
				this.requestData();
			},
			onItemClick(index) {
				uni.$emit('onHealthHospitalChecked', this.dataList[index]);
				uni.navigateBack();
			},
			onSearch(e) {
				this.currentPage = 1;
				this.lastPage = 0;
				this.total = 0;
				this.finished = false;
				this.dataList = [];
				this.status = 'loadmore';
				this.requestData();
			},
			requestData() {
				const that = this;
				this.status = 'loading';
				uni.showLoading({
					title: 'loading...',
					icon: 'none'
				});
				const params = {
					page_size: this.pageSize,
					page: this.currentPage,
					province: this.location.province,
					city: this.location.city,
					city_id: this.location.city_id,
					keywords: this.keywords
				};
				const url = api.HEALTH_HOSPITAL_LISTS_URL;
				HttpUtil.get({
					uri: url,
					params: params,
					success: (res) => {
						uni.hideLoading();
						let data = res.data;
						that.status = 'loadmore';
						if (that.currentPage >= data.last_page) {
							that.status = 'nomore';
						}
						that.currentPage = that.currentPage + 1;
						for (let i = 0; i < data.data.length; i++) {
							that.dataList.push(data.data[i]);
						}
						that.total = data.total;
						that.lastPage = data.last_page;
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
	.list-item .logo {
		height: 80rpx;
		width: 80rpx;
		border-radius: 40rpx;
	}
	
	.picker-view {
		/* width: 750rpx; */
		height: 600rpx;
		/* margin-top: 20rpx; */
	}
</style>
