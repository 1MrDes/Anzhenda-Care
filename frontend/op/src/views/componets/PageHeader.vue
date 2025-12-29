<template>
  <section>
    <div class="flex flex-row flex-center bg-color-white pt10 pb10 border-bottom-gray">
      <div class="flex flex-center flex-row pl20 pr20" @click="$router.back()">
        <i class="iconfont icon-houtui"/>
      </div>

      <div class="flex flex-center flex-row flex-1 font-biger">
        {{title}}
      </div>

      <div class="flex flex-center flex-row pl20 pr20" @click="menusWrapperVisible=true">
        <i class="iconfont icon-gengduo"/>
      </div>
    </div>

    <MenusWrapper @close="onMenusWrapperClose" v-if="menusWrapperVisible" />
  </section>
</template>

<script>
  import MenusWrapper from "./MenusWrapper";
  import Vue from 'vue';
  import {Popup} from 'vant';
  Vue.use(Popup);

  export default {
    name: 'PageHeader',
    components: {
      MenusWrapper
    },
    props: {
      title: {
        type: String,
        default: ''
      }
    },
    data() {
      return {
        menusWrapperVisible: false
      }
    },
    methods: {
      onMenusWrapperClose() {
        this.menusWrapperVisible = false;
      }
    },
    mounted: function () {

    }
  }
</script>

<style scoped>

</style>
