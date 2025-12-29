<template>
  <section>
    <van-sticky>
      <PageHeader title="服务分类"/>
    </van-sticky>

    <van-tabs v-model:active="activeTabName" sticky @click="onTabClick">
      <van-tab title="服务" name="service"></van-tab>
      <van-tab title="服务分类" name="category">
        <div class="pd10">
          <van-button type="primary" class="w100" @click="$router.push({name: 'HealthServiceCategoryAdd'})">+添加分类</van-button>
        </div>

        <div class="bg-color-white cate-list">
          <vxe-table
            :data="categoryList"
            :scroll-y="{enabled: false}">
            <vxe-column type="seq" width="60"></vxe-column>
            <vxe-column field="name" title="名称">
              <template #default="{ row, rowIndex }">
                <span v-bind:style="{paddingLeft: row.level*10 + 'px'}">{{row.name}}</span>
              </template>
            </vxe-column>
            <vxe-column field="sort_order" title="排序" width="60"></vxe-column>
            <vxe-column field="opt" title="操作" width="120">
              <template #default="{ row, rowIndex }">
                <van-icon name="add-o"  @click="$router.push({name: 'HealthServiceCategoryAdd', query: {parent_id: row.id, parent_name: row.name}})" />
                <van-icon name="edit" class="ml10" @click="$router.push({name: 'HealthServiceCategoryEdit', params: {id: row.id}})"/>
                <van-icon name="delete" class="ml10" @click="del(rowIndex)"/>
              </template>
            </vxe-column>
          </vxe-table>
        </div>
      </van-tab>
      <van-tab title="服务类型" name="type"></van-tab>
    </van-tabs>

    <div class="empty-box"></div>
    <Tabbar active="goods"/>
  </section>
</template>

<script>
import Vue from 'vue';
import {Dialog} from 'vant';
import {
  goodsCategoryDeleteUri,
  goodsCategoryTreeUri
} from "../../../common/api";
import Tabbar from "../../componets/Tabbar";
import PageHeader from "../../componets/PageHeader";

Vue.use(Dialog);

export default {
  components: {
    Tabbar: Tabbar,
    PageHeader: PageHeader
  },
  data() {
    return {
      activeTabName: 'category',
      categoryList: [],
    }
  },
  methods: {
    onTabClick(name) {
      if (name == 'service') {
        this.$router.replace({name: 'HealthServiceLists'});
      } else if (name == 'type') {
        this.$router.replace({name: 'HealthServiceTypeLists'});
      }
    },
    getAll() {
      const that = this;
      this.$http.get(goodsCategoryTreeUri).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg);
        } else {
          // let categories = data.categories;
          // for (let k = 0; k < categories.length; k++) {
          //   let prefix = '';
          //
          // }
          that.categoryList = data.categories;
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
        that.$http.get(goodsCategoryDeleteUri + '?id=' + that.categoryList[index].id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            that.categoryList.splice(index, 1);
          }
        }, response => {
          // error callback
        })
      }).catch(() => {  // on cancel

      });
    },

  },
  mounted: function () {
    this.getAll();
  }
}
</script>

<style scoped>
.cate-item {
  padding: 5px 0;
  border-top: 1px solid #ECF0F5;
}
</style>
