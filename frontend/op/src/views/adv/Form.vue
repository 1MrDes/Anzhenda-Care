<template>
  <section>
    <van-form @submit="onFormSubmit">
      <van-field name="position" label="广告位">
        <template #input>
          <van-dropdown-menu>
            <van-dropdown-item v-model="adv.position_id" :options="positions" @change="onPositionChange" />
          </van-dropdown-menu>
        </template>
      </van-field>

      <van-field
        v-model="adv.title"
        name="title"
        label="标题"
        placeholder="请输入标题"
        :rules="[{ required: true, message: '请输入标题' }]"
      />

      <van-field name="radio" label="链接类型">
        <template #input>
          <van-radio-group v-model="adv.link_type" direction="horizontal">
            <van-radio :name="10">网页链接</van-radio>
            <van-radio :name="20">小程序页面</van-radio>
          </van-radio-group>
        </template>
      </van-field>

      <van-field
        v-model="adv.link"
        name="link"
        label="链接"
        placeholder="请输入链接"
        :rules="[{ required: true, message: '请输入链接' }]"
      />

      <van-field name="type" label="广告类型">
        <template #input>
          <van-radio-group v-model="adv.type" direction="horizontal">
            <van-radio :name="10">图片</van-radio>
<!--            <van-radio :name="20">视频</van-radio>-->
          </van-radio-group>
        </template>
      </van-field>

      <van-field name="image" label="图片">
        <template #input>
          <van-uploader :after-read="uploadImage" :before-read="beforeRead">
            <div class="img-uploader-wrapper">
              <div class="btn-icon">
                <van-icon name="plus"/>
              </div>
              <img :src="adv.file_url" v-if="adv.file_url" />
            </div>
          </van-uploader>
        </template>
      </van-field>

      <van-field name="enabled" label="上线">
        <template #input>
          <van-switch v-model="adv.enabled" size="20" :active-value="1" :inactive-value="0" />
        </template>
      </van-field>

      <van-field
        v-model="adv.sort_order"
        type="digit"
        name="sort_order"
        label="排序"
        placeholder="请输入排序"
        :rules="[{ required: true, message: '请输入正确内容' }]"
      />

      <div style="margin: 16px;">
        <van-button round block type="info" native-type="submit">
          保存
        </van-button>
      </div>
    </van-form>
  </section>
</template>

<script>
  import Vue from 'vue';
  import {Toast, Dialog, Button, Form, Field, Row, Col, Icon, DropdownMenu, DropdownItem, Uploader} from 'vant';
  import {advPositionAllUri, advSaveUri, advInfoUri, fileUploadByBase64Uri} from "../../common/api";
  Vue.use(Toast).use(Dialog).use(Button).use(Form).use(Field).use(Row).use(Col).use(Icon).use(DropdownMenu).use(DropdownItem).use(Uploader);

  export default {
    data() {
      return {
        positions: [],
        adv: {id: 0, position_id: 0, title: '', link_type: 10, link: '', type: 10, file_id: 0, file_url: '',
          enabled: 1, sort_order: 255}
      }
    },
    methods: {
      getPositions() {
        const that = this;
        this.$http.get(advPositionAllUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast.fail(msg);
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
      onPositionChange(value) {
        this.adv.position_id = value;
      },
      uploadImage(file, detail) {
        const that = this;
        let imgData = file.content;
        imgData = imgData.indexOf('data:image') == -1 ? 'data:image/jpg;base64,' + imgData : imgData;
        that.uploadFile(imgData);
      },
      beforeRead(file) {
        if (file.type !== 'image/jpeg' && file.type !== 'image/jpg' && file.type !== 'image/png') {
          Toast('请上传 jpg/png 格式图片');
          return false;
        }
        return true;
      },
      uploadFile(imgData) {
        const that = this;
        const params = {fileData: imgData, upload_token: this.GLOBAL.userInfo.upload_token};
        that.$http.post(fileUploadByBase64Uri, params, {emulateJSON: true}).then(response => {
          let {msg, code, file_id, url} = response.body
          if (code != 0) {
            Toast.fail(msg);
          } else {
            that.adv.file_id = file_id;
            that.adv.file_url = url;
          }
        }, response => {
          Toast.fail('发生错误');
        })
      },
      onFormSubmit(values) {
        if(this.adv.position_id == 0) {
          Toast.fail('请选择广告位');
          return;
        }
        if(this.adv.type == 10 && this.adv.file_id == 0) {
          Toast.fail('请上传图片');
          return;
        }
        const that = this;
        that.$http.post(advSaveUri, that.adv, {emulateJSON: true}).then(response => {
          let {msg, code, data} = response.body
          if (code != 0) {
            Toast.fail(msg)
          } else {
            Toast.success('保存成功');
            that.$router.back();
          }
        }, response => {
          Toast.fail('发生错误')
        })
      },
      getInfo() {
        const that = this;
        this.$http.get(advInfoUri + '?id=' + this.adv.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast.fail(msg);
          } else {
            that.adv = data.adv;
          }
        }, response => {
          // error callback
        })
      }
    },
    mounted: function () {
      this.getPositions();
      if(this.$route.name == 'AdvEdit') {
        this.adv.id = this.$route.params.id;
        this.getInfo();
      }
    }
  }
</script>

<style scoped>

</style>
