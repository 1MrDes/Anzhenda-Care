<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2019/11/12
 * @description
 -->
<template>
  <section>

    <van-grid square :column-num="2" style="padding-bottom: 80px;">
      <van-grid-item :to="{name: 'HealthServiceLists'}">
        <van-row class="text-center pt10">
          <van-icon name="friends-o" size="60px"/>
        </van-row>
        <van-row class="text-center pt10 pb10">服务管理</van-row>
      </van-grid-item>

      <van-grid-item :to="{name: 'HealthServiceOrderLists'}">
        <van-row class="text-center pt10">
          <van-icon name="friends-o" size="60px"/>
        </van-row>
        <van-row class="text-center pt10 pb10">订单管理</van-row>
      </van-grid-item>

      <van-grid-item :to="{name: 'WithdrawLists'}">
        <van-row class="text-center pt10">
          <van-icon name="friends-o" size="60px"/>
        </van-row>
        <van-row class="text-center pt10 pb10">提现管理</van-row>
      </van-grid-item>

      <van-grid-item :to="{name: 'MemberLists'}">
        <van-row class="text-center pt10">
          <van-icon name="friends-o" size="60px"/>
        </van-row>
        <van-row class="text-center pt10 pb10">用户管理</van-row>
      </van-grid-item>

      <van-grid-item :to="{name: 'HealthAssistantLists'}">
        <van-row class="text-center pt10">
          <van-icon name="friends-o" size="60px"/>
        </van-row>
        <van-row class="text-center pt10 pb10">陪诊师管理</van-row>
      </van-grid-item>

      <van-grid-item :to="{name: 'ArticleLists'}">
        <van-row class="text-center pt10">
          <van-icon name="friends-o" size="60px"/>
        </van-row>
        <van-row class="text-center pt10 pb10">文章管理</van-row>
      </van-grid-item>

    </van-grid>

    <Tabbar active="home"/>
  </section>
</template>
<script>

import Tabbar from './componets/Tabbar';

export default {
  components: {
    Tabbar: Tabbar
  },
  data() {
    return {

    }
  },
  methods: {

  },
  mounted: function () {

  }
}
</script>
<style scoped>

</style>
