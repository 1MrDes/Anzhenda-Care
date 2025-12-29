<template>
  <van-overlay :show="true">
    <div class="flex flex-col flex-center" style="height: 100%;">
      <img :src="STATIC_BASE_URL + 'images/mp/loading.gif'" class="flex" height="80" width="80" />
    </div>
  </van-overlay>
</template>

<script>
  import Vue from 'vue';
  import {Overlay} from 'vant';
  import {STATIC_BASE_URL} from "../../common/api";
  Vue.use(Overlay);

  export default {
    name: "FullscreenLoading",
    data() {
      return {
        STATIC_BASE_URL: STATIC_BASE_URL
      }
    }
  }
</script>

<style scoped>
  .loading-wrapper {
    height: 100%;
    width: 100%;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: 10000;
  }
  .loading-wrapper img {
    height: 80px;
    width: 80px;
  }
</style>
