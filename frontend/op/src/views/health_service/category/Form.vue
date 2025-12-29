<template>
  <section>
    <van-sticky>
      <PageHeader title="服务分类"/>

      <van-form @submit="onFormSubmit">
        <div class="flex flex-row flex-center bg-color-white" style="padding: 10px 16px;">
          <div class="flex flex-row flex-left text-center font-big" style="width: 90px;">
            上级分类
          </div>
          <div class="flex flex-row flex-left flex-1">
            <Multiselect
              v-model="category.parent"
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
          v-model="category.name"
          name="name"
          label="名称"
          placeholder="请输入名称"
          required
          :rules="[{ required: true, message: '请输入名称' }]"
        />
        <van-field
          v-model.number="category.sort_order"
          name="sort_order"
          label="排序"
          placeholder="请输入排序"
          type="digit"
          required
          :rules="[{ required: true, message: '请输入排序' }]"
        />

        <van-field name="defaultImage" label="默认图片">
          <template #input>
            <van-uploader :after-read="uploadImage" :before-read="beforeRead">
              <div class="img-uploader-wrapper">
                <div class="btn-icon">
                  <van-icon name="plus"/>
                </div>
                <img :src="category.img_url" v-if="category.img_url" />
              </div>
            </van-uploader>
          </template>
        </van-field>

        <div style="margin: 16px;">
          <van-button round block type="info" native-type="submit">
            提交
          </van-button>
        </div>
      </van-form>
    </van-sticky>
  </section>
</template>

<script>
import PageHeader from "../../componets/PageHeader";
import {goodsCategoryInfoUri, fileUploadByBase64Uri, goodsCategorySaveUri, goodsCategoryTreeUri} from "../../../common/api";
import Multiselect from "vue-multiselect";
import 'vue-multiselect/dist/vue-multiselect.min.css';

export default {
  components: {
    PageHeader,
    Multiselect
  },
  data() {
    return {
      categories: [],
      category: {id: 0, parent_id: 0, parent: {id: 0, name: ''}, name: '', sort_order: 255, level: 1, img_id: 0, img_url: ''},
    }
  },
  methods: {
    getAll() {
      const that = this;
      this.$http.get(goodsCategoryTreeUri).then(response => {
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
    onFormSubmit(values) {
      const that = this;
      that.category.parent_id = that.category.parent.id;
      that.$http.post(goodsCategorySaveUri, that.category, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg)
        } else {
          that.$toast.success('保存成功');
          that.$router.back();
        }
      }, response => {
        that.$toast.fail('发生错误');
      })
    },
    getInfo() {
      const that = this;
      this.$http.get(goodsCategoryInfoUri + '?id=' + this.$route.params.id).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg);
        } else {
          that.category = data.category;
        }
      }, response => {
        // error callback
      })
    },
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
          that.category.img_id = file_id;
          that.category.img_url = url;
        }
      }, response => {
        that.$toast.fail('发生错误');
      })
    },
  },
  mounted: function () {
    this.getAll();
    if(this.$route.name == 'HealthServiceCategoryAdd') {
      if(this.$route.query.parent_id) {
        this.category.parent.id = this.$route.query.parent_id;
        this.category.parent.name = this.$route.query.parent_name;
      }
    } else if(this.$route.name == 'HealthServiceCategoryEdit') {
      this.getInfo();
    }
  }
}
</script>

<style scoped>

</style>
