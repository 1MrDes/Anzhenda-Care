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
import VueClipboard from 'vue-clipboard2'
import Loading from 'wc-loading';
import 'wc-loading/style.css';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import './assets/css/common.css';
import './assets/css/iconfont/iconfont.css';

import global from './common/global';

Vue.config.productionTip = false;
Vue.use(resource);
Vue.use(Vuex);
Vue.use(require('vue-wechat-title'));
VueClipboard.config.autoSetContainer = true // add this line
Vue.use(VueClipboard);
Vue.use(ElementUI);
Vue.use(Loading);

Vue.prototype.GLOBAL = global;

router.beforeEach((to, from, next) => {
  const user = Vue.prototype.GLOBAL.userInfo;
  if(!user && (to.meta.authentication == 'login' || to.meta.authentication == 'role')) {
    next({name: 'UserLogin'});
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
  const user = Vue.prototype.GLOBAL.userInfo;
  if(user) {
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
Vue.filter("dateDiff", function(value){
  return util.dateDiff(value * 1000);
});
Vue.filter("substr", function(value, start, len){
  return value.substr(start, len);
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
