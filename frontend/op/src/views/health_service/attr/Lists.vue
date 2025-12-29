<template>
  <section>
    <van-sticky>
      <PageHeader title="服务属性"/>
    </van-sticky>

    <div class="pd10">
      <van-button type="primary"
                  class="w100"
                  @click="$router.push({name: 'HealthServiceAttrAdd', params: {typeId: $route.params.typeId}})">+添加属性</van-button>
    </div>

    <van-list
      class="mt10 bg-color-white"
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="requestData">
      <van-row class="bg-color-gray-light pt10 pb10 cate-item">
        <van-col :span="6" class="pl10 text-left strong">名称</van-col>
        <van-col :span="6" class="pr10 text-right strong">输入方式</van-col>
        <van-col :span="7" class="pr10 text-right strong">可选值</van-col>
        <van-col :span="5" class="pr10 text-right strong">操作</van-col>
      </van-row>

      <van-row v-for="(item, index) in dataList" :key="index" class="pt10 pb10 border-top-gray cate-item">
        <van-col :span="6" class="pl10 text-left">{{item.name}}</van-col>
        <van-col :span="6" class="pr10 text-right strong">
          <span v-if="item.input_type==1">手工录入</span>
          <span v-else-if="item.input_type==2">从列表选择</span>
          <span v-else-if="item.input_type==3">多行文本</span>
        </van-col>
        <van-col :span="7" class="pl10 text-left">{{item.values}}</van-col>
        <van-col :span="5" class="pr10 text-right">
          <van-icon name="edit" @click="$router.push({name: 'HealthServiceAttrEdit', params: {typeId: typeId, id: item.id}})" />
          <van-icon name="delete" class="ml10" @click="del(index)" />
        </van-col>
      </van-row>
    </van-list>
  </section>
</template>

<script>
  import Vue from 'vue';
  import {Toast, Dialog} from 'vant';
  import {goodsAttrListsUri, goodsAttrDeleteUri} from "../../../common/api";
  import PageHeader from "../../componets/PageHeader";
  Vue.use(Toast).use(Dialog);

  export default {
    components: {
      PageHeader
    },
    data() {
      return {
        typeId: 0,
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
      del(index) {
        const that = this;
        Dialog.confirm({
          message: '确定要删除吗？'
        }).then(() => { // on confirm
          that.$http.get(goodsAttrDeleteUri + '?id=' + that.dataList[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              Toast.fail(msg);
            } else {
              that.dataList.splice(index, 1);
            }
          }, response => {
            // error callback
          })
        }).catch(() => {  // on cancel

        });
      },
      requestData() {
        const that = this;
        this.loading = true;
        const url = goodsAttrListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage + '&type_id=' + this.$route.params.typeId;
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
      this.typeId = this.$route.params.typeId;
    }
  }
</script>

<style scoped>

</style>
