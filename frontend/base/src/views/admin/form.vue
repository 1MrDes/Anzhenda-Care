<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/5 13:42
 * @description
 -->
<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white">
      <el-col :span="24" class="filter-box-left">
        <el-button @click="$router.back()">返回上页</el-button>
      </el-col>
    </el-row>
    <div class="bg-color-white pd10">
      <el-form ref="form-admin" :model="admin" :rules="rules" label-width="80px">
        <el-form-item prop="username" label="用户名">
          <el-input placeholder="请输入用户名" v-model="admin.username" size="small" :disabled="disableUsername"></el-input>
        </el-form-item>
        <el-form-item prop="real_name" label="姓名">
          <el-input placeholder="请输入姓名" v-model="admin.real_name" size="small" :disabled="disableRealName"></el-input>
        </el-form-item>
        <el-form-item prop="mobile" label="手机">
          <el-input placeholder="请输入手机" v-model="admin.mobile" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="password" label="密码">
          <el-input placeholder="请输入密码" type="password" v-model="admin.password" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="repeat_password" label="重复密码">
          <el-input placeholder="请再次输入密码" type="password" v-model="admin.repeat_password" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="roleIds" label="角色">
          <el-checkbox-group v-model="admin.roleIds">
            <el-checkbox :label="role.id" v-for="(role, index) in roles" :key="index" name="roleIds[]">{{ role.name }}</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submitForm('form-admin')">提交</el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </section>
</template>
<script>
  import { adminSubmitUri, adminInfoUri, roleAllUri } from '../../common/api';

  export default {
    data () {
      return {
        admin: {
          id: 0,
          username: '',
          real_name: '',
          password: '',
          repeat_password: '',
          mobile: '',
          roleIds: []
        },
        roles: [],
        rules: {
          username: [
            {required: true, message: '请填写用户名', trigger: 'blur'}
          ],
          real_name: [
            {required: true, message: '请填写姓名', trigger: 'blur'}
          ],
          mobile: [
            {required: true, message: '请填写手机', trigger: 'blur'}
          ],
          roleIds: [
            {type: 'array', required: true, message: '请选择角色'}
          ],
          repeat_password: [
            {
              required: false,
              validator: (rule, value, callback) => {
                if (this.admin.password.length > 0 && this.admin.password != value) {
                  return callback(new Error('两次密码必须一致'))
                } else {
                  callback()
                }
              }, trigger: 'blur'
            }
          ]
        },
        disableUsername: false,
        disableRealName: false
      }
    },
    methods: {
      submitForm(name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.$http.post(adminSubmitUri, self.admin, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                self.$message({
                  message: '提交成功',
                  type: 'success'
                });
                self.$router.push({name: 'AdminList'});
              }
            }, response => {
              self.$message.error('发生错误')
            })
          } else {
            self.$message.error('请按提示输入')
          }
        })
      },
      resetValidateRules () {
        if (this.$route.name == 'AdminAdd') {
          this.rules.password = [
            {required: true, message: '请填写密码', trigger: 'blur'},
            {type: 'string', min: 6, message: '密码长度不能小于6位', trigger: 'blur'}
          ];
        } else if (this.$route.name == 'AdminEdit') {
          if (this.rules.password != null) {
            delete this.rules.password;
          }
        }
      }
    },
    created: function () {
      this.resetValidateRules();
      if (this.$route.name == 'AdminEdit') {
        this.disableUsername= true;
        this.disableRealName = true;
      }
    },
    mounted: function () {
      if (this.$route.name == 'AdminEdit') {
        let self = this
        this.$http.get(adminInfoUri + '?id=' + this.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.admin = data;
            self.admin.password = '';
            self.admin.repeat_password = '';
          }
        }, response => {
          // error callback
        })
      }
      let self = this
      this.$http.get(roleAllUri).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          self.$message.error(msg)
        } else {
          self.roles = data
        }
      }, response => {
        // error callback
      })
    }
  }
</script>
