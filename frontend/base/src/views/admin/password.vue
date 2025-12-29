<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/9 22:48
 * @description
 -->
<template>
  <section>
    <div class="bg-color-white pd10">
      <el-form ref="form-admin" :model="admin" :rules="rules" label-width="80px">
        <el-form-item prop="password" label="密码">
          <el-input placeholder="请输入密码" type="password" v-model="admin.password" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="repeat_password" label="重复密码">
          <el-input placeholder="请再次输入密码" type="password" v-model="admin.repeat_password" size="small"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submitForm('form-admin')">提交</el-button>
        </el-form-item>
      </el-form>
    </div>
  </section>
</template>
<script>
  import {adminPasswordUri} from '../../common/api';
  export default {
    data () {
      return {
        admin: {
          password: '',
          repeat_password: ''
        },
        rules: {
          password: [
            {required: true, message: '请输入密码', trigger: 'blur'}
          ],
          repeat_password: [
            {required: true, message: '请输入密码', trigger: 'blur'},
            {
              required: true,
              validator: (rule, value, callback) => {
                if (this.admin.password != value) {
                  return callback(new Error('两次密码必须一致'))
                } else {
                  callback()
                }
              }, trigger: 'blur'
            }
          ]
        }
      }
    },
    methods: {
      submitForm (name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.$http.post(adminPasswordUri, self.admin, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                // sessionStorage.removeItem('user');
                // localStorage.removeItem('authorizedResources');
                self.GLOBAL.userInfo = null;
                self.$message({
                  message: '修改成功',
                  type: 'success'
                });
                self.$router.push({name: 'UserLogin'});
              }
            }, response => {
              self.$message.error('发生错误')
            })
          } else {
            self.$message.error('请按提示输入')
          }
        })
      }
    }
  }
</script>
