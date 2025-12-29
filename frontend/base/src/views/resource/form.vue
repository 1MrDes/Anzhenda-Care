<!--
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-7 16:20
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
      <el-form ref="form-resource" :model="resource" :rules="rules" label-width="80px">
        <el-form-item label="上级">
          <el-select v-model="resource.parent_id" placeholder="请选择上级路径">
            <el-option :value="option.id" :label="option.name" v-for="(option, index) in resources" :key="index" v-html="option.full_name"></el-option>
          </el-select>
        </el-form-item>
        <el-form-item prop="name" label="名称">
          <el-input placeholder="请输入名称" v-model="resource.name" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="url" label="URI">
          <el-input placeholder="请输入uri" v-model="resource.url" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="icon" label="icon">
          <el-input placeholder="图标" v-model="resource.icon" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="description" label="描述">
          <el-input type="textarea" :rows="3" v-model="resource.description"></el-input>
        </el-form-item>
        <el-form-item label="显示">
          <el-switch on-text="" off-text="" v-model.number="resource.ishide" :active-value="0" :inactive-value="1"></el-switch>
        </el-form-item>
        <el-form-item prop="sort_order" label="排序">
          <el-input v-model="resource.sort_order" size="small"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submitForm('form-resource')">提交</el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </section>
</template>
<script>
  import {resourceOptionsUri, resourceSubmitUri, resourceInfoUri} from '../../common/api';
  export default {
    data() {
      return {
        resource: {
          id: 0,
          parent_id: '',
          name: '',
          url: '',
          ishide: 0,
          description: '',
          icon: '',
          sort_order: 255
        },
        resources: [],
        rules: {
          name: [
            {required: true, message: '请填写名称', trigger: 'blur'}
          ],
          url: [
            {required: true, message: '请填写uri', trigger: 'blur'}
          ],
          sort_order: [
            {required: true, message: '请填写排序', trigger: 'blur'}
          ]
        }
      }
    },
    methods: {
      fetchData () {
        let self = this
        self.$http.get(resourceInfoUri + '?id=' + self.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            if(data.parent_id == 0) {
              data.parent_id = '';
            }
            self.resource = data;
          }
        }, response => {
          // error callback
        })
      },
      fetchResouces () {
        let self = this
        self.$http.get(resourceOptionsUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            for (let i = 0; i < data.length; i++) {
              let fullName = '';
              for (let j = 0; j < data[i].layer - 1; j++) {
                fullName += '|-';
              }
              fullName += data[i].name;
              data[i]['full_name'] = fullName;
            }
            self.resources = data;
          }
        }, response => {
          // error callback
        })
      },
      submitForm(name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.$http.post(resourceSubmitUri, self.resource, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                self.$message({
                  message: '提交成功',
                  type: 'success'
                });
                self.$router.push({name: 'ResourceList'});
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
      this.fetchResouces();
      if (this.$route.name == 'ResourceEdit') {
        this.fetchData();
      }
    }
  }
</script>
