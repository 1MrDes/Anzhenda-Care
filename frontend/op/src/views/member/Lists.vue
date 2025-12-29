<template>
  <section>
    <van-sticky>
      <PageHeader title="用户管理"/>
    </van-sticky>

    <form action="/">
      <van-search
        v-model="keywords"
        placeholder="请输入搜索关键词"
        @search="onSearch"
      />
    </form>

    <van-list
      class="mt10"
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="requestData">
      <div class="flex flex-col bg-color-white mb10 pd10 font-common record-item" v-for="(item, index) in dataList" :key="index">
        <div class="flex flex-row flex-centerV flex-left pd10">
          <img :src="item.avatar_url" class="avatar" />
          <span class="ml5">{{item.nick}}</span>
        </div>

        <div class="flex flex-row flex-centerV flex-left border-bottom-gray pt5 pb5">
          手机号：<a :href="'tel:'+item.mobile">{{item.mobile}}</a>
        </div>

        <div class="flex flex-row flex-centerV flex-left border-bottom-gray pt5 pb5">
          余额：{{item.balance}}元，可提现：{{item.withdraw_balance}}元，提现中：{{item.withdrawing_balance}}元
        </div>

        <div class="flex flex-row flex-centerV flex-left border-bottom-gray pt5 pb5">
          支付宝：{{item.alipay_name|defaultVal('N/A')}}({{item.alipay_email|defaultVal('N/A')}})
        </div>

        <div class="flex flex-row flex-left pt5 pb5">
          注册：{{item.register_time|timeFormat('yyyy-MM-dd hh:mm:ss')}}
        </div>

        <div class="flex flex-row flex-left pt5 pb5">
          最后登录：{{item.last_login_time|timeFormat('yyyy-MM-dd hh:mm:ss')}}({{item.last_ip}})
        </div>

      </div>
    </van-list>

    <Tabbar active="member"/>
  </section>
</template>

<script>
  import Vue from 'vue';
  import {Toast, Dialog, Row, Col, List, Search} from 'vant';
  import {memberListsUri} from "../../common/api";
  import PageHeader from "../componets/PageHeader";
  import Tabbar from "../componets/Tabbar";
  Vue.use(Toast).use(Dialog).use(Row).use(Col).use(List).use(Search);

  export default {
    components: {
      PageHeader,
      Tabbar
    },
    data() {
      return {
        currentPage: 1,
        lastPage: 0,
        total: 0,
        pageSize: 10,
        loading: false,
        finished: false,
        dataList: [],
        keywords: ''
      }
    },
    methods: {
      onSearch() {
        this.currentPage = 1;
        this.lastPage = 0;
        this.total = 0;
        this.loading = false;
        this.finished = false;
        this.dataList = [];
      },
      requestData() {
        const that = this;
        this.loading = true;
        const url = memberListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage + '&keywords=' + encodeURIComponent(that.keywords);
        this.$http.get(url).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast.fail(msg);
          } else {
            that.loading = false;
            if (that.currentPage >= data.last_page) {
              that.finished = true;
            }
            that.currentPage = that.currentPage + 1;
            for (let i = 0; i < data.data.length; i++) {
              that.dataList.push(data.data[i]);
            }
            that.total = data.total;
            that.lastPage = data.last_page;
          }
        }, response => {
          // error callback
        })
      }
    },
    mounted: function () {

    }
  }
</script>

<style scoped>
  .record-item .avatar {
    height: 30px;
    width: 30px;
    border-radius: 15px;
  }
</style>
