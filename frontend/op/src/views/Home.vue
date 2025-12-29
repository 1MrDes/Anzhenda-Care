<template>
  <section>

    <keep-alive>
      <router-view v-if="$route.meta.keepAlive">
        <!-- 这里是会被缓存的视图组件，比如列表A页面 -->
      </router-view>
    </keep-alive>

    <router-view v-if="!$route.meta.keepAlive">
      <!-- 这里是不被缓存的视图组件，比如详情B页面-->
    </router-view>

  </section>
</template>

<script>
  export default {
    data() {
      return {}
    },
    methods: {},
    mounted: function () {

    },
    beforeDestroy: function () {

    }
  }
</script>
