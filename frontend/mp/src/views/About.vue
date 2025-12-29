<template>
  <section>
    <div class="pd10 bg-color-white">
      <div class="text-center">
        <img :src="IMG_LOGO" height="60" width="60" />
      </div>

    </div>
  </section>
</template>

<script>
  import {APP_NAME} from "../common/constants";

  const IMG_LOGO = require('../assets/images/logo.jpg');

  export default {
    data() {
      return {
        APP_NAME: APP_NAME,
        IMG_LOGO: IMG_LOGO
      }
    },
    methods: {},
    mounted: function () {

    }
  }
</script>

<style scoped>
  p.p1 {
    margin: 0;
    line-height: 19px;
    font: 13px 'Helvetica Neue'
  }

  p.p2 {
    margin: 0;
    line-height: 19px;
    font: 13px 'Helvetica Neue';
    min-height: 15px
  }
</style>
