<template>
	<view class="flex flex-col flex-center h100">
		<u-loading-page></u-loading-page>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				
			}
		},
		onLoad(options) {
			let from = options.from || '';
			if(from) {
				from = decodeURIComponent(from);
			}
			//#ifdef MP-WEIXIN
			uni.redirectTo({
				url:'/platforms/mp-weixin/pages/user/login?from=' + encodeURIComponent(from)
			});
			//#endif
			//#ifdef MP-ALIPAY
			uni.redirectTo({
				url:'/platforms/mp-alipay/pages/user/login?from=' + encodeURIComponent(from)
			});
			//#endif
		},
		methods: {
			
		}
	}
</script>

<style>

</style>
