<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/11/13 13:35
 * @description
 -->
<template>
  <section>
    <div class="bg-color-white pd10">
      <van-form @submit="onFormSubmit">
        <van-field
          v-model="goodsType.name"
          name=""
          label="服务类型"
          readonly
        />

        <van-field
          v-model="spec.name"
          name="名称"
          label="名称"
          placeholder="请输入名称"
          :rules="[{ required: true, message: '请输入名称' }]"
        />

        <van-field
          v-model="spec.items"
          type="textarea"
          name="items"
          label="规格项"
          placeholder="一行一个"
          autosize
          :rules="[{ required: true, message: '请输入规格项' }]"
        />

        <van-field
          v-model.number="spec.sort_order"
          name="sort_order"
          label="排序"
          placeholder="请输入排序"
          :rules="[{ required: true, message: '请输入排序' }]"
          type="number"
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
  import {goodsTypeInfoUri, goodsTypeSpecSaveUri, goodsTypeSpecInfoUri} from "../../../common/api";
  import Vue from 'vue';
  import {Toast, Dialog} from 'vant';
  Vue.use(Toast).use(Dialog);

  export default {
    data () {
      return {
        goodsType: {id:0, name: ''},
        spec: {
          id: 0,
          name: '',
          type_id: '',
          items: '',
          sort_order: 255
        }
      }
    },
    methods: {
      fetchData() {
        let self = this;
        self.$http.get(goodsTypeSpecInfoUri + '?id=' + this.$route.params.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast.fail(msg);
          } else {
            let spec = data.spec;
            let items = [];
            for (let i = 0; i < spec.items.length; i++) {
              items.push(spec.items[i].item);
            }
            spec.items = items.join("\n");
            self.spec = spec;
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
        that.$http.post(goodsTypeSpecSaveUri, that.spec, {emulateJSON: true}).then(response => {
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
      this.spec.type_id = this.$route.params.typeId;
      this.getGoodsTypeInfo();
      if(this.$route.name == 'HealthServiceSpecEdit') {
        this.fetchData();
      }
    }
  }
</script>
