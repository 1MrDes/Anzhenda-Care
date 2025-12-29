<template>
  <section>
    <PageHeader title="文章"/>

    <van-form @submit="onFormSubmit">
      <div class="flex flex-row flex-center bg-color-white" style="padding: 10px 16px;">
        <div class="flex flex-row flex-left text-center font-big" style="width: 90px;">
          <span class="color-red">*</span>分类
        </div>
        <div class="flex flex-row flex-left flex-1">
          <Multiselect
            v-model="article.category"
            placeholder="请选择分类"
            label="name"
            :options="categories"
            track-by="name">
            <template slot="singleLabel" slot-scope="{ option }">
              <span>{{ option.name }}</span>
            </template>
          </Multiselect>
        </div>
      </div>

      <van-field
        v-model="article.title"
        name="title"
        label="标题"
        placeholder="请输入标题"
        required
        :rules="[{ required: true, message: '标题不能为空' }]"
      />
      <van-field
        v-model.number="article.sort_order"
        name="sort_order"
        label="排序"
        placeholder="请输入排序"
        type="digit"
        required
        :rules="[{ required: true, message: '请输入排序' }]"
      />

      <van-field
        v-model="article.link"
        name="link"
        label="跳转链接"
        placeholder="请输入跳转链接URL"
      />

      <van-field
        v-model="article.code"
        name="code"
        label="编码"
        placeholder="请输入编码"
      />

      <van-field
        v-model="article.tpl"
        name="tpl"
        label="页面模板"
        placeholder="请输入页面模板名称"
      />

      <van-field name="is_show" label="上线">
        <template #input>
          <van-switch v-model="article.is_show" size="20" :active-value="1" :inactive-value="0" />
        </template>
      </van-field>

      <van-field name="defaultImage" label="封面图片">
        <template #input>
          <van-uploader :after-read="uploadImage" :before-read="beforeRead">
            <div class="img-uploader-wrapper">
              <div class="btn-icon">
                <van-icon name="plus"/>
              </div>
              <img :src="article.img_url" v-if="article.img_url" />
            </div>
          </van-uploader>
        </template>
      </van-field>

      <van-field
        v-model="article.short_content"
        name="short_content"
        label="简介"
        placeholder="请输入简介"
        type="textarea"
        :rows="5"
      />

      <van-cell-group title="内容" v-if="article.link.length==0">
        <div class="pd10">
          <Ueditor :defaultMsg="article.content" :config="ueConfig" id="ue" ref="ue" />
        </div>
      </van-cell-group>

      <div style="margin: 16px;">
        <van-button round block type="info" native-type="submit">
          提交
        </van-button>
      </div>
    </van-form>

  </section>
</template>

<script>
import Multiselect from "vue-multiselect";
import 'vue-multiselect/dist/vue-multiselect.min.css';
import Ueditor from "../../components/Ueditor";
import {
  BASE_PATH,
  fileUploadByBase64Uri,
  fileUploadUri,
  articleSaveUri,
  ueFileUploadUri,
  articleCategoryTreeUri, articleInfoUri
} from "../../common/api";
import PageHeader from "../componets/PageHeader";

export default {
  components: {Ueditor, Multiselect, PageHeader},
  data() {
    const ueServerUrl = ueFileUploadUri + '?upload_token=' + this.GLOBAL.userInfo.upload_token;
    return {
      ueConfig: {
        UEDITOR_HOME_URL: BASE_PATH + 'static/js/ueditor/',
        serverUrl: ueServerUrl,
        initialFrameWidth: null,
        initialFrameHeight: 400
      },
      uploadToken:this.GLOBAL.userInfo.upload_token,
      fileUploadUri: fileUploadUri,
      article: {id: 0, title: '', category_id: 0, category: {id: 0, name: ''}, link: '', content: '', sort_order: 255,
        code: '', is_show : 1, short_content: '', img_id: 0, img_url: '', tpl: ''},
      categories: []
    }
  },
  methods: {
    uploadImage(file, detail) {
      const that = this;
      let imgData = file.content;
      imgData = imgData.indexOf('data:image') == -1 ? 'data:image/jpg;base64,' + imgData : imgData;
      that.uploadFile(imgData);
    },
    beforeRead(file) {
      if (file.type !== 'image/jpeg' && file.type !== 'image/jpg' && file.type !== 'image/png') {
        this.$toast('请上传 jpg/png 格式图片');
        return false;
      }
      return true;
    },
    uploadFile(imgData) {
      const that = this;
      const params = {fileData: imgData};
      that.$http.post(fileUploadByBase64Uri + '?upload_token=' + this.GLOBAL.userInfo.upload_token, params, {emulateJSON: true}).then(response => {
        let {msg, code, file_id, url} = response.body
        if (code != 0) {
          that.$toast.fail(msg);
        } else {
          that.article.img_id = file_id;
          that.article.img_url = url;
        }
      }, response => {
        that.$toast.fail('发生错误');
      })
    },
    onFormSubmit(values) {
      if(this.submiting) {
        return;
      }
      if(!this.article.category || this.article.category.id == 0) {
        this.$toast.fail('请选择分类');
        return;
      }
      this.article.category_id = this.article.category.id;

      if(typeof this.$refs.ue != "undefined") {
        this.article.content = this.$refs.ue.getUEContent();
      }
      this.submiting = true;
      const that = this;

      const postData = this.article;
      that.$http.post(articleSaveUri, postData, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body;
        that.submiting = false;
        if (code != 0) {
          that.$toast.fail(msg)
        } else {
          that.$toast.success('保存成功');
          that.$router.back();
        }
      }, response => {
        that.submiting = false;
        that.$toast.fail('发生错误')
      })
    },
    getCategoryList() {
      const that = this;
      this.$http.get(articleCategoryTreeUri).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg);
        } else {
          let categories = data.categories;
          for (let i = 0; i < categories.length; i++) {
            let prefix = '';
            for (let j = 0; j < categories[i].level; j++) {
              prefix += '|--';
            }
            categories[i].name = prefix + categories[i].name;
          }
          that.categories = categories;
        }
      }, response => {
        // error callback
      })
    },
    info() {
      const that = this;
      this.$http.get(articleInfoUri + '?id=' + this.$route.params.id).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg);
        } else {
          that.article = data.article;
          that.$refs.ue.setUEContent(data.article.content);
        }
      }, response => {
        // error callback
      })
    },
  },
  mounted: function () {
    if(this.$route.name == 'ArticleEdit') {
      this.info();
    }
    this.getCategoryList();
  }
}
</script>

<style scoped>

</style>
