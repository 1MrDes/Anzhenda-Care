<template>
  <section>
    <van-sticky>
      <PageHeader title="服务类型"/>
    </van-sticky>

    <van-tabs v-model:active="activeTabName" sticky @click="onTabClick">
      <van-tab title="服务" name="service"></van-tab>
      <van-tab title="服务分类" name="category"></van-tab>
      <van-tab title="服务类型" name="type">
        <div class="pd10">
          <van-button type="primary" class="w100" @click="showFormPopup">+添加类型</van-button>
        </div>

        <div class="bg-color-white cate-list">
          <vxe-table
            :data="dataList"
            :scroll-y="{enabled: false}">
            <vxe-column type="seq" width="60"></vxe-column>
            <vxe-column field="name" title="名称"></vxe-column>
            <vxe-column field="enabled" title="启用" width="60">
              <template #default="{ row, rowIndex }">
                <label v-if="row.enabled">Y</label>
                <label v-else>N</label>
              </template>
            </vxe-column>
            <vxe-column field="opt" title="操作" width="120">
              <template #default="{ row, rowIndex }">
                <i class="iconfont icon-weixinzhifu mr5"
                   title="属性管理"
                   @click="$router.push({name: 'HealthServiceAttrLists', params: {typeId: row.id}})"/>
                <!--          <i class="iconfont icon-weixinzhifu mr5"-->
                <!--             title="规格管理"-->
                <!--             @click="$router.push({name: 'HealthServiceSpecLists', params: {typeId: item.id}})"/>-->
                <van-icon name="edit" @click="edit(rowIndex)"/>
                <van-icon name="delete" class="ml10" @click="del(rowIndex)"/>
              </template>
            </vxe-column>
          </vxe-table>

        </div>
      </van-tab>
    </van-tabs>

    <div class="empty-box"></div>
    <Tabbar active="goods"/>

    <van-popup closeable v-model="formPopupVisible" class="pd10" style="width: 80%;">
      <van-form @submit="onFormSubmit">
        <van-field
          v-model="type.name"
          name="名称"
          label="名称"
          placeholder="请输入名称"
          required
          :rules="[{ required: true, message: '请输入名称' }]"
        />

        <van-cell center title="启用">
          <template #right-icon>
            <van-switch v-model="type.enabled" :active-value="1" :inactive-value="0"/>
          </template>
        </van-cell>

        <div style="margin: 16px;">
          <van-button round block type="info" native-type="submit">
            保存
          </van-button>
        </div>
      </van-form>
    </van-popup>
  </section>
</template>

<script>
import Vue from 'vue';
import {Dialog} from 'vant';
import {goodsTypeListsUri, goodsTypeSaveUri, goodsTypeDeleteUri} from "../../../common/api";
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
      activeTabName: 'type',
      formPopupVisible: false,
      type: {id: 0, name: '', enabled: 1},
      dataList: []
    }
  },
  methods: {
    onTabClick(name) {
      if (name == 'category') {
        this.$router.replace({name: 'HealthServiceCategoryLists'});
      } else if (name == 'service') {
        this.$router.replace({name: 'HealthServiceLists'});
      }
    },
    showFormPopup() {
      this.type = {id: 0, name: '', enabled: 1};
      this.formPopupVisible = true;
    },
    edit(index) {
      this.type = this.dataList[index];
      this.formPopupVisible = true;
    },
    getAll() {
      const that = this;
      this.$http.get(goodsTypeListsUri).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg);
        } else {
          that.dataList = data.types;
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
        that.$http.get(goodsTypeDeleteUri + '?id=' + that.dataList[index].id).then(response => {
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
    onFormSubmit(values) {
      const that = this;
      that.$http.post(goodsTypeSaveUri, that.type, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg)
        } else {
          that.$toast.success('保存成功');
          that.formPopupVisible = false;
          that.getAll();
        }
      }, response => {
        that.$toast.fail('发生错误')
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
