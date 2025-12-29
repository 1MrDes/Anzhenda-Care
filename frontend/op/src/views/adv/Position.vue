<template>
  <section>
    <div class="pd10">
      <van-button type="primary" class="w100" @click="showFormPopup">+添加</van-button>
    </div>

    <van-list
      class="mt10"
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="requestData">
      <van-row class="list-item bg-color-white">
        <van-col :span="21" class="pl10 text-left strong">名称</van-col>
        <van-col :span="3" class="pr10 text-right strong">操作</van-col>
      </van-row>

      <van-row v-for="(item, index) in dataList" :key="index" class="list-item bg-color-white">
        <van-col :span="21" class="pl10 text-left">{{item.name}}</van-col>
        <van-col :span="3" class="pr10 text-right">
          <van-icon name="edit" @click="edit(index)" />
          <van-icon name="delete" class="ml10" @click="del(index)" />
        </van-col>
      </van-row>
    </van-list>

    <van-popup closeable v-model="formPopupVisible" class="pd10" style="width: 80%;">
      <van-form @submit="onFormSubmit">
        <van-field
          v-model="position.name"
          name="name"
          label="名称"
          placeholder="请输入名称"
          :rules="[{ required: true, message: '请输入名称' }]"
        />

        <van-field
          v-model="position.code"
          name="code"
          label="编码"
          placeholder="请输入编码"
        />

        <van-field
          v-model="position.height"
          name="height"
          label="高度"
          placeholder="请输入高度"
          type="digit"
        />

        <van-field
          v-model="position.width"
          name="width"
          label="宽度"
          placeholder="请输入宽度"
          type="digit"
        />

        <van-field
          v-model="position.description"
          name="description"
          label="说明"
          placeholder="请输入说明"
          type="textarea"
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
  import {Toast, Dialog, Button, Form, Field, Popup, Row, Col, Icon, List} from 'vant';
  import {advPositionSaveUri, advPositionListsUri, advPositionDeleteUri} from "../../common/api";
  Vue.use(Toast).use(Dialog).use(Button).use(Popup).use(Form).use(Field).use(Row).use(Col).use(Icon).use(List);

  export default {
    data() {
      return {
        formPopupVisible: false,
        position: {id: 0, code: '', name: '', height: 0, width: 0, description: ''},

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
      showFormPopup() {
        this.position = {id: 0, code: '', name: '', height: 0, width: 0, description: ''};
        this.formPopupVisible = true;
      },
      edit(index) {
        this.position = this.dataList[index];
        this.formPopupVisible = true;
      },
      del(index) {
        const that = this;
        Dialog.confirm({
          message: '确定要删除吗？'
        }).then(() => { // on confirm
          that.$http.get(advPositionDeleteUri + '?id=' + that.dataList[index].id).then(response => {
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
      onFormSubmit(values) {
        const that = this;
        that.$http.post(advPositionSaveUri, that.position, {emulateJSON: true}).then(response => {
          let {msg, code, data} = response.body
          if (code != 0) {
            Toast.fail(msg)
          } else {
            Toast.success('保存成功');
            that.formPopupVisible = false;
            that.currentPage = 1;
            that.lastPage = 0;
            that.total = 0;
            that.loading = false;
            that.finished = false;
            that.dataList = [];
          }
        }, response => {
          Toast.fail('发生错误')
        })
      },
      requestData() {
        const that = this;
        this.loading = true;
        const url = advPositionListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
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

</style>
