/**
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-9 14:19
 * Description:
 */
// 定义所需的 mutations
export default {
  INCREMENT(state) {
    state.count++
  },
  DECREMENT(state) {
    state.count--
  },
  addCheckedGoods(state, goodsList) {
    let checkedGoods = state.checkedGoods;
    for (let i = 0; i < goodsList.length; i++) {
      let checked = false;
      for (let j = 0; j < checkedGoods.length; j++) {
        if(goodsList[i].id == checkedGoods[j].id) {
          checked = true;
          break;
        }
      }
      if(!checked) {
        checkedGoods.push(goodsList[i]);
      }
    }
    state.checkedGoods = checkedGoods;
  }
}
