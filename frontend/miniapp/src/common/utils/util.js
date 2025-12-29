function clone(obj) {
	let buf;
	if (obj instanceof Array) {
		buf = [];
		let i = obj.length;
		while (i--) {
			buf[i] = clone(obj[i]);
		}
		return buf;
	} else if (obj instanceof Object) {
		buf = {};
		for (let k in obj) {
			buf[k] = clone(obj[k]);
		}
		return buf;
	} else {
		return obj;
	}
}

export default {
	generateUUID: function() {
		var d = new Date().getTime()
		var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = (d + Math.random() * 16) % 16 | 0
			d = Math.floor(d / 16)
			return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(16)
		})
		return uuid
	},

	formatTime: function(date) {
		const year = date.getFullYear()
		const month = date.getMonth() + 1
		const day = date.getDate()
		const hour = date.getHours()
		const minute = date.getMinutes()
		const second = date.getSeconds()

		return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(
			':')
	},

	formatSeconds: (s) => {
		let str = ""
		if (s > 0) {
			const minutes = Math.floor(s / 60);
			const seconds = Math.floor(s - minutes * 60);
			let m_str = minutes < 10 ? "0" + minutes : minutes;
			let s_str = seconds < 10 ? "0" + seconds : seconds;
			str = m_str + ":" + s_str + "\"";
		}
		return str;
	},

	formatNumber: n => {
		n = n.toString()
		return n[1] ? n : '0' + n
	},

	// util.dateFormat(timestamp, 'Y/M/D h:m:s');
	dateFormat: (timestamp, format) => {
		const formatNumber = n => {
			n = n.toString()
			return n[1] ? n : '0' + n
		};

		const formateArr = ['Y', 'M', 'D', 'h', 'm', 's'];
		let returnArr = [];

		const date = new Date(timestamp * 1000);
		returnArr.push(date.getFullYear());
		returnArr.push(formatNumber(date.getMonth() + 1));
		returnArr.push(formatNumber(date.getDate()));

		returnArr.push(formatNumber(date.getHours()));
		returnArr.push(formatNumber(date.getMinutes()));
		returnArr.push(formatNumber(date.getSeconds()));

		for (let i in returnArr) {
			format = format.replace(formateArr[i], returnArr[i]);
		}
		return format;
	},

	dateToTimestamp: (date) => {
		date = date.substring(0, 19);
		date = date.replace(/-/g, '/');
		let timestamp = new Date(date).getTime() / 1000;
		return timestamp;
	},

	timestamp2TimeStr: (timestamp) => {
		let theTime = parseInt(timestamp); // 需要转换的时间秒 
		let theTime1 = 0; // 分 
		let theTime2 = 0; // 小时 
		let theTime3 = 0; // 天
		if (theTime > 60) {
			theTime1 = parseInt(theTime / 60);
			theTime = parseInt(theTime % 60);
			if (theTime1 > 60) {
				theTime2 = parseInt(theTime1 / 60);
				theTime1 = parseInt(theTime1 % 60);
				if (theTime2 > 24) {
					//大于24小时
					theTime3 = parseInt(theTime2 / 24);
					theTime2 = parseInt(theTime2 % 24);
				}
			}
		}
		let result = '';
		if (theTime > 0) {
			result = "" + parseInt(theTime) + "秒";
		}
		if (theTime1 > 0) {
			result = "" + parseInt(theTime1) + "分" + result;
		}
		if (theTime2 > 0) {
			result = "" + parseInt(theTime2) + "小时" + result;
		}
		if (theTime3 > 0) {
			result = "" + parseInt(theTime3) + "天" + result;
		}
		return result;
	},

	weekday: (timestamp) => {
		let d = new Date(timestamp);
		let weekday = new Array(7)
		weekday[0] = "Sun"
		weekday[1] = "Mon"
		weekday[2] = "Tue"
		weekday[3] = "Wed"
		weekday[4] = "Thu"
		weekday[5] = "Fri"
		weekday[6] = "Sat"
		return weekday[d.getDay()];
	},

	weekdayOfCn: (timestamp) => {
		let d = new Date(timestamp);
		let weekday = new Array(7)
		weekday[0] = "周日"
		weekday[1] = "周一"
		weekday[2] = "周二"
		weekday[3] = "周三"
		weekday[4] = "周四"
		weekday[5] = "周五"
		weekday[6] = "周六"
		return weekday[d.getDay()];
	},

	dateDiff: (dateTimeStamp) => {
		var minute = 1000 * 60;
		var hour = minute * 60;
		var day = hour * 24;
		var halfamonth = day * 15;
		var month = day * 30;
		var now = new Date().getTime();
		var diffValue = now - dateTimeStamp;
		if (diffValue < 0) {
			return;
		}
		var monthC = diffValue / month;
		var weekC = diffValue / (7 * day);
		var dayC = diffValue / day;
		var hourC = diffValue / hour;
		var minC = diffValue / minute;
		var result = '';
		if (monthC >= 1) {
			result = "" + parseInt(monthC) + "月前";
		} else if (weekC >= 1) {
			result = "" + parseInt(weekC) + "周前";
		} else if (dayC >= 1) {
			result = "" + parseInt(dayC) + "天前";
		} else if (hourC >= 1) {
			result = "" + parseInt(hourC) + "小时前";
		} else if (minC >= 1) {
			result = "" + parseInt(minC) + "分钟前";
		} else
			result = "刚刚";
		return result;
	},

	timeCounterDown: function(second) {
		var month = '',
			day = '',
			hour = '',
			minute = '';
		if (second >= 86400 * 30) {
			month = Math.floor(second / (86400 * 30));
			month = month < 10 ? '0' + month : month;
			second = second % (86400 * 30);
		}
		if (second >= 86400) {
			day = Math.floor(second / 86400);
			day = day < 10 ? '0' + day : day;
			second = second % (86400);
		}
		if (second >= 3600) {
			hour = Math.floor(second / 3600);
			hour = hour < 10 ? '0' + hour : hour;
			second = second % 3600;
		}
		if (second >= 60) {
			minute = Math.floor(second / 60);
			minute = minute < 10 ? '0' + minute : minute;
			second = second % 60;
		}
		if (second > 0) {
			second = second ? second : '';
			second = second < 10 ? '0' + second : second;
		}
		return {
			month: month,
			day: day,
			hour: hour,
			minute: minute,
			second: second
		}
	},
	// 当前时间戳，单位：秒
	nowtime: () => {
		return parseInt(((new Date()).getTime()) / 1000);
	},

	isMobilePhone: (sMobile) => {
		if (!(/^1[3|4|5|8][0-9]\d{4,8}$/.test(sMobile))) {
			return false;
		}
		return true;
	},

	clone: clone,

	rpx2px: (rpxValue, windowWidth) => {
		return (rpxValue * windowWidth) / 750
	},

	px2rpx: (pxValue, windowWidth) => {
		return (pxValue * 750) / windowWidth
	},

	strlen: (str) => {
		return str.replace(/[\u0391-\uFFE5]/g, "a").length; //先把中文替换成1个字节的英文，在计算长度
	},

	randomNumLimit: (Min, Max) => {
		let Range = Max - Min;
		let Rand = Math.random();
		let num = Min + Math.round(Rand * Range); //四舍五入
		return num;
	},

	//生成从minNum到maxNum的随机数
	randomNum: function(minNum, maxNum) {
		switch (arguments.length) {
			case 1:
				return parseInt(Math.random() * minNum + 1, 10);
				break;
			case 2:
				return parseInt(Math.random() * (maxNum - minNum + 1) + minNum, 10);
				break;
			default:
				return 0;
				break;
		}
	},
	//生成随机颜色
	randomColor: function() {
		let col = "#";
		for (let i = 0; i < 6; i++) {
			col += parseInt(Math.random() * 16).toString(16)
		}
		return col;
	},

	fileext: (filename) => {
		if (!filename || typeof filename != 'string') {
			return null
		};
		let a = filename.split('').reverse().join('');
		let b = a.substring(0, a.search(/\./)).split('').reverse().join('');
		return b
	},

	sleep: (numberMillis) => {
		let now = new Date();
		let exitTime = now.getTime() + numberMillis;
		while (true) {
			now = new Date();
			if (now.getTime() > exitTime)
				return;
		}
	},

	getUrlQueryVariable: (url, variable) => {
		let query = url.slice(url.indexOf("?") + 1);
		let vars = query.split("&");
		for (let i = 0; i < vars.length; i++) {
			let pair = vars[i].split("=");
			if (pair[0] == variable) {
				return pair[1];
			}
		}
		return null;
	},

	arabicNumerals2ChineseNumber: (num) => {
		switch (num) {
			case 1:
				return '一';
			case 2:
				return '二';
			case 3:
				return '三';
			case 4:
				return '四';
			case 5:
				return '五';
			case 6:
				return '六';
			case 7:
				return '七';
			case 8:
				return '八';
			case 9:
				return '九';
			case 10:
				return '十';
			case 11:
				return '十一';
			case 12:
				return '十二';
			case 13:
				return '十三';
			case 14:
				return '十四';
			case 15:
				return '十五';
			case 16:
				return '十六';
			case 17:
				return '十七';
			case 18:
				return '十八';
			case 19:
				return '十九';
			case 20:
				return '二十';
			default:
				return '';
		}
	},
	// compareVersion('1.11.0', '1.9.9') // 1
	compareVersion: (v1, v2) => {
		v1 = v1.split('.')
		v2 = v2.split('.')
		const len = Math.max(v1.length, v2.length)

		while (v1.length < len) {
			v1.push('0')
		}
		while (v2.length < len) {
			v2.push('0')
		}

		for (let i = 0; i < len; i++) {
			const num1 = parseInt(v1[i])
			const num2 = parseInt(v2[i])

			if (num1 > num2) {
				return 1
			} else if (num1 < num2) {
				return -1
			}
		}

		return 0
	},
	// 数组随机排序
	arrayShuffle: function(array) {
		let res = [], random;
		while (array.length > 0) {
			random = Math.floor(Math.random() * array.length);
			res.push(array[random]);
			array.splice(random, 1);
		}
		return res;
	}
}
