<template>
	<view :class="customClass" @click="onClick">
		<slot></slot>
	</view>
</template>

<script>
	import globalUtil from '@/common/global';
	
	export default {
		name: "vm-navigator",
		props: {
			url: {
				type: String,
				required:true
			},
			type: {
				type: String,
				default: 'navigate'
			},
			customClass: {
				type: String,
				default: ''
			},
			login: {
				type: Boolean,
				default: false
			},
			subMsg: {
				type: Boolean,
				default: false
			},
			msgTempIds: {
				type: Array,
				default: () => []
			}
		},
		data() {
			return {
				uniPlatform: ''
			};
		},
		mounted: function() {
			const systemInfo = uni.getSystemInfoSync();
			this.uniPlatform = systemInfo.uniPlatform;
		},
		methods: {
			onClick() {
				if(this.subMsg && this.uniPlatform == 'mp-weixin') {
					uni.requestSubscribeMessage({
					  tmplIds: this.msgTempIds,
					  complete: () => {
						  globalUtil.navigator({
						  	url: this.url,
						  	type: this.type,
						  	login: this.login
						  });
					  }
					});
				} else {
					globalUtil.navigator({
						url: this.url,
						type: this.type,
						login: this.login
					});
				}
			}
		}
	}
</script>

<style>

</style>
