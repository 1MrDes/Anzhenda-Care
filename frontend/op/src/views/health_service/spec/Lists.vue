<template>
  <section>
    <div class="pd10">
      <van-button type="primary"
                  class="w100"
                  @click="$router.push({name: 'HealthServiceSpecAdd', params: {typeId: $route.params.typeId}})">+添加规格
      </van-button>
    </div>

    <div class="bg-color-white">
      <van-row class="cate-item">
        <van-col :span="6" class="pl10 text-left strong">名称</van-col>
        <van-col :span="13" class="pr10 text-left strong">规格项</van-col>
        <van-col :span="5" class="pr10 text-right strong">操作</van-col>
      </van-row>

      <van-row v-for="(item, index) in specs" :key="index" class="cate-item">
        <van-col :span="6" class="pl10 text-left">{{item.name}}</van-col>
        <van-col :span="13" class="pl10 text-left">
          <span v-for="(item2, index2) in item.items">{{item2.item}},</span>
        </van-col>
        <van-col :span="5" class="pr10 text-right">
          <van-icon name="edit" @click="$router.push({name: 'HealthServiceSpecEdit', params: {typeId: typeId, id: item.id}})" />
          <van-icon name="delete" class="ml10" @click="del(index)" />
        </van-col>
      </van-row>
    </div>
  </section>
</template>

<script>
  import Vue from 'vue';
  import {Toast, Dialog} from 'vant';
  import {goodsTypeSpecAllUri, goodsTypeSpecDeleteUri} from "../../../common/api";
  Vue.use(Toast).use(Dialog);

  export default {
    data() {
      return {
        typeId: 0,
        specs: []
      }
    },
    methods: {
      del(index) {
        const that = this;
        Dialog.confirm({
          message: '确定要删除吗？'
        }).then(() => { // on confirm
          that.$http.get(goodsTypeSpecDeleteUri + '?id=' + that.specs[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              Toast.fail(msg);
            } else {
              that.specs.splice(index, 1);
            }
          }, response => {
            // error callback
          })
        }).catch(() => {  // on cancel

        });
      },
      requestData() {
        const that = this;
        const url = goodsTypeSpecAllUri + '?type_id=' + this.$route.params.typeId;
        this.$http.get(url).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            Toast.fail(msg);
          } else {
            that.specs = data.specs;
          }
        }, response => {
          // error callback
        })
      }
    },
    mounted: function () {
      this.typeId = this.$route.params.typeId;
      this.requestData();
    }
  }
</script>

<style scoped>

</style>
