<template>
  <section>
    <div class="pd10">
      <van-search v-model="keywords" placeholder="请输入搜索关键词" @search="onSearch"/>
    </div>

    <van-list
      class="mt10 goods-list"
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="requestData">
      <van-checkbox-group v-model="checkedGoodsList">
      <van-row class="goods-item bg-color-white">
        <van-col :span="3" class="text-left strong">&nbsp;</van-col>
        <van-col :span="21" class="pl10 text-left strong">名称</van-col>
      </van-row>

      <van-row v-for="(item, index) in dataList" :key="index" class="goods-item bg-color-white">
        <van-col :span="3" class="text-left strong">
          <van-checkbox :name="index">&nbsp;</van-checkbox>
        </van-col>
        <van-col :span="21" class="pl10 text-left">{{ item.name }}</van-col>
      </van-row>

      </van-checkbox-group>
    </van-list>

    <div class="empty-box"></div>
    <div class="footer">
      <van-button type="primary" block @click="onConfirm">确 定</van-button>
    </div>
  </section>
</template>

<script>
import {goodsListsUri} from '../../common/api';

export default {
  data() {
    return {
      currentPage: 1,
      lastPage: 0,
      total: 0,
      pageSize: 10,
      loading: false,
      finished: false,
      dataList: [],
      keywords: '',
      checkedGoodsList: []
    }
  },
  methods: {
    onConfirm() {
      let goodsList = [];
      for (let i = 0; i < this.checkedGoodsList.length; i++) {
        const goods = this.dataList[this.checkedGoodsList[i]];
        goodsList.push({
          id: goods.id,
          name: goods.name,
          default_image_url: goods.default_image_url,
          sale_price: goods.sale_price
        });
      }
      // window.sessionStorage.setItem('checked_goods', JSON.stringify(goodsList));
      // this.EventBus.$emit('OnGoodsSelect', goodsList);
      this.$store.commit('addCheckedGoods', goodsList);
      this.$router.back();
    },
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
      const url = goodsListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage + '&keywords=' + encodeURIComponent(this.keywords);
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
  .goods-item {
    padding: 10px 10px;
  }
  .empty-box {
    height: 80px;
  }
  .footer {
    text-align: center;
    padding: 10px 10px;
    position: fixed;
    bottom: 0px;
    left: 0px;
    right: 0px;
  }
</style>
