<template>
  <div id="app">
    <router-view v-wechat-title="$route.meta.title"></router-view>
  </div>
</template>

<script>
  import {userLoginByTokenUri} from './common/api';
  import EventBus from "./assets/js/eventBus";

  export default {
    name: 'app',
    data() {
      return {}
    },
    methods: {
      login() {
        const access_token = window.localStorage.getItem('access_token');
        if(!access_token) {
          return;
        }
        const that = this;
        this.$http.get(userLoginByTokenUri + '?access_token=' + access_token).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            alert(msg);
          } else {
            window.sessionStorage.setItem('user', JSON.stringify(data.user));
            window.localStorage.setItem('access_token', data.user.access_token);
            that.GLOBAL.userInfo = data.user;
            EventBus.$emit('OnUserLogined', null);
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
    },
    beforeMount: function () {
//      console.log('App beforeMount 钩子执行...')
    },
    mounted: function () {
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
</style>
