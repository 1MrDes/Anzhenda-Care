<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2019/11/12
 * @description
 -->
<template>
  <section>
    <div class="flex flex-col flex-center bg-color-white" ref="loginOuter" :style="{backgroundImage: bgImage}">
      <div class="flex flex-col login-wrapper">
        <div class="flex flex-row flex-center mt10">
          <img :src="logo" class="logo"/>
        </div>
        <div class="flex flex-col mt10">
          <div class="flex flex-row flex-center">
            <input type="text" class="el-input" v-model="userName" placeholder="请输入用户名" />
          </div>

          <div class="flex flex-row flex-center mt10">
            <input type="password" class="el-input" v-model="password" placeholder="请输入密码" />
          </div>

          <div class="flex flex-row flex-centerV flex-left pd10 color-white">
            <van-checkbox v-model="remember"><span class="color-white">记住账号</span></van-checkbox>
          </div>
        </div>

        <div class="flex flex-row flex-center mt10">
          <van-button color="#EE3D11" size="large" round :loading="submitting" loading-text="登录中" @click="login">登录
          </van-button>
        </div>
      </div>
    </div>
  </section>
</template>
<script>
  import {userLoginUri} from '../../common/api';

  const logo = require('../../assets/images/logo.png');

  export default {
    data() {
      return {
        logo: logo,
        userName: '',
        password: '',
        clientHeight: '',
        submitting: false,
        remember: false,
        bgImage: ''
      }
    },
    watch: {
      // 如果 `clientHeight` 发生改变，这个函数就会运行
      clientHeight: function () {
        this.changeFixed(this.clientHeight)
      }
    },
    methods: {
      changeFixed(clientHeight) {
        this.$refs.loginOuter.style.height = clientHeight + 'px';
      },
      login() {
        if (this.userName.length == 0 || this.password.length == 0) {
          this.$toast('用户名和密码不能为空');
          return
        }
        const that = this;
        const url = userLoginUri + '?username=' + encodeURIComponent(this.userName) + '&password=' + this.password;
        this.$http.get(url).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            if(that.remember) {
              window.localStorage.setItem('access_token', data.user.access_token);
            } else {
              window.localStorage.removeItem('access_token');
            }
            that.GLOBAL.userInfo = data.user;
            that.EventBus.$emit('OnUserLogined', null);
            that.$router.push({name: 'Main'});
          }
        }, response => {
          // error callback
        })
      },
      //生成从minNum到maxNum的随机数
      randomNum: function (minNum, maxNum) {
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
      randomColor: function () {
        let col = "#";
        for (let i = 0; i < 6; i++) {
          col += parseInt(Math.random() * 16).toString(16)
        }
        return col;
      }
    },
    mounted: function () {
      // 获取浏览器可视区域高度
      this.clientHeight = `${document.documentElement.clientHeight}`          //document.body.clientWidth;
      window.onresize = function temp() {
        this.clientHeight = `${document.documentElement.clientHeight}`;
      };

      this.bgImage = 'linear-gradient(' + this.randomNum(45, 150) + 'deg, ' + this.randomColor() + ', ' + this.randomColor() + ')';
    }
  }
</script>
<style scoped>
  .el-input {
    width: 100%;
    height: 44px;
    line-height: 22px;
    color: #FFFFFF;
    border-top: none;
    border-left: none;
    border-right: none;
    border-bottom: 1px solid #FFFFFF;
    background: transparent;
  }
  .login-wrapper {
    width: 80%;
  }

  .login-wrapper .logo {
    height: 90px;
    width: 90px;
    border-radius: 45px;
  }
</style>
