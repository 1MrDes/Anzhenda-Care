<template>
  <section>
    <van-tabs v-model:active="activeTabName" sticky @click="onTabClick">
      <van-tab title="文章" name="article"></van-tab>
      <van-tab title="分类" name="article-category">
        <div class="pd10">
          <van-button type="primary" class="w100" @click="showFormPopup">+添加分类</van-button>
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
                <van-icon name="add-o" @click="addChild(rowIndex)"/>
                <van-icon name="edit" class="ml10" @click="edit(rowIndex)"/>
                <van-icon name="delete" class="ml10" @click="del(rowIndex)"/>
              </template>
            </vxe-column>
          </vxe-table>
        </div>

      </van-tab>
    </van-tabs>

    <van-popup closeable v-model="formPopupVisible" class="pd10" style="width: 80%;">
      <van-form @submit="onFormSubmit">
        <van-field
          v-model="category.parent_name"
          name="parent_name"
          label="上级分类"
          readonly
          v-if="category.parent_id > 0"
        />
        <van-field
          v-model="category.name"
          name="name"
          label="名称"
          placeholder="请输入名称"
          required
          :rules="[{ required: true, message: '请输入名称' }]"
        />
        <van-field
          v-model.number="category.sort_order"
          name="sort_order"
          label="排序"
          placeholder="请输入排序"
          type="digit"
          required
          :rules="[{ required: true, message: '请输入排序' }]"
        />

        <van-field
          v-model.number="category.code"
          name="code"
          label="编码"
          placeholder="请输入编码"
        />

        <div style="margin: 16px;">
          <van-button round block type="info" native-type="submit">
            提交
          </van-button>
        </div>
      </van-form>
    </van-popup>
  </section>
</template>

<script>
import Vue from 'vue';
import {Dialog} from 'vant';
import {
  articleCategorySaveUri,
  articleCategoryDeleteUri,
  articleCategoryTreeUri
} from "../../../common/api";
import Tabbar from "../../componets/Tabbar";

Vue.use(Dialog);

export default {
  components: {
    Tabbar: Tabbar
  },
  data() {
    return {
      activeTabName: 'article-category',
      formPopupVisible: false,
      category: {id: 0, parent_id: 0, parent_name: '', name: '', sort_order: 255, code: '', level: 1},
      categoryList: [],
    }
  },
  methods: {
    onTabClick(name) {
      if (name == 'article') {
        this.$router.replace({name: 'ArticleLists'});
      }
    },
    showFormPopup() {
      this.category = {id: 0, parent_id: 0, parent_name: '', name: '', sort_order: 255, code: '', level: 1};
      this.formPopupVisible = true;
    },
    addChild(index) {
      const parent = this.categoryList[index];
      this.category = {
        id: 0,
        parent_id: parent.id,
        parent_name: parent.name,
        name: '',
        sort_order: 255,
        code: '',
        level: parent.level + 1
      };
      this.formPopupVisible = true;
    },
    edit(index) {
      this.category = this.categoryList[index];
      this.formPopupVisible = true;
    },
    getAll() {
      const that = this;
      this.$http.get(articleCategoryTreeUri).then(response => {
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
        that.$http.get(articleCategoryDeleteUri + '?id=' + that.categoryList[index].id).then(response => {
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
    onFormSubmit(values) {
      const that = this;
      that.$http.post(articleCategorySaveUri, that.category, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg)
        } else {
          that.$toast.success('保存成功');
          that.formPopupVisible = false;
          that.getAll();
        }
      }, response => {
        that.$toast.fail('发生错误');
      })
    }
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
