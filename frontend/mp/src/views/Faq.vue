<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2019/10/31
 * @description
 -->
<template>
  <section>
    <div class="pd10 font-common">
      <van-collapse v-model="activeNames" accordion>
<!--        <van-collapse-item name="1">-->
<!--          <div slot="title">-->
<!--            <van-icon name="question-o" color="#e86321" />-->
<!--            <span class="strong">记错了，如何删除记录？</span>-->
<!--          </div>-->
<!--          <div>-->
<!--            首页每日记录列表中长按某一条记录将会提示是否要删除，选择确定后记录将被删除，如下图所示：-->
<!--          </div>-->
<!--          <div class="mt5">-->
<!--            <img :src="STATIC_BASE_URL + 'images/faq-1.jpg'" width="70%" />-->
<!--          </div>-->
<!--          <div class="mt5">-->
<!--            <img :src="STATIC_BASE_URL + 'images/faq-2.jpg'" width="70%" />-->
<!--          </div>-->
<!--        </van-collapse-item>-->

      </van-collapse>
    </div>
  </section>
</template>
<script>
  import Vue from 'vue';
  import {Collapse, CollapseItem, Toast, Button} from 'vant';
  Vue.use(Collapse).use(CollapseItem).use(Toast).use(Button);
  import {APP_NAME} from '../common/constants';
  import {STATIC_BASE_URL} from '../common/api';

  export default {
    data() {
      return {
        APP_NAME: APP_NAME,
        activeNames: '1',
        secondActiveNames: '1',
        STATIC_BASE_URL: STATIC_BASE_URL
      }
    },
    methods: {
      onClipboardClick(content) {
        if (content.length == 0) {
          return;
        }
        const that = this;
        this.$copyText(content).then(function (e) {
          Toast.success('复制成功');
        }, function (e) {
          Toast.fail('复制失败');
        })
      }
    },
    mounted: function () {

    }
  }
</script>
<style scoped>

</style>
