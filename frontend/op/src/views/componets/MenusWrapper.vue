<template>
  <van-popup v-model="menusPopupVisible" position="top" :style="{ height: '30%', width: '100%' }"
             @close="onMenusPopupClose">
    <div class="flex flex-row flex-center mt20">
      <div class="flex flex-col flex-1 flex-center" @click="$router.push({name: 'MemberIndex'})">
        <div class="flex flex-row flex-center round-icon" style="background: #ff709f;">
          <i class="iconfont icon-icon-test" style="font-size: 22px;" />
        </div>
        <div class="flex flex-row flex-center font-common mt5">
          缘分
        </div>
      </div>

      <div class="flex flex-col flex-1 flex-center" @click="$router.push({name: 'ActivityIndex'})">
        <div class="flex flex-row flex-center round-icon" style="background: #ffaf52;">
          <i class="iconfont icon-jiehun" style="font-size: 22px;" />
        </div>
        <div class="flex flex-row flex-center font-common mt5">
          活动
        </div>
      </div>

      <div class="flex flex-col flex-1 flex-center" @click="$router.push({name: 'CollegeIndex'})">
        <div class="flex flex-row flex-center round-icon" style="background: #24aaff;">
          <i class="iconfont icon-xueyuan" style="font-size: 22px;" />
        </div>
        <div class="flex flex-row flex-center font-common mt5">
          学院
        </div>
      </div>
    </div>

    <div class="flex flex-row flex-center mt20">
      <div class="flex flex-col flex-1 flex-center" @click="$router.push({name: 'MessageIndex'})">
        <div class="flex flex-row flex-center round-icon" style="background: #ff7624;">
          <i class="iconfont icon-xiaoxi" style="font-size: 22px;" />
        </div>
        <div class="flex flex-row flex-center font-common mt5">
          消息
        </div>
      </div>

      <div class="flex flex-col flex-1 flex-center" @click="$router.push({name: 'MyIndex'})">
        <div class="flex flex-row flex-center round-icon" style="background: #ffca57;">
          <i class="iconfont icon-wode" style="font-size: 22px;" />
        </div>
        <div class="flex flex-row flex-center font-common mt5">
          我的
        </div>
      </div>

      <div class="flex flex-col flex-1 flex-center" @click="$router.push({name: 'MatchmakerIndex'})">
        <div class="flex flex-row flex-center round-icon" style="background: #f74181;">
          <i class="iconfont icon-hongniang-copy" style="font-size: 22px;" />
        </div>
        <div class="flex flex-row flex-center font-common mt5">
          兼职红娘
        </div>
      </div>
    </div>
  </van-popup>
</template>

<script>
    export default {
      name: 'MenusWrapper',
      data() {
        return {
          menusPopupVisible: true
        }
      },
      methods: {
        onMenusPopupClose() {
          this.$emit('close');
        }
      }
    }
</script>

<style scoped>
  .round-icon {
    height: 36px;
    width: 36px;
    border-radius: 18px;
    color: #FFFFFF;
  }
</style>
