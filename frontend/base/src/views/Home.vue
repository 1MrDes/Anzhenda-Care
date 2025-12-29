<template>
  <div class="wrapper" :class="{hasSecondNav : hasSecondNav}" type="flex" justify="start">
    <aside class="first-nav">
      <div class="logo"><img src="../assets/images/logo.png" /></div>
      <ul class="sidebar-menu">
        <li v-for="(menu, index) in mainMenus" :key="index" v-bind:class="{'active' : index==activeFirstNav}" v-if="menu.enable">
          <a href="javascript:;" @click="onFirstNavClicked(index)">
            <i class="icon iconfont" v-bind:class="menu.icon" /> <span>{{ menu.name }}</span>
          </a>
        </li>
      </ul>
    </aside>
    <aside class="second-nav" v-show="hasSecondNav">
      <div class="first-nav-name">{{ firstNavName }}</div>
      <ul class="sidebar-menu">
        <li v-for="(menu, index) in subMenus" :key="index" v-bind:class="{'active' : index==activeSecondNav}" v-if="menu.enable">
          <a href="javascript:;" @click="onSecondNavClicked(index)">
            {{ menu.name }}
          </a>
        </li>
      </ul>
    </aside>
    <div class="main">
      <section class="content-container">
        <header>
          <el-row>
            <el-col :span="12">
              <el-breadcrumb separator="/">
                <el-breadcrumb-item>{{ firstNavName }}</el-breadcrumb-item>
                <el-breadcrumb-item v-if="secondNavName!=''">{{ secondNavName }}</el-breadcrumb-item>
              </el-breadcrumb>
            </el-col>
            <el-col :span="12">
              <el-dropdown class="fr">
                <span class="el-dropdown-link">
                  {{ user ? (user.real_name ? user.real_name : user.username) : '' }}<i class="el-icon-caret-bottom el-icon--right"></i>
                </span>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item><a href="javascript:;" @click="password">修改密码</a></el-dropdown-item>
                  <el-dropdown-item><a href="javascript:;" @click="logout">退出</a></el-dropdown-item>
                </el-dropdown-menu>
              </el-dropdown>
            </el-col>
          </el-row>
        </header>
        <section class="content-wrapper">
          <transition name="fade" mode="out-in">
            <router-view></router-view>
          </transition>
        </section>
      </section>
    </div>
  </div>
</template>

<script>
  import {logoutUri} from '../common/api';
  import navigations from '../common/navigations';
  export default {
    data () {
      return {
        mainMenus: [],
        subMenus: null,
        hasSecondNav: false,
        activeFirstNav : 0,
        activeSecondNav : 0,
        firstNavName: '',
        secondNavName: '',
        user: null
      }
    },
    mounted: function () {
      this.user = this.GLOBAL.userInfo;
      this.mainMenus = this.GLOBAL.navigations;
      this.activeFirstNav = this.GLOBAL.homeActiveFirstNav;
      this.activeSecondNav = this.GLOBAL.homeActiveSecondNav;
      if(this.activeFirstNav == null) {
        this.activeFirstNav = 0;
      }
      if(this.activeSecondNav == null) {
        this.activeSecondNav = 0;
      }
      this.subMenus = this.mainMenus[this.activeFirstNav].subMenus;
      if (this.subMenus != null) {
        this.hasSecondNav = true;
      } else {
        this.hasSecondNav = false;
      }

      this.setFirstNavName();
      this.setSecondNavName();
    },
    beforeMount: function () {
//      console.log('Home beforeMount 钩子执行...');
    },
    beforeUpdate: function () {
//      console.log('Home beforeUpdate 钩子执行...')
    },
    updated: function () {
//      console.log('Home updated 钩子执行...')
    },
    methods: {
      onFirstNavClicked: function (index) {
        this.activeFirstNav = index;
        this.firstNavName = this.mainMenus[index].name;
        this.subMenus = this.mainMenus[index].subMenus;
        this.activeSecondNav = 0;
        if (this.subMenus != null) {
          this.hasSecondNav = true;
          for (let i = 0; i < this.mainMenus[index].subMenus.length; i++) {
            if(this.mainMenus[index].subMenus[i].enable) {
              this.$router.push(this.mainMenus[index].subMenus[i].url);
              break;
            }
          }
        } else {
          this.hasSecondNav = false;
          this.$router.push(this.mainMenus[index].url);
        }
      },
      onSecondNavClicked: function (index) {
        this.activeSecondNav = index;
        this.$router.push(this.subMenus[index].url);
      },
      logout: function () {
        let self = this;
        self.$http.get(logoutUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.GLOBAL.userInfo = null;
            self.GLOBAL.navigations = [];
            self.GLOBAL.homeActiveFirstNav = null;
            self.GLOBAL.homeActiveSecondNav = null;
            self.$router.replace({name: 'UserLogin'});
          }
        }, response => {
          // error callback
        })
      },
      password: function () {
        this.activeFirstNav = 0;
        this.hasSecondNav = false;
        this.$router.push({name: 'AdminPassword'});
      },
      setFirstNavName: function () {
        this.firstNavName = this.mainMenus[this.activeFirstNav].name;
      },
      setSecondNavName: function () {
        if(this.mainMenus[this.activeFirstNav].subMenus != null) {
          this.secondNavName = this.mainMenus[this.activeFirstNav].subMenus[this.activeSecondNav].name;
        } else {
          this.secondNavName = '';
        }
      }
    },
    watch: {
      activeFirstNav: function (newVal) {
        this.GLOBAL.homeActiveFirstNav = newVal;
        this.setFirstNavName();
      },
      activeSecondNav: function (newVal) {
        this.GLOBAL.homeActiveSecondNav = newVal;
        this.setSecondNavName();
      }
    }
  }
</script>

<style scoped>
  .wrapper {
    width: 100%;
  }

  .first-nav {
    width: 120px;
    background-color: #2c3e50;
    min-height: 100%;
    position: fixed;
    top: 0;
    left: 0;
    color: #ffffff;
  }

  .second-nav {
    width: 120px;
    background-color: #ffffff;
    min-height: 100%;
    position: fixed;
    top: 0;
    left: 120px;
  }

  .main {

    padding: 0 0 0 120px;
    min-height: 100%;
  }

  .hasSecondNav .main{padding: 0 0 0 240px;}
  .main .content-container header{ padding: 10px 10px 10px 10px; font-weight: bold; background: #ffffff;}
  .main .content-wrapper{ margin: 10px 10px 0 10px;}

  .first-nav .logo {
    width: 100%;
    height: auto;
    text-align: center;
    margin-top: 10px;
  }
  .first-nav .logo img{ height: 32px; width: 32px; border-radius: 16px;}

  .first-nav .sidebar-menu {
    margin-top: 10px;
  }

  .first-nav .sidebar-menu > li {
    transition: border-left-color 0.3s ease 0s;
  }

  .first-nav .sidebar-menu > li.active {
    background: #ffffff;
  }

  .first-nav .sidebar-menu > li > a {
    display: block;
    padding: 12px 5px 12px 15px;
  }

  .first-nav .sidebar-menu > li > a {
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
  }

  .first-nav .sidebar-menu > li.active a {
    color: #20A0FF;
  }

  .first-nav .sidebar-menu > li > a:hover {
    background: #ffffff;
    color: #20A0FF;
  }
  .second-nav .first-nav-name{ font-size: 14px; font-weight: bold; padding: 15px 0; text-align: center; border-bottom: 1px solid #ECF0F5;}
  .second-nav .sidebar-menu > li.active {
    background: #ECF0F5;
  }
  .second-nav .sidebar-menu > li > a {
    display: block;
    padding: 12px 5px 12px 15px;
    font-size: 13px; font-weight: 400;
  }
  .second-nav .sidebar-menu > li > a:hover {
    background: #ECF0F5;
    color: #20A0FF;
  }
</style>
