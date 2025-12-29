<template>
  <section>
    <van-sticky>
      <PageHeader title="广告管理"/>
    </van-sticky>

    <div class="pd10">
      <van-button type="primary" class="w100" @click="$router.push({name: 'AdvAdd'})">+添加</van-button>
    </div>

    <div class="mt10">
      <van-dropdown-menu>
        <van-dropdown-item v-model="positionId" :options="positions" @change="onPositionChange" />
      </van-dropdown-menu>
    </div>

    <van-list
      class="mt10"
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="requestData">
      <van-row class="list-item bg-color-white">
        <van-col :span="21" class="pl10 text-left strong">标题</van-col>
        <van-col :span="3" class="pr10 text-right strong">操作</van-col>
      </van-row>

      <van-row v-for="(item, index) in dataList" :key="index" class="list-item bg-color-white">
        <van-col :span="21" class="pl10 text-left">{{item.title}}</van-col>
        <van-col :span="3" class="pr10 text-right">
          <van-icon name="edit" @click="$router.push({name: 'AdvEdit', params: {id: item.id}})" />
          <van-icon name="delete" class="ml10" @click="del(index)" />
        </van-col>
      </van-row>
    </van-list>
  </section>
</template>

<script>
  import {advPositionAllUri, advListsUri, advDeleteUri} from "../../common/api";
  import PageHeader from "../componets/PageHeader";
  import Vue from 'vue';
  import {Dialog} from 'vant';
  Vue.use(Dialog);

  export default {
    components: {
      PageHeader
    },
    data() {
      return {
        positions: [],
        positionId: 0,
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
      onPositionChange(value) {
        this.positionId = value;
        this.currentPage = 1;
        this.lastPage = 0;
        this.total = 0;
        this.loading = false;
        this.finished = false;
        this.dataList = [];
      },
      getPositions() {
        const that = this;
        this.$http.get(advPositionAllUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            that.positions.push({text: '选择广告位', value: 0});
            for (let i = 0; i < data.positions.length; i++) {
              that.positions.push({text: data.positions[i].name, value: data.positions[i].id});
            }
          }
        }, response => {
          // error callback
        })
      },
      del(index) {
        const that = this;
        Dialog.confirm({
          message: '确定要删除吗？'
        }).then(() => { // on confirm
          that.$http.get(advDeleteUri + '?id=' + that.dataList[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              that.$toast.fail(msg);
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
        const url = advListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage + '&position_id=' + this.positionId;
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
      this.getPositions();
    }
  }
</script>

<style scoped>

</style>
