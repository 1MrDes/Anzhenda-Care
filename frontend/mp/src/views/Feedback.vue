<template>
  <section>
    <div class="bg-color-white pd10 md10 round-box">
      <van-form @submit="onFormSubmit">
        <van-field
          v-model="formData.title"
          name="title"
          label="标题"
          placeholder="请输入标题"
          :rules="[{ required: true, message: '请输入标题' }]"
        />

        <van-field name="radio" label="联系方式">
          <template #input>
            <van-radio-group v-model="formData.contact_type" direction="horizontal">
              <van-radio :name="1">手机</van-radio>
              <van-radio :name="2">微信号</van-radio>
              <van-radio :name="3">QQ</van-radio>
              <van-radio :name="4">其他</van-radio>
            </van-radio-group>
          </template>
        </van-field>

        <van-field
          v-model="formData.contact"
          name="contact"
          label="联系方式"
          placeholder="请输入手机号/微信号/QQ/其他"
          :rules="[{ required: true, message: '请输入具体的联系方式' }]"
        />

        <van-field
          v-model="formData.content"
          name="content"
          label="内容"
          placeholder="请输入内容"
          type="textarea"
          :rows="6"
          :rules="[{ required: true, message: '请输入内容' }]"
        />

        <div style="margin: 16px;">
          <van-button round block type="info" native-type="submit">
            提交
          </van-button>
        </div>
      </van-form>
    </div>
  </section>
</template>

<script>
import Vue from 'vue';
import {Toast, Dialog, Button, Form, Field, Row, Col,RadioGroup, Radio} from 'vant';
import {feedbackSubmitUri} from "../common/api";
Vue.use(Toast).use(Dialog).use(Button).use(Form).use(Field).use(Row).use(Col).use(RadioGroup).use(Radio);

export default {
  data() {
    return {
      formData: {title: '', content: '', contact_type: 1, contact: ''}
    }
  },
  methods: {
    onFormSubmit(values) {
      if(this.formData.title.length == 0) {
        Toast.fail('请输入标题');
        return;
      }
      if(this.formData.contact.length == 0) {
        Toast.fail('请输入具体的联系方式');
        return;
      }
      if(this.formData.content.length == 0) {
        Toast.fail('请输入内容');
        return;
      }
      const that = this;
      that.$http.post(feedbackSubmitUri, that.formData, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          Toast.fail(msg)
        } else {
          Toast.success({
            message: '提交成功，稍后客服会与您联系',
            duration: 6000
          });
          that.formData = {title: '', content: '', contact_type: 1, contact: ''};
        }
      }, response => {
        Toast.fail('发生错误')
      })
    },
  },
  mounted: function () {

  }
}
</script>

<style scoped>

</style>
