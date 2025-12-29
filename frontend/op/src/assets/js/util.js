/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-9 15:26
 * Description:
 */
export default {
  checkAuth: function (vm, path) {
    let user = JSON.parse(sessionStorage.getItem('user'))
    if (path == '' || path == '/main' || (user != null && user.isBoss == 1)) {
      return true
    }
    let result = false;
    let pathinfo = path.split('/');
    let authorizedResources = localStorage.getItem("authorizedResources");
    if (authorizedResources != null) {
      authorizedResources = JSON.parse(authorizedResources);
      for (let i = 0; i < authorizedResources.length; i++) {
        if (authorizedResources[i] == '/' + pathinfo[1] + '/*'
          || authorizedResources[i] == '/' + pathinfo[1] + '/' + pathinfo[2] + '/*'
          || authorizedResources[i] == '/' + pathinfo[1] + '/' + pathinfo[2] + '/' + pathinfo[3]) {
          result = true
          break
        }
      }
    }
    return result
  },
  generateUUID: function () {
    var d = new Date().getTime()
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = (d + Math.random() * 16) % 16 | 0
      d = Math.floor(d / 16)
      return (c == 'x' ? r : (r & 0x3 | 0x8)).toString(16)
    })
    return uuid
  },
  dateFormat: function (time, _format) {
    let newdate = new Date()
    newdate.setTime(time * 1000)

    Date.prototype.format = function (format) {
      var date = {
        'M+': this.getMonth() + 1,
        'd+': this.getDate(),
        'h+': this.getHours(),
        'm+': this.getMinutes(),
        's+': this.getSeconds(),
        'q+': Math.floor((this.getMonth() + 3) / 3),
        'S+': this.getMilliseconds()
      }
      if (/(y+)/i.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length))
      }
      for (var k in date) {
        if (new RegExp('(' + k + ')').test(format)) {
          format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? date[k] : ('00' + date[k]).substr(('' + date[k]).length))
        }
      }
      return format
    }

    return newdate.format(_format)
  },
  dateToTimestamp: function (date) {
    date = date.substring(0, 19);
    date = date.replace(/-/g, '/');
    let timestamp = new Date(date).getTime() / 1000;
    return timestamp;
  },
  checkMobile: function (sMobile) {
    if (!(/^1[3|4|5|8][0-9]\d{4,8}$/.test(sMobile))) {
      return false;
    }
    return true;
  },
  clone: function (obj) {
    let buf;
    if (obj instanceof Array) {
      buf = [];
      let i = obj.length;
      while (i--) {
        buf[i] = this.clone(obj[i]);
      }
      return buf;
    } else if (obj instanceof Object) {
      buf = {};
      for (let k in obj) {
        buf[k] = this.clone(obj[k]);
      }
      return buf;
    } else {
      return obj;
    }
  },
  /**
   * var $spec_arr = [
   ['7'],
   ['15','2', '3'],
   ['1', '5', '4']
   ];
   var result = DescartesUtils.descartes($spec_arr);
   */
  DescartesUtils: {

    /**
     * 如果传入的参数只有一个数组，求笛卡尔积结果
     * @param arr1 一维数组
     * @returns {Array}
     */
    descartes1: function (arr1) {
      // 返回结果，是一个二维数组
      var result = [];
      var i = 0;
      for (i = 0; i < arr1.length; i++) {
        var item1 = arr1[i];
        result.push([item1]);
      }
      return result;
    },

    /**
     * 如果传入的参数只有两个数组，求笛卡尔积结果
     * @param arr1 一维数组
     * @param arr2 一维数组
     * @returns {Array}
     */
    descartes2: function (arr1, arr2) {
      // 返回结果，是一个二维数组
      var result = [];
      var i = 0, j = 0;
      for (i = 0; i < arr1.length; i++) {
        var item1 = arr1[i];
        for (j = 0; j < arr2.length; j++) {
          var item2 = arr2[j];
          result.push([item1, item2]);
        }
      }
      return result;
    },

    /**
     *
     * @param arr2D 二维数组
     * @param arr1D 一维数组
     * @returns {Array}
     */
    descartes2DAnd1D: function (arr2D, arr1D) {
      var i = 0, j = 0;
      // 返回结果，是一个二维数组
      var result = [];

      for (i = 0; i < arr2D.length; i++) {
        var arrOf2D = arr2D[i];
        for (j = 0; j < arr1D.length; j++) {
          var item1D = arr1D[j];
          result.push(arrOf2D.concat(item1D));
        }
      }

      return result;
    },

    descartes3: function (list) {
      var listLength = list.length;
      var i = 0, j = 0;
      // 返回结果，是一个二维数组
      var result = [];
      // 为了便于观察，采用这种顺序
      var arr2D = this.descartes2(list[0], list[1]);
      for (i = 2; i < listLength; i++) {
        var arrOfList = list[i];
        arr2D = this.descartes2DAnd1D(arr2D, arrOfList);
      }
      return arr2D;
    },

    //笛卡儿积组合
    descartes: function (list) {
      if (!list) {
        return [];
      }
      if (list.length <= 0) {
        return [];
      }
      if (list.length == 1) {
        return this.descartes1(list[0]);
      }
      if (list.length == 2) {
        return this.descartes2(list[0], list[1]);
      }
      if (list.length >= 3) {
        return this.descartes3(list);
      }
    }
  },
  getQueryVariable: function (variable) {
    const query = window.location.search.substring(1);
    const vars = query.split("&");
    for (let i = 0; i < vars.length; i++) {
      let pair = vars[i].split("=");
      if (pair[0] == variable) {
        return pair[1];
      }
    }
    return null;
  },
  getVariableByQuery: function (query, variable) {
    const vars = query.split("&");
    for (let i = 0; i < vars.length; i++) {
      let pair = vars[i].split("=");
      if (pair[0] == variable) {
        return pair[1];
      }
    }
    return null;
  }
}
