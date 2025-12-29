import constants from "../constants.js";
import md5 from "./md5.js";
import util from "./util.js";

export default {
	get: function(obj) {
		let app = getApp();
		const apiKey = constants.API_KEY;

		const uri = obj.uri;
		const params = obj.params || {};
		const header = obj.header || {};
		const success = obj.success || null;
		const fail = obj.fail || null;
		const complete = obj.complete || null;

		if (typeof app != 'undefined') {
			params['__appid'] = app.globalData.miniapp_id;
		} else {
			const accountInfo = uni.getAccountInfoSync();
			params['__appid'] = accountInfo.miniProgram.appId;
		}
		params['__platform'] = 'miniapp';
		params['__uuid'] = util.generateUUID();
		params['__timestamp'] = util.nowtime();
		params['__level'] = constants.API_LEVEL;
		params['__version'] = constants.API_VERSION;
		params['__sign'] = md5.hex_md5(params['__level'] + params['__version'] + params['__platform'] + params[
			'__timestamp'] + params['__uuid'] + apiKey);

		header['X-Requested-With'] = 'XMLHttpRequest';

		const systemInfo = uni.getSystemInfoSync();
		params['__mpplatform'] = systemInfo.uniPlatform;

		if (typeof header['access-token'] == 'undefined') {
			if (typeof app != 'undefined') {
				const user = app.globalData.userInfo;
				header['access-token'] = user ? user.access_token : '';
			}
		}

		uni.request({
			url: uri,
			data: params,
			header: header,
			success: (response) => {
				const res = response.data;
				if (res.code == 0) { // 操作成功
					if (typeof success == 'function') {
						success(res);
					}
				} else { // 发生错误
					if (typeof fail == 'function') {
						fail(res);
					}
				}
			},
			fail: () => {
				if (typeof fail == 'function') {
					fail({
						code: -1,
						msg: '请求失败'
					});
				}
			},
			complete: () => {
				if (typeof complete == 'function') {
					complete();
				}
			}
		});
	},

	post: function(obj) {
		// const app = getApp();
		// let apiKey = '';
		// if (typeof app != 'undefined') {
		// 	apiKey = app.globalData.apiKey;
		// } else {
		// 	apiKey = wx.getStorageSync('api_key') || '';
		// }
		// for (let i = 0; i < 5; i++) {
		// 	if (apiKey == '') {
		// 		await util.sleep(200);
		// 		apiKey = wx.getStorageSync('api_key') || '';
		// 	} else {
		// 		break;
		// 	}
		// }

		let app = getApp();
		const apiKey = constants.API_KEY;

		const uri = obj.uri;
		const params = obj.params || {};
		const header = obj.header || {};
		const success = obj.success;
		const fail = obj.fail;

		if (typeof app != 'undefined') {
			params['__appid'] = app.globalData.miniapp_id;
		} else {
			const accountInfo = uni.getAccountInfoSync();
			params['__appid'] = accountInfo.miniProgram.appId;
		}
		params['__platform'] = 'miniapp';
		params['__uuid'] = util.generateUUID();
		params['__timestamp'] = util.nowtime();
		params['__level'] = constants.API_LEVEL;
		params['__version'] = constants.API_VERSION;
		params['__sign'] = md5.hex_md5(params['__level'] + params['__version'] + params['__platform'] + params[
			'__timestamp'] + params['__uuid'] + apiKey);

		header['X-Requested-With'] = 'XMLHttpRequest';

		const systemInfo = uni.getSystemInfoSync();
		params['__mpplatform'] = systemInfo.uniPlatform;

		if (typeof header['access-token'] == 'undefined') {
			if (typeof app != 'undefined') {
				const user = app.globalData.userInfo;
				header['access-token'] = user ? user.access_token : '';
			}
		}
		header['content-type'] = 'application/x-www-form-urlencoded';

		uni.request({
			url: uri,
			data: params,
			header: header,
			method: 'POST',
			success: (response) => {
				const res = response.data;
				if (res.code == 0) { // 操作成功
					if (typeof success == 'function') {
						success(res);
					}
				} else { // 发生错误
					if (typeof fail == 'function') {
						fail(res);
					}
				}
			},
			fail: () => {
				if (typeof fail == 'function') {
					fail({
						code: -1,
						msg: '请求失败'
					});
				}
			},
			complete: () => {
				if (typeof complete == 'function') {
					complete();
				}
			}
		});
	},

	uploadFile: function(obj) {
		let app = getApp();
		const apiKey = constants.API_KEY;

		const filePath = obj.filePath;
		const uri = obj.uri;
		const params = obj.params || {};
		const header = obj.header || {};
		const success = obj.success;
		const fail = obj.fail;

		if (typeof app != 'undefined') {
			params['__appid'] = app.globalData.miniapp_id;
		} else {
			const accountInfo = uni.getAccountInfoSync();
			params['__appid'] = accountInfo.miniProgram.appId;
		}
		params['__platform'] = 'miniapp';
		params['__uuid'] = util.generateUUID();
		params['__timestamp'] = util.nowtime();
		params['__level'] = constants.API_LEVEL;
		params['__version'] = constants.API_VERSION;
		params['__sign'] = md5.hex_md5(params['__level'] + params['__version'] + params['__platform'] + params[
			'__timestamp'] + params['__uuid'] + apiKey);

		header['X-Requested-With'] = 'XMLHttpRequest';

		const systemInfo = uni.getSystemInfoSync();
		params['__mpplatform'] = systemInfo.uniPlatform;

		if (typeof header['access-token'] == 'undefined') {
			if (typeof app != 'undefined') {
				const user = app.globalData.userInfo;
				header['upload-token'] = user ? user.upload_token : '';
			}
		}

		uni.uploadFile({
			url: uri,
			filePath: filePath,
			name: 'file',
			formData: params,
			header: header,
			success: (response) => {
				const res = JSON.parse(response.data);
				if (res.code == 0) { // 操作成功
					if (typeof success == 'function') {
						success(res);
					}
				} else { // 发生错误
					if (typeof fail == 'function') {
						fail(res);
					}
				}
			},
			fail: () => {
				if (typeof fail == 'function') {
					fail({
						code: -1,
						msg: '请求失败'
					});
				}
			},
			complete: () => {
				if (typeof complete == 'function') {
					complete();
				}
			}
		});
	}
}
