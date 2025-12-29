export default {
	navigator: function(obj) {
		const url = obj.url || '';
		if(url.length == 0) {
			console.log('url为空');
			return;
		}
		const type = obj.type || 'navigate';
		const login = obj.login || false;
		
		if(login) {
			const app = getApp();
			if(!app.globalData.userInfo) {
				let path = '/pages/user/login?from=';
				
				//#ifdef MP-WEIXIN
				path = '/platforms/mp-weixin/pages/user/login?from=';
				//#endif
				
				uni.navigateTo({
					url: path + encodeURIComponent(url)
				});
				return;
			}
		}
		
		if(type == 'navigate') {
			uni.navigateTo({
				url: url
			})
		} else if(type == 'redirect') {
			uni.redirectTo({
				url: url
			})
		} else if(type == 'switchTab') {
			uni.switchTab({
				url: url
			})
		} else if(type == 'reLaunch') {
			uni.reLaunch({
				url: url
			})
		}
	}
}