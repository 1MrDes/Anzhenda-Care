// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import resource from 'vue-resource'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'
import Vuex from 'vuex'
import store from './vuex/store'
import util from './assets/js/util'
import 'vant/lib/index.css';
import VueClipboard from 'vue-clipboard2';
import '@vant/touch-emulator';

import './assets/css/common.css';
import './assets/css/iconfont/iconfont.css';

import global from './common/global';

Vue.config.productionTip = false;
Vue.use(resource);
Vue.use(Vuex);
Vue.use(require('vue-wechat-title'));
VueClipboard.config.autoSetContainer = true // add this line
Vue.use(VueClipboard);

Vue.prototype.GLOBAL = global;
Vue.prototype.EventBus = new Vue();

router.beforeEach((to, from, next) => {
  if((to.meta.authentication == 'login' || to.meta.authentication == 'role')) { // 需要登录
    // let user = window.sessionStorage.getItem('user');
    let user = global.userInfo;
    if(user) {  // 已登录
      next();
    } else {  // 未登录
      if(util.browser.versions.vanmai) {  // 自有APP环境
        user = vanmai.getUserInfo();
        if(user) {
          window.sessionStorage.setItem('user', user);
          global.userInfo = user;
        } else {
          vanmai.login();
        }
        next();
      } else {  // 非自有APP环境，跳转到登录页面
        next({name: 'UserLogin'});
      }
    }
  } else {
    next();
  }
});

Vue.http.interceptors.push((request, next) => {
  // ...
  // 请求发送前的处理逻辑
  // ...
  NProgress.start();

  let accessToken = '';
  let user = window.sessionStorage.getItem('user');
  if(user) {
    user = JSON.parse(user);
    accessToken = user.access_token ? user.access_token : '';
  }
  request.headers.set('access-token', accessToken);

  next((response) => {
    // ...
    // 请求发送后的处理逻辑
    // ...
    // 根据请求的状态，response参数会返回给successCallback或errorCallback
    NProgress.done();
    return response;
  })
});

/* eslint-disable no-new */
Vue.filter("timeFormat", function(value, format){
  return util.dateFormat(value, format);
});
Vue.filter("defaultVal", function(value, defaultValue){
  return (typeof value == 'undefined' || value == null || value.length == 0) ? defaultValue : value;
});

Vue.component(
  'remote-js', {
    render(createElement) {
      const self = this;
      return createElement('script', {
        attrs: {
          type: 'text/javascript',
          src: this.src
        },
        on: {
          load: function (event) {
            self.$emit('load', event);
          },
          error: function (event) {
            self.$emit('error', event);
          },
          readystatechange: function (event) {
            if (this.readyState == 'complete') {
              self.$emit('load', event);
            }
          }
        }
      });
    },
    props: {
      src: {type: String, required: true},
    },
  }
);

var vm = new Vue({
  el: '#app',
  router,
  store,
  // render: h => h(App),
  template: '<App/>',
  components: {App}
})
