<template>
  <section>
    <van-cell-group>
      <van-cell title="系统设置" is-link :to="{name: 'SiteConfig'}" />
    </van-cell-group>

    <van-cell-group class="mt10">
      <van-cell title="广告位" is-link :to="{name: 'AdvPosition'}" />
      <van-cell title="广告" is-link :to="{name: 'AdvLists'}" />
    </van-cell-group>

    <van-cell-group class="mt10">
      <van-cell title="角色" is-link />
      <van-cell title="管理员" is-link />
    </van-cell-group>

    <van-cell-group class="mt10">
      <van-cell title="退出登录" is-link @click="logout" />
    </van-cell-group>

    <Tabbar active="setting" />
  </section>
</template>

<script>
  import Vue from 'vue';
  import {Cell, CellGroup} from 'vant';
  import Tabbar from '../componets/Tabbar';
  Vue.use(Cell).use(CellGroup);

  export default {
    components: {
      Tabbar: Tabbar
    },
    data() {
      return {}
    },
    methods: {
      logout() {
        window.localStorage.removeItem('access_token');
        this.GLOBAL.userInfo = null;
        this.$router.push({name: 'UserLogin'});
      }
    },
    mounted: function () {

    }
  }
</script>

<style scoped>

</style>
