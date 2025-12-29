<template>
  <section>
    <van-sticky>
      <PageHeader title="订单管理"/>
    </van-sticky>

    <template v-if="order">
      <div class="flex flex-row flex-left flex-centerV bg-color-white font-common color-dark pd10 mt10">
        <div class="flex flex-row flex-left flex-centerV">
          <label>买家：{{ buyer.nick }}</label>
        </div>
      </div>

      <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray">
        <div class="flex flex-row flex-left flex-centerV">
          <label>订单状态：</label><label class="color-green">{{ order.order_status_label }}</label>
        </div>
      </div>

      <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray">
        <div class="flex flex-row flex-2 flex-left">
          <label>订单号：{{ order.order_sn }}</label>
        </div>
      </div>

      <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray">
        <label>订单日期：{{ order.create_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}</label>
      </div>

      <template v-if="order.pay_time>0">
        <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray">
          <label>付款时间：{{ order.pay_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}</label>
        </div>

        <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray">
          <label>付款方式：{{ order.payment_name }}</label>
        </div>

        <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray">
          <label>支付平台流水号：{{ order.transaction_id }}</label>
        </div>
      </template>

      <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray"
           v-if="order.shipping_time>0">
        <label>发货时间：{{ order.shipping_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}</label>
      </div>

      <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray" v-if="order.express_sn">
        <div class="flex flex-row flex-2 flex-left">
          <label>快递单号：{{ order.express_sn }}</label>
        </div>

        <div class="flex flex-row flex-1 flex-right">
          <a
            :href="'https://m.kuaidi100.com/app/query/?com='+order.express_code+'&nu='+order.express_sn+'&coname=qfanqie'"
            target="_blank">物流进度</a>
        </div>
      </div>

      <div class="flex flex-row bg-color-white font-common color-dark pd10 border-top-gray">
        <label>订单金额：</label>
        <label class="color-red">￥{{ order.order_money }}</label>
      </div>
      <!--服务-->
      <div class="flex flex-col bg-color-white border-top-gray font-common mt10 order-items"
           v-for="(orderItem, idx) in orderItems" :key="idx">
        <div class="flex flex-row pt20 pb20">
          <div class="flex flex-row center cl pl20">
            <img :src="orderItem.health_service_image_url" class="image"/>
          </div>

          <div class="flex flex-1 flex-col cl pl20">
            <div class="flex flex-row flex-left w100">
              <label class="strong">{{ orderItem.health_service_name }}</label>
            </div>
            <div class="flex flex-row flex-left w100" v-html="orderItem.spec_sku_key_name"></div>
          </div>

          <div class="flex flex-col flex-center cl pl20 pr20">
            <div class="flex flex-row flex-right w100">
              <label class="color-red">￥{{ orderItem.health_service_price }}</label>
            </div>
            <div class="flex flex-row flex-right w100">
              <label class="color-gray">X{{ orderItem.quantity }}</label>
            </div>
          </div>
        </div>

        <div class="flex flex-row flex-right pt20 pb20 pr20 border-top-gray">
          <label class="color-gray">共{{ orderItem.quantity }}件服务，</label>
          <label class="color-red">小计：￥{{ orderItem.health_service_price * orderItem.quantity }}</label>
        </div>
      </div>

      <!--就医信息-->
      <div class="flex flex-col bg-color-white font-common mt10">
        <div class="flex flex-row pd10">
          <div class="flex flex-1 flex-row flex-left">
            就诊医院：{{ order.hospital_name }}
          </div>
        </div>

        <div class="flex flex-row flex-left pd10" v-if="order.hospital_lab">
          就诊科室：{{ order.hospital_lab }}
        </div>

        <div class="flex flex-row flex-left pd10" v-if="order.clinic_time">
          就诊时间：{{ order.clinic_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}
        </div>

        <div class="flex flex-row flex-left pd10">
          联系人：{{ order.contact_name }}
        </div>

        <div class="flex flex-row flex-left pd10">
          联系电话：<a :href="'tel:' + order.contact_tel">{{ order.contact_tel }}</a>
        </div>

        <div class="flex flex-row flex-left pd10">
          陪诊师：<span v-if="order.health_assistant_uid > 0">{{ order.health_assistant.nick }}(<a
          :href="'tel:' + order.health_assistant.mobile">{{ order.health_assistant.mobile }}</a>)</span><span
          v-else>未指派</span>
        </div>
      </div>

      <!--收货信息-->
      <div class="flex col bg-color-white font-common mt10"
           v-if="order.shipping_type==HEALTH_SERVICE_ORDER_SHIPPING_TYPE_EXPRESS">
        <div class="flex flex-row pd10">
          <div class="flex flex-1 flex-row flex-left">
            {{ order.consignee }}
          </div>

          <div class="flex flex-1 flex-row flex-right">
            {{ order.mobile }}
          </div>
        </div>

        <div class="flex flex-row flex-left pd10 color-gray">
          {{ order.region_name }}{{ order.address }}({{ order.zipcode }})
        </div>
      </div>
      <!--留言-->
      <div class="flex flex-row mt10 font-common bg-color-white pd10" v-if="order.remark">
        <div class="flex flex-row flex-left pr20">
          留言：
        </div>
        <div class="flex flex-1 flex-row flex-left color-gray">
          {{ order.remark }}
        </div>
      </div>

      <div class="flex flex-row white-wrapper"/>

      <!--底部按钮-->
      <div class='flex flex-row flex-centerV flex-right bg-color-white footer'>
        <div class="flex flex-row flex-right pd10">
          <div class="flex flex-row"
               v-if="order.order_status==ORDER_STATUS_UNCONFIRMED">
            <van-button plain type="info" size="small" class="ml20" @click="onShowDialog('confirm', '', '确定接单吗？')">
              确认订单
            </van-button>
          </div>
          <div class="flex flex-row"
               v-if="(order.order_status==ORDER_STATUS_UNCONFIRMED || order.order_status==ORDER_STATUS_CONFIRMED) && order.payment_status==PAYMENT_STATUS_UNPAIED">
            <van-button plain type="info" size="small" class="ml20" @click="onShowDialog('cancel', '', '确定要取消订单？')">
              取消订单
            </van-button>
            <van-button plain type="info" size="small" class="ml20" @click="showModifyOrderMoneyPopup">修改金额</van-button>
            <van-button type="danger" size="small" class="ml20" @click="payFormPopupVisible=true">确认付款</van-button>
          </div>

          <div class="flex flex-row"
               v-else-if="(order.order_status==ORDER_STATUS_WAIT_SHIP || order.order_status==ORDER_STATUS_SHIPPED || order.order_status==ORDER_STATUS_REFUNDING || order.order_status==ORDER_STATUS_ACTING) && order.payment_status==PAYMENT_STATUS_PAIED">
            <van-button plain type="info" size="small" class="ml20" v-if="order.refund_status==REFUND_STATUS_AUDITING"
                        @click="op('refund', 'confirm')">确认退款
            </van-button>
            <van-button plain type="info" size="small" class="ml20"
                        v-else-if="order.refund_status==REFUND_STATUS_GOODS_RETURNING"
                        @click="op('refund','return_goods')">确认已退货
            </van-button>
            <van-button plain type="info" size="small" class="ml20"
                        v-else-if="order.refund_status==REFUND_STATUS_PAYING" @click="op('refund','pay')">确认打款
            </van-button>
            <van-button plain type="info" size="small" class="ml20" v-else
                        @click="onShowDialog('refund','start', '确定发起退款吗？')">发起退款
            </van-button>

            <van-button type="info" size="small" class="ml20"
                        @click="onShowAssistantPopupTap">派单
            </van-button>
            <van-button type="info" size="small" class="ml20" v-if="order.order_status==ORDER_STATUS_SHIPPED"
                        @click="onShowDialog('acting', '', '确定开始服务吗？')">开始服务
            </van-button>
            <van-button type="info" size="small" class="ml20" v-if="order.order_status==ORDER_STATUS_ACTING"
                        @click="onShowDialog('finish', '', '确认已完成吗？')">确认完成
            </van-button>
          </div>

          <div class="flex flex-row"
               v-if="order.shipping_type==HEALTH_SERVICE_ORDER_SHIPPING_TYPE_EXPRESS && order.express_code=='' && order.payment_status==PAYMENT_STATUS_PAIED">
            <van-button plain type="info" size="small" class="ml20" @click="shipFormPopupVisible=true">填写快递信息
            </van-button>
          </div>
        </div>
      </div>

      <van-popup closeable v-model="modifyOrderMoneyPopupVisible" class="pd10" style="width: 80%;">
        <van-form @submit="onModifyOrderMoneyFormSubmit">
          <van-field
            v-model.number="order.order_money"
            name="order_money"
            label="订单金额"
            placeholder="请输入订单金额"
            type="number"
            :rules="[{ required: true, message: '请输入订单金额' }]"
          />
          <div style="margin: 16px;">
            <van-button round block type="info" native-type="submit">
              确定
            </van-button>
          </div>
        </van-form>
      </van-popup>

      <van-popup closeable v-model="shipFormPopupVisible" class="pd10" style="width: 80%;">
        <van-form @submit="onShipFormSubmit">
          <van-field name="express_code" label="物流公司">
            <template #input>
              <van-radio-group v-model="order.express_code" direction="horizontal">
                <van-radio :name="item.value" v-for="(item, index) in dropOptions" :key="index">{{ item.text }}
                </van-radio>
              </van-radio-group>
            </template>
          </van-field>

          <van-field
            v-model.number="order.express_sn"
            name="express_sn"
            label="物流单号"
            placeholder="请输入物流单号"
            :rules="[{ required: true, message: '请输入物流单号' }]"
          />
          <div style="margin: 16px;">
            <van-button round block type="info" native-type="submit">
              确定
            </van-button>
          </div>
        </van-form>
      </van-popup>

      <van-popup closeable v-model="payFormPopupVisible" class="pd10" style="width: 95%;">
        <van-form @submit="onPayFormSubmit">
          <van-field
            v-model="order.payment_name"
            name="payment_name"
            label="支付平台"
            placeholder="请输入支付平台名称"
            :rules="[{ required: true, message: '请输入支付平台名称' }]"
          />

          <van-field
            v-model="order.transaction_id"
            name="transaction_id"
            label="支付订单号"
            placeholder="请输入支付订单号"
            :rules="[{ required: true, message: '请输入支付订单号' }]"
          />

          <van-field name="pay_time" label="付款时间">
            <template #input>
              <van-datetime-picker
                v-model="payTime"
                type="datetime"
                :show-toolbar="false"
                @change="onPaytimeChange"
              />
            </template>
          </van-field>

          <div style="margin: 16px;">
            <van-button round block type="info" native-type="submit">
              确定
            </van-button>
          </div>
        </van-form>
      </van-popup>

      <van-popup closeable v-model="assistantPopupVisible" class="pd10" style="width: 90%; height: 90%;">
        <van-list
          class="mt10"
          v-model="loading"
          :finished="finished"
          finished-text="没有更多了"
          @load="getAssistants">
          <van-radio-group v-model="assistantCheckedIndex">
            <div class="flex flex-row flex-center font-common pt10 pb10 border-bottom-gray el-assistant"
                 v-for="(item, index) in dataList"
                 :key="index">
              <div class="flex flex-row flex-left flex-1 flex-centerV">
                <van-radio :name="index" @click="onAssistantItemClick"/>
                <img :src="item.user.avatar_url" class="avatar ml10"/>
                <span class="ml5">{{ item.user.nick }}</span>
              </div>

              <div class="flex flex-row flex-right">
                服务地区：{{ item.region_name }}
              </div>
            </div>
          </van-radio-group>
        </van-list>
      </van-popup>
    </template>
  </section>
</template>

<script>
import Vue from 'vue';
import {
  orderInfoUri,
  orderOpUri,
  orderShipUri,
  orderPayUri,
  orderModifyMoneyUri,
  advDeleteUri, healthAssistantListsUri, orderAssignAssistantUri
} from "../../common/api";
import {Toast, Dialog} from "vant";
import {
  ORDER_STATUS_CONFIRMED,
  ORDER_STATUS_WAIT_SHIP,
  ORDER_STATUS_SHIPPED,
  ORDER_STATUS_FINISHED,
  ORDER_STATUS_CANCELLED,
  ORDER_STATUS_REFUNDED,
  ORDER_STATUS_REFUNDING,
  PAYMENT_STATUS_UNPAIED,
  PAYMENT_STATUS_PAIED,
  SHIPPING_STATUS_WAIT_SHIP,
  SHIPPING_STATUS_SHIPPED,
  SHIPPING_STATUS_REFUNDED,
  REFUND_STATUS_AUDITING,
  REFUND_STATUS_GOODS_RETURNING,
  REFUND_STATUS_PAYING,
  REFUND_STATUS_FINISHED,
  HEALTH_SERVICE_ORDER_SHIPPING_TYPE_EXPRESS,
  HEALTH_SERVICE_ORDER_SHIPPING_TYPE_OFFLINE,
  ORDER_STATUS_ACTING, ORDER_STATUS_UNCONFIRMED
} from '../../common/constants';
import Tabbar from "../componets/Tabbar";
import PageHeader from "../componets/PageHeader";

Vue.use(Toast).use(Dialog);

export default {
  components: {
    Tabbar: Tabbar,
    PageHeader: PageHeader
  },
  data() {
    return {
      ORDER_STATUS_UNCONFIRMED: ORDER_STATUS_UNCONFIRMED,
      ORDER_STATUS_CONFIRMED: ORDER_STATUS_CONFIRMED,
      ORDER_STATUS_WAIT_SHIP: ORDER_STATUS_WAIT_SHIP,
      ORDER_STATUS_SHIPPED: ORDER_STATUS_SHIPPED,
      ORDER_STATUS_FINISHED: ORDER_STATUS_FINISHED,
      ORDER_STATUS_CANCELLED: ORDER_STATUS_CANCELLED,
      ORDER_STATUS_REFUNDED: ORDER_STATUS_REFUNDED,
      ORDER_STATUS_REFUNDING: ORDER_STATUS_REFUNDING,
      ORDER_STATUS_ACTING: ORDER_STATUS_ACTING,

      PAYMENT_STATUS_UNPAIED: PAYMENT_STATUS_UNPAIED,
      PAYMENT_STATUS_PAIED: PAYMENT_STATUS_PAIED,

      SHIPPING_STATUS_WAIT_SHIP: SHIPPING_STATUS_WAIT_SHIP,
      SHIPPING_STATUS_SHIPPED: SHIPPING_STATUS_SHIPPED,
      SHIPPING_STATUS_REFUNDED: SHIPPING_STATUS_REFUNDED,

      REFUND_STATUS_AUDITING: REFUND_STATUS_AUDITING,
      REFUND_STATUS_GOODS_RETURNING: REFUND_STATUS_GOODS_RETURNING,
      REFUND_STATUS_PAYING: REFUND_STATUS_PAYING,
      REFUND_STATUS_FINISHED: REFUND_STATUS_FINISHED,

      HEALTH_SERVICE_ORDER_SHIPPING_TYPE_EXPRESS: HEALTH_SERVICE_ORDER_SHIPPING_TYPE_EXPRESS,
      HEALTH_SERVICE_ORDER_SHIPPING_TYPE_OFFLINE: HEALTH_SERVICE_ORDER_SHIPPING_TYPE_OFFLINE,

      orderSn: '',
      order: null,
      orderItems: [],
      buyer: null,
      shipFormPopupVisible: false,
      dropOptions: [
        {text: '圆通快递', value: 'yuantong'},
        {text: '韵达快递', value: 'yuanda'},
        {text: 'EMS快递', value: 'ems'},
        {text: '邮政包裹', value: 'youzhengguonei'},
        {text: '申通', value: 'shentong'},
        {text: '顺丰速运', value: 'shunfeng'},
        {text: '如风达', value: 'rufengda'},
        {text: '中通速递', value: 'zhongtong'},
        {text: '京东快递', value: 'jingdong'},
        {text: '达达', value: 'dada'},
        {text: '顺丰同城', value: 'sfexpress'},
        {text: '极兔快递', value: 'jitu'},
        {text: '其他', value: 'NA'},
      ],
      payFormPopupVisible: false,
      payTime: new Date(),
      modifyOrderMoneyPopupVisible: false,

      currentPage: 1,
      lastPage: 0,
      total: 0,
      pageSize: 10,
      loading: false,
      finished: false,
      dataList: [],
      assistantPopupVisible: false,
      assistantCheckedIndex: -1
    }
  },
  methods: {
    onShowDialog(op, act, message) {
      const that = this;
      Dialog.confirm({
        message: message
      }).then(() => { // on confirm
        that.op(op, act);
      }).catch(() => {  // on cancel

      });
    },
    showModifyOrderMoneyPopup() {
      this.modifyOrderMoneyPopupVisible = true;
    },
    op(op, act) {
      const that = this;
      this.$http.get(orderOpUri + '?order_sn=' + this.orderSn + '&op=' + op + '&act=' + act).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          Toast.fail(msg);
        } else {
          that.info();
        }
      }, response => {
        // error callback
      })
    },
    onModifyOrderMoneyFormSubmit(values) {
      const that = this;
      const postData = {
        order_sn: this.orderSn,
        order_money: this.order.order_money
      };
      that.$http.post(orderModifyMoneyUri, postData, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          Toast.fail(msg)
        } else {
          Toast.success('操作成功');
          that.modifyOrderMoneyPopupVisible = false;
          that.info();
        }
      }, response => {
        Toast.fail('发生错误')
      })
    },
    onShipFormSubmit(values) {
      if (this.order.express_code.length == 0) {
        Toast.fail('请选择物流公司');
        return;
      }
      const that = this;
      const postData = {
        order_sn: this.orderSn,
        express_sn: this.order.express_sn,
        express_code: this.order.express_code
      };
      that.$http.post(orderShipUri, postData, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          Toast.fail(msg)
        } else {
          Toast.success('操作成功');
          that.shipFormPopupVisible = false;
          that.info();
        }
      }, response => {
        Toast.fail('发生错误')
      })
    },
    onPayFormSubmit(values) {
      if (this.order.pay_time == 0) {
        Toast.fail('请选择付款时间');
        return;
      }
      const that = this;
      const postData = {
        order_sn: this.orderSn,
        payment_name: this.order.payment_name,
        transaction_id: this.order.transaction_id,
        pay_time: this.order.pay_time
      };
      that.$http.post(orderPayUri, postData, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          Toast.fail(msg)
        } else {
          Toast.success('操作成功');
          that.payFormPopupVisible = false;
          that.info();
        }
      }, response => {
        Toast.fail('发生错误')
      })
    },
    onPaytimeChange(picker) {
      const values = picker.getValues();
      const timeStr = values[0] + '/' + values[1] + '/' + values[2] + ' ' + values[3] + ':' + values[4] + ':00';
      this.order.pay_time = (new Date(timeStr)).getTime() / 1000;
    },
    info() {
      const that = this;
      this.$http.get(orderInfoUri + '?order_sn=' + this.orderSn).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          Toast.fail(msg);
        } else {
          that.order = data.order;
          let items = [];
          for (let i = 0; i < data.items.length; i++) {
            let item = data.items[i];
            item.spec_sku_key_name = item.spec_sku_key_name.replaceAll("\\n", '<br />');
            items.push(item);
          }
          that.orderItems = items;
          that.buyer = data.buyer;
        }
      }, response => {
        // error callback
      })
    },
    onShowAssistantPopupTap() {
      this.assistantPopupVisible = true;
      this.currentPage = 1;
      this.lastPage = 0;
      this.total = 0;
      this.loading = false;
      this.finished = false;
      this.dataList = [];
      this.assistantCheckedIndex = -1;
      this.getAssistants();
    },
    onAssistantItemClick(e) {
      this.assistantPopupVisible = false;
      const uid = this.dataList[this.assistantCheckedIndex].user_id;
      const that = this;
      Dialog.confirm({
        message: '确定指派给该陪诊师吗?'
      }).then(() => { // on confirm
        that.$http.get(orderAssignAssistantUri + '?order_sn=' + that.orderSn + '&health_assistant_uid=' + uid).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            that.info();
          }
        }, response => {
          // error callback
        })
      }).catch(() => {  // on cancel

      });
    },
    getAssistants() {
      const that = this;
      this.loading = true;
      const url = healthAssistantListsUri + '?page_size=' + that.pageSize + '&page=' + that.currentPage;
      this.$http.get(url).then(response => {
        let {code, msg, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg);
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
    }
  },
  mounted: function () {
    this.orderSn = this.$route.params.sn;
    this.info();
  }
}
</script>

<style scoped>
.order-items .cl .image {
  height: 80px;
  width: 80px;
}

.footer {
  padding: 10px 0;
  height: 35px;
  width: 100%;
  position: fixed;
  bottom: 0px;
  left: 0px;
  z-index: 1;
}

.white-wrapper {
  height: 85px;
}

.el-assistant .avatar {
  height: 40px;
  width: 40px;
  border-radius: 20px;
}
</style>
