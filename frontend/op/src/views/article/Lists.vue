<template>
  <section>
    <van-tabs v-model:active="activeTabName" sticky @click="onTabClick">
      <van-tab title="文章" name="article">
        <div class="pd10">
          <van-button type="primary" class="w100" @click="$router.push({name: 'ArticleAdd'})">+添加文章</van-button>
        </div>

        <van-list
          class="mt10"
          v-model="loading"
          :finished="finished"
          finished-text="没有更多了"
          @load="requestData">
          <vxe-table
            :data="dataList"
            :scroll-y="{enabled: false}">
            <vxe-column field="title" title="标题"></vxe-column>

            <vxe-column field="category" title="分类" width="80">
              <template #default="{ row, rowIndex }">
                <span>{{row.category.name}}</span>
              </template>
            </vxe-column>

            <vxe-column field="is_show" title="上线" width="60">
              <template #default="{ row, rowIndex }">
                <span>{{row.is_show ? 'Y' : 'N'}}</span>
              </template>
            </vxe-column>

            <vxe-column field="sort_order" title="排序" width="60"></vxe-column>

            <vxe-column field="opt" title="操作" width="120">
              <template #default="{ row, rowIndex }">
                <van-icon name="edit" @click="$router.push({name: 'ArticleEdit', params: {id: row.id}})"/>
                <van-icon name="delete" class="ml10" @click="del(rowIndex)"/>
              </template>
            </vxe-column>
          </vxe-table>

        </van-list>

      </van-tab>
      <van-tab title="分类" name="article-category"></van-tab>
    </van-tabs>
  </section>
</template>

<script>
import {Dialog} from "vant";
import {articleDeleteUri, articleListsUri} from "../../common/api";

export default {
  data() {
    return {
      activeTabName: 'article',

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
      if (name == 'article-category') {
        this.$router.replace({name: 'ArticleCategoryLists'});
      }
    },
    del(index) {
      const that = this;
      Dialog.confirm({
        message: '确定要删除吗？'
      }).then(() => { // on confirm
        that.$http.get(articleDeleteUri + '?id=' + that.dataList[index].id).then(response => {
          let {code, msg, data} = response.body;
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
      const url = articleListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
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

</style>
