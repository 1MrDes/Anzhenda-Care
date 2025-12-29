<template>
  <section>
    <remote-js src="https://cdn.bootcss.com/jquery/2.2.1/jquery.min.js" @load="onJqueryLoaded" v-if="!jqLoaded" />
    <div class="bg-color-white pd10 font-common">
      <form id="form-settings" onsubmit="return false">
        <van-tabs type="card">
          <van-tab :title="item.name" v-for="(item, key) in settings" :key="key">
            <van-row v-for="(item2, key2) in item.vars" :key="key2" class="pt10 pb10 border-bottom-gray">
              <van-col :span="6" style="text-align: right;">{{ item2.name }}：</van-col>
              <van-col :span="18">
                <input type="hidden" name="keys[]" :value="item2.code"/>
                <input type="text" :name="'values['+item2.code+']'" :value="item2.value" size="small"
                       v-if="item2.type=='text'" class="w100"/>

                <textarea :name="'values['+item2.code+']'" v-if="item2.type=='textarea'">{{item2.value}}</textarea>

                <select class="select" :name="'values['+item2.code+']'" v-if="item2.type=='options'">
                  <option :value="key3" v-for="(item3, key3) in item2.display_options" :selected="key3 == item2.value">
                    {{ item3 }}
                  </option>
                </select>

                <template v-if="item2.type=='select'">
                  <label v-for="(item3, key3) in item2.display_options" :key="key3">
                    <input :name="'values['+item2.code+']'" type="radio" :value="key3" v-if="item2.value==key3"
                           checked/>
                    <input :name="'values['+item2.code+']'" type="radio" :value="key3" v-else/>
                    {{ item3 }}
                  </label>
                </template>

                <!--                <van-radio-group v-model="item2.value" v-if="item2.type=='select'">-->
                <!--                  <van-radio :name="'values['+item2.code+']'" :label="key3" :value="key3"-->
                <!--                            v-for="(item3, key3) in item2.display_options">{{ item3 }}-->
                <!--                  </van-radio>-->
                <!--                </van-radio-group>-->
                <p v-if="item2.desc!=''">{{ item2.desc }}</p>
              </van-col>
            </van-row>
          </van-tab>
        </van-tabs>
        <van-row class="pt10">
          <van-button type="primary" block @click="submitForm">保存</van-button>
        </van-row>
      </form>
    </div>
  </section>
</template>

<script>
  import Vue from 'vue';
  import {Tab, Tabs, Row, Col, Button, Field, RadioGroup, Radio, Toast} from 'vant';

  Vue.use(Tab).use(Tabs).use(Row).use(Col).use(Button).use(Field).use(RadioGroup).use(Radio).use(Toast);

  import {
    siteConfigAllUri,
    siteConfigSaveUri
  } from '../../common/api';

  var jq = typeof $ == "undefined" ? null : $.noConflict();
  export default {
    data() {
      return {
        jqLoaded : jq == null ? false : true,
        settings: {}
      }
    },
    methods: {
      onJqueryLoaded() {
        jq = $.noConflict();
      },
      fetchData() {
        let self = this
        this.$http.get(siteConfigAllUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast(msg);
          } else {
            self.settings = data;
          }
        }, response => {
          // error callback
        })
      },
      submitForm() {
        let self = this;
        let user = this.GLOBAL.userInfo;
        jq.ajax({
          url: siteConfigSaveUri + '?access-token=' + user['access_token'],  //请求后台地址
          type: "post",   //请求方式
          cache: false, //控制是否缓存数据（post请求默认false，而get请求会为true,容易造成页面数据缓存问题）
          async: false, //控制同步还是异步
          data: jq('#form-settings').serialize(),  //传入参数
          success: function (response) {
            let {msg, code, data} = response;
            if (code != 0) {
              Toast(msg);
            } else {
              Toast({
                message: '保存成功',
                type: 'success'
              });
            }
          }
        });
        return false;
      }
    },
    mounted: function () {
      this.fetchData();
    }
  }
</script>

<style scoped>

</style>
