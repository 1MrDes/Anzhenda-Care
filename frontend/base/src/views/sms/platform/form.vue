<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/14 23:17
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
        <el-form ref="form-sms-platform" :model="platform" :rules="rules" label-width="80px">
          <el-form-item prop="sms_name" label="名称">
            <el-input placeholder="请输入名称" v-model="platform.sms_name" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="sms_code" label="编码">
            <el-input placeholder="请输入编码" v-model="platform.sms_code" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="weight" label="权重">
            <el-input v-model.number="platform.weight" size="small"></el-input>
          </el-form-item>
          <el-form-item label="启用">
            <el-switch on-text="" off-text="" v-model.number="platform.enable" :active-value="1" :inactive-value="0"></el-switch>
          </el-form-item>
          <el-form-item prop="config" label="配置">
            <el-input v-model="platform.config" type="textarea" :rows="5"></el-input>
          </el-form-item>
          <el-form-item prop="sms_desc" label="备注">
            <el-input v-model="platform.sms_desc" type="textarea" :rows="5"></el-input>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="submitForm('form-sms-platform')">提交</el-button>
            <el-button @click="$router.back()">取消</el-button>
          </el-form-item>
        </el-form>
      </div>
    </section>
</template>
<script>
  import {smsPlatformSubmitUri, smsPlatformInfoUri, smsPlatformCheckCodeExistsUri} from '../../../common/api';
  export default {
    data () {
      return {
        platform: {
          id: 0,
          sms_name: '',
          sms_code: '',
          weight: 255,
          enable: 1,
          sms_desc: '',
          config: ''
        },
        rules: {
          sms_name: [
            {required: true, message: '请填写名称', trigger: 'blur'}
          ],
          sms_code: [
            {required: true, message: '请填写编码', trigger: 'blur'},
            {
              required: true,
              validator: (rule, value, callback) => {
                let self = this;
                self.$http.get(smsPlatformCheckCodeExistsUri + '?id='+ self.platform.id +'&code=' + value).then(response => {
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
          weight: [
            {type:'number', required: true, message: '请填写权重', trigger: 'blur'}
          ],
          config: [
            {required: true, message: '请填写配置信息', trigger: 'blur'}
          ]
        }
      }
    },
    methods: {
      fetchData () {
        let self = this;
        self.$http.get(smsPlatformInfoUri + '?id=' + self.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.platform = data;
          }
        }, response => {
          // error callback
        })
      },
      submitForm(name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.$http.post(smsPlatformSubmitUri, self.platform, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                self.$message({
                  message: '提交成功',
                  type: 'success'
                });
                self.$router.push({name: 'smsPlatformList'});
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
      if (this.$route.name == 'editSmsPlatform') {
        this.fetchData();
      }
    }
  }
</script>
