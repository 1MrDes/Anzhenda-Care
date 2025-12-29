<template>
  <div id="app">
<!--    <router-view v-wechat-title="$route.meta.title"></router-view>-->
    <keep-alive>
      <router-view v-wechat-title="$route.meta.title" v-if="$route.meta.keepAlive">
        <!-- 这里是会被缓存的视图组件，比如列表A页面 -->
      </router-view>
    </keep-alive>

    <router-view v-wechat-title="$route.meta.title" v-if="!$route.meta.keepAlive">
      <!-- 这里是不被缓存的视图组件，比如详情B页面-->
    </router-view>

    <van-overlay :show="loadingOverlayVisible">
      <div class="loading-overlay-wrapper">
        <van-loading type="spinner" />
      </div>
    </van-overlay>

  </div>
</template>

<script>
  import {STATIC_BASE_URL, userLoginByTokenUri} from './common/api';
  import Vue from 'vue';
  import {Overlay, Loading, Toast} from 'vant';
  Vue.use(Overlay).use(Loading).use(Toast);

  export default {
    name: 'app',
    data() {
      return {
        STATIC_BASE_URL: STATIC_BASE_URL,
        APP_NAME: APP_NAME,
        loadingOverlayVisible: true,
        subscribeOverlayVisible: false,
        mpQrcode: ''
      }
    },
    methods: {
      loginByToken() {
        const access_token = window.localStorage.getItem('access_token');
        if(!access_token) {
          return;
        }
        const that = this;
        this.$http.get(userLoginByTokenUri + '?access_token=' + access_token).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            // that.toast.fail(msg);
          } else {
            if(data.user) {
              this.GLOBAL.userInfo = data.user;
            }
            that.EventBus.$emit('OnUserLogined', {user: data.user, method: 'loginByToken'});
            that.$router.push({name: 'Main'});
          }
        }, response => {
          // error callback
        })
      }
    },
    beforeCreate: function () {
//      console.log('App beforeCreate 钩子执行...')
    },
    created: function () {
//      console.log('App created 钩子执行...')
      const that = this;

      this.EventBus.$on('OnUserLogout', (data) => {
        this.GLOBAL.userInfo = null;
      });

      if (!this.GLOBAL.userInfo) {
        this.loginByToken();
      }
    },
    beforeMount: function () {
//      console.log('App beforeMount 钩子执行...')
    },
    mounted: function () {
      this.loadingOverlayVisible = false;
      // this.login();
//      console.log('App mounted 钩子执行...')
    },
    beforeUpdate: function () {
//      console.log('App beforeUpdate 钩子执行...')
    },
    updated: function () {
//      console.log('App updated 钩子执行...')
    },
    beforeDestroy: function () {
//      console.log('App beforeDestroy 钩子执行...')
      const that = this;

    },
    destroyed: function () {
//      console.log('App destroyed 钩子执行...')
    }
  }
</script>

<style>
  html, body, #app {
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
    font-size: 16px;
    height: 100%;
  }

  .loading-overlay-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
  }
</style>
