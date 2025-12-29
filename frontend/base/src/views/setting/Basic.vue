<!--
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-10 10:00
 * Description:
 -->
<template>
  <section>
    <remote-js src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js" @load="onJqueryLoaded" v-if="!jqLoaded" />
    <div class="bg-color-white pd10">
      <form id="form-settings">
        <el-tabs type="border-card">
          <el-tab-pane :label="item.name" v-for="(item, key) in settings" :key="key">
            <el-row v-for="(item2, key2) in item.vars" :key="key2" class="pt5 pb5">
              <el-col :span="4" style="text-align: right;">{{ item2.name }}：</el-col>
              <el-col :span="20" class="pr10">
                <input type="hidden" name="keys[]" :value="item2.code" />
                <input type="text" :name="'values['+item2.code+']'" :value="item2.value" class="input w100" v-if="item2.type=='text'" />
<!--                <el-input :name="'values['+item2.code+']'" :value="item2.value" size="small" v-if="item2.type=='text'"></el-input>-->
<!--                <el-input :name="'values['+item2.code+']'" :value="item2.value" size="small" type="textarea" v-if="item2.type=='textarea'"></el-input>-->
                <textarea :name="'values['+item2.code+']'" v-if="item2.type=='textarea'" class="pd5 w100">{{item2.value}}</textarea>

                <select class="select" :name="'values['+item2.code+']'" v-if="item2.type=='options'">
                  <option :value="key3" v-for="(item3, key3) in item2.display_options" :key="key3" :selected="key3 == item2.value">{{ item3 }}</option>
                </select>

                <block v-if="item2.type=='select'">
                  <label v-for="(item3, key3) in item2.display_options" :key="key3">
                    <input type="radio"
                           :name="'values['+item2.code+']'"
                           :value="key3"
                           :checked="item2.value==key3" />
                    {{ item3 }}
                  </label>
                </block>

<!--                <el-radio-group v-model="item2.value" v-if="item2.type=='select'">-->
<!--                  <el-radio :name="'values['+item2.code+']'" :label="key3" :value="key3" :key="key3" v-for="(item3, key3) in item2.display_options">{{ item3 }}</el-radio>-->
<!--                </el-radio-group>-->
                <p v-if="item2.desc!=''">{{ item2.desc }}</p>
              </el-col>
            </el-row>
          </el-tab-pane>
        </el-tabs>
        <el-row class="pt10">
          <el-col :span="24">
            <el-button type="primary" @click="submitForm">保 存</el-button>
          </el-col>
        </el-row>
      </form>
    </div>
  </section>
</template>
<script>
  import {siteConfigAllUri, siteConfigSaveUri} from '../../common/api';
  var jq = typeof $ == "undefined" ? null : $.noConflict();

  export default {
    data() {
      return {
        jqLoaded : jq == null ? false : true,
        settings:{}
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
            self.$message.error(msg)
          } else {
            self.settings = data;
          }
        }, response => {
          // error callback
        })
      },
      submitForm() {
        const self = this;
        // const user = JSON.parse(sessionStorage.getItem('user'));
        const user = this.GLOBAL.userInfo;
        jq.post(siteConfigSaveUri + '?access_token=' + user.access_token, jq('#form-settings').serialize(), function (response) {
          let { msg, code, data } = response
          if(code != 0) {
            self.$message.error(msg);
          } else {
            self.$message({
              message: '保存成功',
              type: 'success'
            });
          }
        });
      }
    },
    mounted: function () {
      this.fetchData();
    }
  }
</script>
