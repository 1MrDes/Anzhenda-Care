<template>
  <div class="login-page" :style="{backgroundImage: bgImage}">
    <div class="login-box">
      <div class="login-box-body">
        <div class="login-logo">
          <img :src="logo">
        </div>
        <el-form ref="form-login" :model="user" :rules="rules">
          <el-form-item prop="username">
            <el-input placeholder="账号" v-model="user.username" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="password">
            <el-input placeholder="请输入密码" type="password" v-model="user.password" size="small"
                      @keyup.enter.native="login('form-login')"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="login('form-login')" style="width: 100%;">登录</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
  </div>
</template>
<script>
import {BASE_PATH, loginUri} from '../../common/api';
import navigations from '../../common/navigations';

const logo = require('../../assets/images/logo.png');

export default {
  data() {
    return {
      logo: logo,
      version: {version: '0.0.0', release: ''},
      user: {username: '', password: ''},
      admin: null,
      rules: {
        username: [
          {required: true, message: '请输入账号', trigger: 'blur'}
        ],
        password: [
          {required: true, message: '请填写密码', trigger: 'blur'},
          {type: 'string', min: 6, max: 20, message: '密码长度不能小于6位', trigger: 'blur'}
        ]
      },
      bgImage: ''
    }
  },
  methods: {
    login(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          let self = this;
          this.$http.post(loginUri, self.user, {emulateJSON: true}).then(response => {
            let {msg, code, data} = response.body;
            if (code != 0) {
              self.$message.error(msg);
            } else {
              self.admin = data.user;
              self.navigationHandler(data.user.resources);
              self.GLOBAL.userInfo = self.admin;
              self.$router.replace({name: 'Main'});
            }
          }, response => {
            self.$message.error('发生错误');
          });
        } else {
          this.$message.error('请按提示输入');
        }
      })
    },
    navigationHandler(authorizedResources) {
      if (this.admin.is_super == 0) {
        for (let i = 1; i < navigations.length; i++) {
          if (navigations[i].url != null) {
            navigations[i].enable = false;
            let pathinfo = navigations[i].url.split('/');
            for (let ii = 0; ii < authorizedResources.length; ii++) {
              if (authorizedResources[ii] == '/' + pathinfo[1] + '/*'
                // || authorizedResources[ii] == '/' + pathinfo[1] + '/' + pathinfo[2] + '/*'
                || authorizedResources[ii] == navigations[i].url) {
                navigations[i].enable = true;
                break;
              }
            }
          }
          if (navigations[i].subMenus != null) {
            let enableSubMenus = 0;
            for (let m = navigations[i].subMenus.length - 1; m >= 0; m--) {
              if (navigations[i].subMenus[m].url != null) {
                navigations[i].subMenus[m].enable = false;
                let pathinfo = navigations[i].subMenus[m].url.split('/');
                for (let ii = 0; ii < authorizedResources.length; ii++) {
                  if (authorizedResources[ii] == '/' + pathinfo[1] + '/*'
                    // || authorizedResources[ii] == '/' + pathinfo[1] + '/' + pathinfo[2] + '/*'
                    || authorizedResources[ii] == navigations[i].subMenus[m].url) {
                    navigations[i].subMenus[m].enable = true;
                    enableSubMenus++;
                    break;
                  }
                }
              }
            }
            if (enableSubMenus == 0) {
              navigations[i].enable = false;
//                navigations.splice(i, 1);
            }
          }
        }
      }
      this.GLOBAL.navigations = navigations;
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
    this.bgImage = 'linear-gradient(' + this.randomNum(45, 150) + 'deg, ' + this.randomColor() + ', ' + this.randomColor() + ')';
  }
}
</script>
<style scoped>
.login-page {
  min-height: 100%;
  min-width: 100%;
  background-color: #d2d6de;
  background-size: cover;
  position: absolute;
}

.login-page .login-box {
  width: 360px;
  margin: 7% auto
}

@media (max-width: 768px) {
  .login-page .login-box {
    width: 90%;
    margin-top: 20px
  }
}

.login-page .login-box-body {
  background-color: rgba(255, 255, 255, 0.75);
  border-radius: 3px;
  box-shadow: 0 0 50px rgba(0, 0, 0, 0.2);
  padding: 20px;
  border-top: 0;
  color: #666
}

.login-page .login-box-body .login-logo {
  margin: 10px 10px;
  text-align: center;
}

.login-page .login-box-body .login-logo img {
  height: 150px;
  width: 150px;
  border-radius: 75px;
}

.login-page .footer {
  color: #eee;
  position: fixed;
  bottom: 0px;
  text-align: center;
  width: 100%;
  /*background: #444;*/
  background: rgba(0, 0, 0, 0.3);
  padding: 10px;
}
</style>
