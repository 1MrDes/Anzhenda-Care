<template>
  <van-tabbar v-model="currentTabItem">
    <van-tabbar-item icon="gift-o" :to="{name: 'PlaylistLists'}">歌单</van-tabbar-item>
    <van-tabbar-item icon="setting-o" :to="{name: 'SongRankIndex'}">排行榜</van-tabbar-item>
    <van-tabbar-item icon="setting-o" :to="{name: 'MyIndex'}">我的</van-tabbar-item>
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
                type: Number,
                default: 0
            }
        },
        computed: {
            currentTabItem: function () {
                return this.active;
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
