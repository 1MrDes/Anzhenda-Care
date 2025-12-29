<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/14 23:18
 * @description
 -->
<template>
    <section>
      <el-row class="filter-box pd10 mb10 bg-color-white">
        <el-col :span="24" class="filter-box-left">
          <el-button type="primary" @click="$router.back()">返回上页</el-button>
        </el-col>
      </el-row>
      <div class="bg-color-white pd10">
        <el-form ref="form-sms-template" :model="template" :rules="rules" label-width="130px">
          <el-form-item prop="name" label="名称">
            <el-input placeholder="请输入名称" v-model="template.name" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="code" label="编码">
            <el-input placeholder="请输入编码" v-model="template.code" size="small"></el-input>
          </el-form-item>
          <el-form-item label="启用">
            <el-switch on-text="" off-text="" v-model.number="template.state" :active-value="1" :inactive-value="0"></el-switch>
          </el-form-item>
          <el-form-item prop="maximum" label="每日最大发送量">
            <el-input placehoder="0表示不限制" v-model.number="template.maximum" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="content" label="模板">
            <el-input v-model="template.content" type="textarea" :rows=5></el-input>
          </el-form-item>
          <el-form-item prop="description" label="备注">
            <el-input v-model="template.description" type="textarea" :rows=5></el-input>
          </el-form-item>
          <el-form-item label="平台模板编码："></el-form-item>
          <el-form-item :label="platform.platform_name" v-for="(platform, index) in platforms" :key="index">
            <el-input v-model="platform.platform_content"></el-input>
          </el-form-item>

          <el-form-item>
            <el-button type="primary" @click="submitForm('form-sms-template')">提交</el-button>
            <el-button @click="$router.back()">取消</el-button>
          </el-form-item>
        </el-form>
      </div>
    </section>
</template>
<script>
  import {smsTemplateSubmitUri, smsTemplateInfoUri, smsTemplateCheckCodeExistsUri
    , smsTemplatePlatformsUri} from '../../../common/api';
  export default {
    data () {
      return {
        platforms: [],
        template: {
          id: 0,
          name: '',
          code: '',
          maximum: 10,
          state: 1,
          content: '',
          description: '',
          platforms: ''
        },
        rules: {
          name: [
            {required: true, message: '请填写名称', trigger: 'blur'}
          ],
          code: [
            {required: true, message: '请填写编码', trigger: 'blur'},
            {
              required: true,
              validator: (rule, value, callback) => {
                let self = this;
                self.$http.get(smsTemplateCheckCodeExistsUri + '?id='+ self.template.id +'&code=' + value).then(response => {
                  let {code, msg, data} = response.body
                  if (code != 0) {
                    callback(new Error(msg));
                  } else {
                    callback();
                  }
                }, response => {
                  // error callback
                });
              },
              trigger: 'blur'
            }
          ],
          content: [
            {required: true, message: '请填写模板', trigger: 'blur'}
          ],
          maximum: [
            {type:'number', required: true, message: '请填写最大发送量', trigger: 'blur'}
          ]
        }
      }
    },
    methods: {
      fetchData() {
        let self = this;
        self.$http.get(smsTemplateInfoUri + '?id=' + self.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.template = data;
          }
        }, response => {
          // error callback
        })
      },
      fetchPlatforms(id) {
        let self = this;
        self.$http.get(smsTemplatePlatformsUri + '?id=' + id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.platforms = data;
          }
        }, response => {
          // error callback
        })
      },
      submitForm(name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.template.platforms = JSON.stringify(self.platforms);
            self.$http.post(smsTemplateSubmitUri, self.template, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                self.$message({
                  message: '提交成功',
                  type: 'success'
                });
                self.$router.push({name: 'smsTemplateList'});
              }
            }, response => {
              self.$message.error('发生错误')
            })
          } else {
            self.$message.error('请按提示输入')
          }
        })
      }
    },
    mounted: function () {
      if (this.$route.name == 'editSmsTemplate') {
        this.fetchData();
        this.fetchPlatforms(this.$route.params.id);
      } else {
        this.fetchPlatforms(0);
      }
    }
  }
</script>
