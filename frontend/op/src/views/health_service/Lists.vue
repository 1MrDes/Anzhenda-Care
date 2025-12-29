<template>
  <section>
    <van-sticky>
      <PageHeader title="服务"/>
    </van-sticky>

    <van-tabs v-model:active="activeTabName" sticky @click="onTabClick">
      <van-tab title="服务" name="service">
        <div class="pd10">
          <van-button type="primary" class="w100" @click="$router.push({name: 'HealthServiceAdd'})">+添加服务</van-button>
        </div>

        <van-list
          class="mt10 goods-list"
          v-model="loading"
          :finished="finished"
          finished-text="没有更多了"
          @load="requestData">
          <vxe-table
            :data="dataList"
            :scroll-y="{enabled: false}">
            <vxe-column field="name" title="名称"></vxe-column>

            <vxe-column field="on_sale" title="上架" width="60">
              <template #default="{ row, rowIndex }">
                <van-switch v-model="row.on_sale" size="20" :active-value="rowIndex + '_' + 1" :inactive-value="rowIndex + '_' + 0" @change="onSaleStatusChange" />
              </template>
            </vxe-column>

            <vxe-column field="is_deleted" title="删除" width="60">
              <template #default="{ row, rowIndex }">
               <span>{{row.is_deleted ? 'Y' : 'N'}}</span>
              </template>
            </vxe-column>

            <vxe-column field="sort_order" title="排序" width="60"></vxe-column>

            <vxe-column field="opt" title="操作" width="120">
              <template #default="{ row, rowIndex }">
                <van-icon name="edit" @click="edit(rowIndex)"/>
                <van-icon name="delete" class="ml10" @click="del(rowIndex)"/>
              </template>
            </vxe-column>
          </vxe-table>
        </van-list>
      </van-tab>
      <van-tab title="服务分类" name="category"></van-tab>
      <van-tab title="服务类型" name="type"></van-tab>
    </van-tabs>

    <div class="empty-box"></div>
    <Tabbar active="goods"/>
  </section>
</template>

<script>
  import Vue from 'vue';
  import {goodsDeleteUri, goodsListsUri, goodsToggleSaleStatusUri} from '../../common/api';
  import {Dialog} from 'vant';
  import Tabbar from "../componets/Tabbar";
  import PageHeader from "../componets/PageHeader";
  Vue.use(Dialog);

  export default {
    components: {
      Tabbar: Tabbar,
      PageHeader: PageHeader
    },
    data() {
      return {
        activeTabName: 'service',
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
      onTabClick(name) {
        if(name == 'category') {
          this.$router.replace({name: 'HealthServiceCategoryLists'});
        } else if(name == 'type') {
          this.$router.replace({name: 'HealthServiceTypeLists'});
        }
      },
      onSaleStatusChange(value) {
        const values = value.split('_');
        const index = values[0];
        const saleStatus = values[1];
        const that = this;
        const url = goodsToggleSaleStatusUri + '?id=' + that.dataList[index].id + '&sale_status=' + saleStatus;
        that.$http.get(url).then(response => {
          let {code, msg, data} = response.body;
          if (code != 0) {
            that.$toast.fail(msg);
          } else {

          }
        }, response => {
          // error callback
        })
      },
      edit(index) {
        const id = this.dataList[index].id;
        this.$router.push({name: 'HealthServiceEdit', params: {id: id}});
      },
      del(index) {
        const that = this;
        Dialog.confirm({
          message: '确定要删除吗？'
        }).then(() => { // on confirm
          that.$http.get(goodsDeleteUri + '?id=' + that.dataList[index].id).then(response => {
            let {code, msg, data} = response.body;
            if (code != 0) {
              that.$toast.fail(msg);
            } else {
              // let health_service  = that.dataList[index];
              // health_service.is_deleted = 1;
              // that.$set(that.dataList, index, health_service);
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
        const url = goodsListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
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
              data.data[i].on_sale = i + '_' + data.data[i].on_sale;
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

  }
</style>
