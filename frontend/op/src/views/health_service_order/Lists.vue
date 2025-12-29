<template>
  <section>
    <van-sticky>
      <PageHeader title="订单管理"/>
    </van-sticky>

    <van-tabs v-model="activeTabName" sticky @click="onTabClick">
      <van-tab :title="tab.label" :name="tab.id" v-for="(tab, tabindex) in tabs" :key="tabindex">
        <template v-if="activeTabName==tab.id">
          <van-list
            class="mt10"
            v-model="loading"
            :finished="finished"
            finished-text="没有更多了"
            @load="requestData">
            <div class="flex flex-col bg-color-white mt20 font-common color-drak order-item"
                 v-for="(item, index) in dataList"
                 :key="index"
                  @click="$router.push({name: 'HealthServiceOrderDetail', params: {sn: item.order_sn}})">
              <div class="flex flex-row pt20 pb10">
                <div class="flex flex-row flex-2 flex-left pl20">
                  <label>订单号：{{item.order_sn}}</label>
                </div>

<!--                <div class="flex row flex-1 right pr20">-->
<!--                  <van-button round type="info" size="mini" catchtap='onCopy' data-content="{{item.order_sn}}">复制</van-button>-->
<!--                </div>-->
              </div>

              <div class="flex flex-row pt10 pb20 border-top-gray">
                <div class="flex flex-row flex-2 flex-left pl20">
                  <label>订单日期：{{item.create_time|timeFormat('yyyy-MM-dd hh:mm:ss')}}</label>
                </div>

                <div class="flex flex-row flex-1 flex-right pr20">
                  <label class="color-green">{{item.order_status_label}}</label>
                </div>
              </div>

              <div class="flex flex-row pt10 pb20 border-top-gray order-items" v-for="(orderItem, idx) in item.items"  :key="idx">
                <div class="flex flex-row flex-center cl pl20">
                  <img :src="orderItem.health_service_image_url" class="image" />
                </div>

                <div class="flex flex-1 flex-col cl pl20">
                  <label>{{orderItem.health_service_name}}</label>
                </div>

                <div class="flex flex-col flex-center cl pl20 pr20">
                  <div class="flex flex-row flex-right w100">
                    <label class="color-red">￥{{orderItem.health_service_price}}</label>
                  </div>
                  <div class="flex flex-row flex-right w100">
                    <label class="color-gray">X{{orderItem.quantity}}</label>
                  </div>
                </div>
              </div>

              <div class="flex flex-row flex-right pt10 pb20 pr20 border-top-gray">
                <label class="color-gray">共计{{item.quantity}}件，</label>
                <label class="color-red">合计：￥{{item.order_money}}</label>
              </div>
            </div>
          </van-list>
        </template>
      </van-tab>
    </van-tabs>

    <Tabbar active="order"/>
  </section>
</template>

<script>
  import {orderListsUri} from "../../common/api";
  import Tabbar from '../componets/Tabbar';
  import PageHeader from "../componets/PageHeader";

  export default {
    components: {
      Tabbar: Tabbar,
      PageHeader: PageHeader
    },
    data() {
      return {
        activeTabName: 'all',
        tabs: [
          {id: 'all', label: '全部'},
          {id: 'unpaied', label: '待付款'},
          {id: 'paied', label: '待发货'},
          {id: 'shipped', label: '待收货'},
          {id: 'finished', label: '已完成'},
          {id: 'refunded', label: '已退款'}
        ],

        currentPage: 1,
        lastPage: 0,
        total: 0,
        pageSize: 10,
        loading: false,
        finished: false,
        dataList: []
      }
    },
    methods: {
      onTabClick(name, title) {
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
        const url = orderListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage + '&status=' + this.activeTabName;
        this.$http.get(url).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
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
  .order-item .image {
    height: 60px;
    width: 60px;
  }
</style>
