<!--
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-7 14:31
 * Description:
 -->
<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white">
      <el-col :span="24" class="filter-box-left">
        <el-button @click="$router.back()">返回上页</el-button>
      </el-col>
    </el-row>
    <div class="bg-color-white pd10">
      <el-form ref="form-role" :model="role" :rules="rules" label-width="80px">
        <el-form-item prop="name" label="角色">
          <el-input placeholder="请输入角色" v-model="role.name" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="remark" label="备注">
          <el-input v-model="role.remark" type="textarea" :rows="5"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submitForm('form-role')">提交</el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </section>
</template>
<script>
  import {roleInfoUri, roleSaveUri} from '../../common/api';
  export default {
    data() {
      return {
        role: {
          id: 0,
          name: '',
          remark: ''
        },
        rules: {
          name: [
            {required: true, message: '请填写角色名称', trigger: 'blur'}
          ]
        }
      }
    },
    methods: {
      fetchData() {
        let self = this;
        this.$http.get(roleInfoUri + '?id=' + this.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.role = data
          }
        }, response => {
          // error callback
        })
      },
      submitForm(name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.$http.post(roleSaveUri, self.role, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                self.$message({
                  message: '提交成功',
                  type: 'success'
                });
                self.$router.push({name: 'RoleList'});
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
      if (this.$route.name == 'RoleEdit') {
        this.fetchData();
      }
    }
  }
</script>
