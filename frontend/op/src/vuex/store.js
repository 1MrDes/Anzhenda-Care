/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-9 14:14
 * Description:
 */
import Vue from 'vue'
import Vuex from 'vuex'
import * as actions from './actions'
import * as getters from './getters'
import mutations from './mutations'
Vue.use(Vuex);

// 应用初始状态
const state = {
  count: 10,
  checkedGoods: []
}

// 创建 store 实例
export default new Vuex.Store({
  actions,
  getters,
  state,
  mutations
})
