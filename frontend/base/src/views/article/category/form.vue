<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/11/13 13:35
 * @description
 -->
<template>
  <section>
    <div class="bg-color-white pd10">
      <el-form ref="form-category" :rules="rules" :model="category"  label-width="120px">
        <el-form-item label="名称：" prop="name">
          <el-input v-model="category.name"></el-input>
        </el-form-item>
        <el-form-item label="编码：" prop="code">
          <el-input v-model="category.code"></el-input>
        </el-form-item>
        <el-form-item label="排序：" prop="sort_order">
          <el-input v-model.number="category.sort_order"></el-input>
        </el-form-item>
        <el-form-item label="">
          <el-button type="primary" @click="submitForm('form-category')">提交</el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </section>
</template>
<script>
  import {articleCategoryInfoUri, articleCategorySubmitUri} from '../../../common/api';
  export default {
    data () {
      return {
        category: {
          id: 0,
          parent_id: 0,
          name: '',
          code: '',
          sort_order: 255
        },
        rules: {
          name: [
            {required: true, message: '请输入名称', trigger: 'blur'}
          ],
          sort_order: [
            {type: 'integer', required: true, message: '请输入排序', trigger: 'blur'}
          ]
        }
      }
    },
    methods: {
      fetchData() {
        let self = this;
        self.$http.get(articleCategoryInfoUri + '?id=' + this.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.category = data;
          }
        }, response => {
          // error callback
        })
      },
      submitForm(name) {
        this.$refs[name].validate((valid) => {
          if (valid) {
            let self = this;
            this.$http.post(articleCategorySubmitUri, self.category, {emulateJSON: true}).then(response => {
              let { msg, code, data } = response.body;
              if(code != 0) {
                self.$message.error(msg);
              } else {
                self.$message({
                  message: '保存成功',
                  type: 'success'
                });
                self.$router.push({name: 'articleCategoryList'});
              }
            }, response => {
              self.$message.error('发生错误');
            });
          } else {
            this.$message.error('请按提示输入');
          }
        })
      }
    },
    mounted: function () {
      if(this.$route.name == 'editArticleCategory') {
        this.fetchData();
      }
    }
  }
</script>
