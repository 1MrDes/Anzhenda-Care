<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/11/13 13:35
 * @description
 -->
<template>
  <section>
    <van-sticky>
      <PageHeader title="服务属性"/>
    </van-sticky>

    <div class="bg-color-white pd10">
      <van-form @submit="onFormSubmit">
        <van-field
          v-model="goodsType.name"
          name=""
          label="服务类型"
          readonly
        />

        <van-field
          v-model="attribute.name"
          name="名称"
          label="名称"
          placeholder="请输入名称"
          required
          :rules="[{ required: true, message: '请输入名称' }]"
        />

        <van-cell center title="能否检索">
          <template #right-icon>
            <van-radio-group v-model.number="attribute.index" direction="horizontal">
              <van-radio :name="1">关键字检索</van-radio>
              <van-radio :name="0">不要检索</van-radio>
            </van-radio-group>
          </template>
        </van-cell>

        <van-cell center title="能否检索">
          <template #right-icon>
            <van-radio-group v-model.number="attribute.input_type" direction="horizontal">
              <van-radio :name="1">手工录入</van-radio>
              <van-radio :name="2">从下面的列表中选择(一行代表一个可选值)</van-radio>
              <van-radio :name="3">多行文本框</van-radio>
            </van-radio-group>
          </template>
        </van-cell>

        <van-field
          v-model="attribute.values"
          type="textarea"
          name="名称"
          label="可选值列表"
          placeholder="请输入可选值"
          :disabled="attribute.input_type!=2"
          autosize
        />

        <van-field
          v-model.number="attribute.sort_order"
          name="sort_order"
          label="排序"
          placeholder="请输入排序"
          type="digit"
          required
          :rules="[{ required: true, message: '请输入排序' }]"
        />

        <div class="md15">
          <van-button round block type="info" native-type="submit">
            保存
          </van-button>
        </div>
      </van-form>
    </div>
  </section>
</template>
<script>
  import {goodsTypeInfoUri, goodsAttrSaveUri, goodsAttrInfoUri} from "../../../common/api";
  import Vue from 'vue';
  import {Toast, Dialog} from 'vant';
  import PageHeader from "../../componets/PageHeader";
  Vue.use(Toast).use(Dialog);

  export default {
    components: {
      PageHeader
    },
    data () {
      return {
        goodsType: {id:0, name: ''},
        attribute: {
          id: 0,
          name: '',
          type_id: 0,
          index: 1,
          input_type: 1,
          values: '',
          sort_order: 255
        }
      }
    },
    methods: {
      fetchData() {
        let self = this;
        self.$http.get(goodsAttrInfoUri + '?id=' + this.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast.fail(msg)
          } else {
            self.attribute = data.attribute;
          }
        }, response => {
          // error callback
        })
      },
      getGoodsTypeInfo() {
        let self = this;
        self.$http.get(goodsTypeInfoUri + '?id=' + this.$route.params.typeId).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast.fail(msg)
          } else {
            self.goodsType = data.type;
          }
        }, response => {
          // error callback
        })
      },
      onFormSubmit(values) {
        const that = this;
        that.$http.post(goodsAttrSaveUri, that.attribute, {emulateJSON: true}).then(response => {
          let {msg, code, data} = response.body
          if (code != 0) {
            Toast.fail(msg)
          } else {
            Toast.success('保存成功');
            that.$router.back();
          }
        }, response => {
          Toast.fail('发生错误')
        })
      }
    },
    mounted: function () {
      this.attribute.type_id = this.$route.params.typeId;
      this.getGoodsTypeInfo();
      if(this.$route.name == 'HealthServiceAttrEdit') {
        this.fetchData();
      }
    }
  }
</script>
