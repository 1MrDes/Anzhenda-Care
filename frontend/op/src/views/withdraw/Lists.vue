<template>
  <section>
    <van-sticky>
      <PageHeader title="提现管理"/>
    </van-sticky>

    <form action="/">
      <van-search
        v-model="keywords"
        placeholder="请输入用户手机号/昵称"
        @search="onSearch"
      />
    </form>

    <van-list
      class="mt10"
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="requestData">
      <div class="flex flex-col mt10 pd10 bg-color-white font-common"
           v-for="(item, index) in dataList" :key="index">
        <div class="flex flex-row pb10">
          <div class="flex flex-row flex-centerV flex-left flex-1">
            <img :src="item.user.avatar_url ? item.user.avatar_url : BASE_STATIC_URL + 'images/avatar.jpg'"
                 class="user-avatar"/>
            <span class="ml5">{{ item.user.nick }}</span>
          </div>
          <div class="flex flex-row flex-centerV flex-right flex-1">
            申请时间：{{ item.apply_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}
          </div>
        </div>

        <div class="flex flex-row pb10 pt10 border-top-gray">
          <div class="flex flex-row flex-centerV flex-left flex-1">
            金额：{{ item.amount }}元
          </div>
          <div class="flex flex-row flex-centerV flex-right flex-1">
            状态：{{ item.status_label }}
          </div>
        </div>

        <div class="flex flex-row pb10 pt10 border-top-gray">
          <div class="flex flex-row flex-centerV flex-left flex-1">
            支付宝姓名：{{ item.alipay_name }}
          </div>
          <div class="flex flex-row flex-centerV flex-right flex-1">
            支付宝账号：{{ item.alipay_email }}
          </div>
        </div>

        <template v-if="item.status == WITHDRAW_CASH_STATUS_PAIED">
          <div class="flex flex-row pb10 pt10 border-top-gray">
            <div class="flex flex-row flex-centerV flex-left flex-1">
              打款平台：{{ item.pay_type_label }}
            </div>
            <div class="flex flex-row flex-centerV flex-right flex-1">
              打款时间：{{ item.pay_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}
            </div>
          </div>

          <div class="flex flex-row pb10 pt10 border-top-gray">
            打款平台流水号：{{ item.payment_iid }}
          </div>
        </template>

        <div class="flex flex-row pb10 pt10 border-top-gray" v-if="item.remark">
          备注：{{ item.remark }}
        </div>

        <template v-if="item.status == WITHDRAW_CASH_STATUS_AUDITTING">
          <div class="flex flex-row pb10 pt10 border-top-gray">
            <div class="flex flex-row flex-center flex-1 bg-color-red pb10 pt10 color-white" @click="onReject(index)">
              拒绝申请
            </div>
            <div class="flex flex-row flex-center flex-1 bg-color-green pb10 pt10 color-white" @click="onAgree(index)">
              通过申请
            </div>
          </div>
        </template>

        <template v-else-if="item.status == WITHDRAW_CASH_STATUS_WAIT_PAY">
          <div class="flex flex-row pb10 pt10 border-top-gray">
            <div class="flex flex-row flex-center flex-1 bg-color-green pb10 pt10 color-white" @click="onShowPayTypePicker(index)">
              打款
            </div>
          </div>
        </template>

      </div>
    </van-list>

    <Tabbar active="member"/>

    <van-action-sheet v-model="payTypePickerVisible"
                      :actions="payTypePickerActions"
                      @select="onPayTypePickerSelect"
                      cancel-text="取消"
                      close-on-click-action
                      @cancel="payTypePickerVisible=false" />
    <van-dialog v-model="rejectFormVisible" title="拒绝申请" show-cancel-button @confirm="onRejectFormConfirm">
      <form action="/">
        <van-field
          v-model="rejectForm.remark"
          name="remark"
          label="说明"
          placeholder="请输入说明"
          type="textarea"
        />
      </form>
    </van-dialog>
  </section>
</template>

<script>
import PageHeader from "../componets/PageHeader";
import Tabbar from '../componets/Tabbar';
import {
  withdrawCashListsUri,
  STATIC_BASE_URL,
  withdrawCashRejectUri,
  withdrawCashAgreeUri,
  withdrawCashPayUri
} from "../../common/api";
import {
  WITHDRAW_CASH_STATUS_PAIED,
  WITHDRAW_CASH_STATUS_AUDITTING,
  WITHDRAW_CASH_STATUS_WAIT_PAY,
  WITHDRAW_CASH_STATUS_REJECTED,
  WITHDRAW_CASH_PAY_TYPE_WEPAY,
  WITHDRAW_CASH_PAY_TYPE_ALIPAY
} from "../../common/constants";
import Vue from 'vue';
import {Dialog} from "vant";
Vue.use(Dialog);

export default {
  components: {
    PageHeader,
    Tabbar
  },
  data() {
    return {
      BASE_STATIC_URL: STATIC_BASE_URL,
      WITHDRAW_CASH_STATUS_PAIED: WITHDRAW_CASH_STATUS_PAIED,
      WITHDRAW_CASH_STATUS_AUDITTING: WITHDRAW_CASH_STATUS_AUDITTING,
      WITHDRAW_CASH_STATUS_WAIT_PAY: WITHDRAW_CASH_STATUS_WAIT_PAY,
      WITHDRAW_CASH_STATUS_REJECTED: WITHDRAW_CASH_STATUS_REJECTED,
      WITHDRAW_CASH_PAY_TYPE_WEPAY: WITHDRAW_CASH_PAY_TYPE_WEPAY,
      WITHDRAW_CASH_PAY_TYPE_ALIPAY: WITHDRAW_CASH_PAY_TYPE_ALIPAY,

      currentPage: 1,
      lastPage: 0,
      total: 0,
      pageSize: 10,
      loading: false,
      finished: false,
      dataList: [],
      keywords: '',

      rejectFormVisible: false,
      rejectForm: {index: -1, remark: ''},

      payTypePickerVisible: false,
      payTypePickerActions: [
        {name: '微信支付', pay_type: WITHDRAW_CASH_PAY_TYPE_WEPAY},
        {name: '支付宝', pay_type: WITHDRAW_CASH_PAY_TYPE_ALIPAY}
      ],
      currentItemIndex: -1
    }
  },
  methods: {
    onShowPayTypePicker(index) {
      const that = this;
      Dialog.confirm({
        message: '确定打款吗？'
      }).then(() => { // on confirm
        that.currentItemIndex = index;
        that.payTypePickerVisible = true;
      }).catch(() => {  // on cancel

      });
    },
    onPayTypePickerSelect(item) {
      this.payTypePickerVisible = false;
      this.onPay(this.currentItemIndex, item.pay_type);
    },
    onReject(index) {
      this.rejectForm.index = index;
      this.rejectFormVisible = true;
    },
    onRejectFormConfirm() {
      this.rejectFormVisible = false;
      const postData = {
        id: this.dataList[this.rejectForm.index].id,
        remark: this.rejectForm.remark
      };
      const that = this;
      that.$http.post(withdrawCashRejectUri, postData, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          that.toast.fail(msg);
        } else {
          that.$set(that.dataList, that.rejectForm.index, data);
          that.toast.success('操作成功');
        }
      }, response => {
        that.toast.fail('发生错误')
      })
    },
    onAgree(index) {
      const that = this;
      Dialog.confirm({
        message: '确定通过该申请吗？'
      }).then(() => { // on confirm
        that.$http.get(withdrawCashAgreeUri + '?id=' + that.dataList[index].id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.toast.fail(msg);
          } else {
            that.$set(that.dataList, index, data);
            that.toast.success('操作成功');
          }
        }, response => {
          // error callback
        })
      }).catch(() => {  // on cancel

      });
    },
    onPay(index, payType) {
      const that = this;
      that.$http.get(withdrawCashPayUri + '?id=' + that.dataList[index].id + '&pay_type=' + payType).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.toast.fail(msg);
        } else {
          that.$set(that.dataList, index, data);
          that.toast.success('操作成功');
        }
      }, response => {
        // error callback
      });
    },
    onSearch() {
      this.currentPage = 1;
      this.lastPage = 0;
      this.total = 0;
      this.loading = false;
      this.finished = false;
      this.dataList = [];
      this.requestData();
    },
    requestData() {
      const that = this;
      this.loading = true;
      const url = withdrawCashListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage + '&keywords=' + encodeURIComponent(that.keywords);
      this.$http.get(url).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.toast.fail(msg);
        } else {
          that.loading = false;
          if (that.currentPage >= data.last_page) {
            that.finished = true;
          }
          that.currentPage = that.currentPage + 1;
          for (let i = 0; i < data.data.length; i++) {
            that.dataList.push(data.data[i]);
          }
          that.total = data.total;
          that.lastPage = data.last_page;
        }
      }, response => {
        // error callback
      })
    },
  },
  mounted: function () {

  }
}
</script>

<style scoped>
.user-avatar {
  height: 20px;
  width: 20px;
  border-radius: 10px;
}
</style>
