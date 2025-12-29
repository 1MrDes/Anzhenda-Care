<template>
  <van-tabbar v-model="currentTabItem">
    <van-tabbar-item name="home" icon="wap-home-o" :to="{name: 'Main'}">首页</van-tabbar-item>
    <van-tabbar-item name="goods" icon="balance-list-o" :to="{name: 'HealthServiceLists'}">服务</van-tabbar-item>
    <van-tabbar-item name="order" icon="hot-o" :to="{name: 'HealthServiceOrderLists'}">订单</van-tabbar-item>
    <van-tabbar-item name="setting" icon="setting-o" :to="{name: 'SettingIndex'}">设置</van-tabbar-item>
  </van-tabbar>
</template>

<script>
    import Vue from 'vue';
    import { Tabbar, TabbarItem } from 'vant';
    Vue.use(Tabbar).use(TabbarItem);
    export default {
        name: 'Tabbar',
        components: {
            [Tabbar.name]: Tabbar,
            [TabbarItem.name]: TabbarItem
        },
        props: {
            active: {
                type: String,
                default: 'home'
            }
        },
        computed: {
            currentTabItem: {
              get: function () {
                return this.active;
              },
              set: function (newVal) {

              }
            }
        },
        data () {
          return {}
        },
        methods: {

        },
        mounted: function () {

        }
    };
</script>
