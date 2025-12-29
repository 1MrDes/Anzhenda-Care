<template>
  <section>
    <transition name="fade" mode="out-in">
      <router-view></router-view>
    </transition>
  </section>
</template>

<script>
    export default {
        data() {
            return {

            }
        },
        methods: {

        },
        mounted: function () {

        },
        beforeDestroy: function () {

        }
    }
</script>
