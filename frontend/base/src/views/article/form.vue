<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/11/14 22:59
 * @description
 -->
<template>
  <section>
    <div class="bg-color-white pd10">
      <el-form ref="form-article" :model="article" :rules="rules" label-width="80px">
        <el-form-item prop="cateId" label="分类">
          <el-select v-model.number="article.cate_id" placeholder="请选择">
            <el-option
              v-for="(item, index) in categories"
              :key="index"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item prop="title" label="标题">
          <el-input v-model="article.title" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="code" label="编码">
          <el-input v-model="article.code" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="short_content" label="简介">
          <el-input v-model="article.short_content" size="small" type="textarea" :rows="5"></el-input>
        </el-form-item>
        <el-form-item label="缩略图" prop="imgUrl">
          <el-upload
            class="image-uploader"
            :action="fileUploadUri"
            :data="{upload_token:uploadToken}"
            :show-file-list="false"
            :on-success="onFileUploadSuccess"
            :before-upload="beforeFileUpload">
            <img v-if="article.img_url" :src="article.img_url" class="upload-image">
            <i v-else class="el-icon-plus image-uploader-icon"></i>
          </el-upload>
        </el-form-item>
        <el-form-item label="内容：" prop="content">
          <UE :defaultMsg="article.content" :config="ueConfig" id="ue" ref="ue"></UE>
        </el-form-item>
        <el-form-item prop="sortOrder" label="排序">
          <el-input v-model.number="article.sort_order" size="small"></el-input>
        </el-form-item>
        <el-form-item label="显示：">
          <el-radio-group v-model.number="article.is_show">
            <el-radio :label="1">是</el-radio>
            <el-radio :label="0">否</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item prop="tpl" label="模板名">
          <el-input v-model="article.tpl" size="small"></el-input>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="submitForm('form-article')">提交</el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </section>
</template>
<script>
  import UE from '../../components/Ueditor';
  import {
    fileUploadUri,
    ueFileUploadUri,
    articleCategoryListUri,
    articleSubmitUri,
    articleInfoUri, BASE_PATH
  } from '../../common/api'
  export default {
    components: {UE},
    data () {
      let user = this.GLOBAL.userInfo;
      let ueServerUrl = ueFileUploadUri + '?upload_token=' + user.upload_token;
      return {
        uploadToken:user.upload_token,
        fileUploadUri: fileUploadUri + '?upload_token=' + user.upload_token,
        ueConfig: {
          UEDITOR_HOME_URL: BASE_PATH + 'static/js/ueditor/',
          serverUrl: ueServerUrl,
          initialFrameWidth: null,
          initialFrameHeight: 350
        },
        categories: [],
        article: {
          id : 0,
          code: '',
          title: '',
          cate_id: '',
          sort_order: 255,
          is_show: 1,
          img_id: 0,
          img_url: '',
          short_content: '',
          content: '',
          tpl: ''
        },
        rules: {
          cate_id: [
            {type: 'integer', required: true, message: '请选择分类', trigger: 'blur'}
          ],
          title: [
            {required: true, message: '请填写标题', trigger: 'blur'}
          ],
          content: [
            {
              validator: (rule, value, callback) => {
                if(this.getUEContent().length == 0) {
                  return callback(new Error('请填写内容'));
                }
                callback();
              },
              required: true,
              message: '请填写内容',
              trigger: 'blur'
            }
          ],
          sort_order: [
            {type: 'integer', required: true, message: '请填写排序', trigger: 'blur'}
          ]
        }
      }
    },
    methods: {
      getUEContent () {
        let content = this.$refs.ue.getUEContent() // 调用子组件方法
        return content
      },
      setUEContent (content) {
        this.$refs.ue.setUEContent(content);
      },
      getCategories () {
        let self = this;
        this.$http.get(articleCategoryListUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.categories = data;
          }
        }, response => {
          // error callback
        })
      },
      onFileUploadSuccess (res, file) {
        if(res.code == 0) {
          this.article.img_id = res.data.id;
          this.article.img_url = URL.createObjectURL(file.raw);
        } else {
          this.$message.error(res.msg);
        }
      },
      beforeFileUpload (file) {
        const isJPG = (file.type === 'image/jpeg' || file.type === 'image/png');
        const isLt2M = file.size / 1024 / 1024 < 2;

        if (!isJPG) {
          this.$message.error('上传图片只能是 JPG或PNG 格式!')
        }
        if (!isLt2M) {
          this.$message.error('上传图片大小不能超过 2MB!')
        }
        return isJPG && isLt2M
      },
      submitForm(name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.article.content = self.getUEContent();
            if(self.article.content.length == 0) {
              self.$message.error('请输入内容');
              return;
            }
            self.$http.post(articleSubmitUri, self.article, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                self.$message({
                  message: '提交成功',
                  type: 'success'
                });
                self.$router.push({name: 'articleList'});
              }
            }, response => {
              self.$message.error('发生错误')
            })
          } else {
            self.$message.error('请按提示输入')
          }
        })
      },
      getInfo () {
        let self = this;
        this.$http.get(articleInfoUri + '?id=' + self.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.article = data;
            self.setUEContent(self.article.content);
          }
        }, response => {
          // error callback
        })
      }
    },
    mounted: function () {
      this.getCategories();
      if(this.$route.name == 'editArticle') {
        this.getInfo();
      }
    }
  }
</script>
<style scoped="scoped">
  .image-uploader-icon {
    line-height: 178px;
  }
</style>
