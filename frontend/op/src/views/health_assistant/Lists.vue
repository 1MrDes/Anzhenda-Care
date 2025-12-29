<template>
  <section>
    <van-sticky>
      <PageHeader title="陪诊师管理"/>
    </van-sticky>

    <van-list
      class="mt10"
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="requestData">
      <div class="flex flex-col md10 round-box bg-color-white font-common"
           v-for="(item, index) in dataList"
           :key="index">
        <div class="flex flex-row flex-left flex-centerV pd10">
          <img :src="item.user.avatar_url" class="avatar" />
          <span class="ml5">{{item.user.nick}}</span>
        </div>

        <div class="flex flex-row flex-left flex-centerV pd10 border-top-gray">
          服务地区：{{item.region_name}}
        </div>

        <div class="flex flex-row flex-left flex-centerV pd10 border-top-gray">
          状态：{{item.status_label}}
        </div>

        <div class="flex flex-row flex-left flex-centerV pd10 border-top-gray" v-if="item.audit_reason">
          审核备注：{{item.audit_reason}}
        </div>

        <div class="flex flex-row flex-center pt10 pb10 border-top-gray bg-color-green color-white"
             style="border-bottom-right-radius: 8px;border-bottom-left-radius: 8px;"
             @click="onShowFormPopupVisibleTap(index)"
             v-if="item.status==HEALTH_ASSISTANT_STATUS_WAIT_AUDIT">
          处理申请
        </div>
      </div>
    </van-list>

    <van-overlay :show="formPopupVisible" @click="formPopupVisible = false">
      <div class="flex flex-col flex-center h100" @click.stop>
        <div class="flex flex-col bg-color-white" style="width: 80%;">
          <div class="flex flex-row flex-center pd10 font-common">
            <div class="flex flex-row flex-left pr10">
              状态：
            </div>
            <div class="flex flex-row flex-left flex-1">
              <van-radio-group v-model="formData.status" direction="horizontal">
                <van-radio :name="HEALTH_ASSISTANT_STATUS_AUDIT_PASS">通过</van-radio>
                <van-radio :name="HEALTH_ASSISTANT_STATUS_AUDIT_REJECT">不通过</van-radio>
              </van-radio-group>
            </div>
          </div>

          <div class="flex flex-row flex-center pd10">
            <van-field
              v-model="formData.reason"
              rows="5"
              autosize
              label="备注"
              type="textarea"
              placeholder="请输入备注"
            />
          </div>
          <div class="flex flex-row flex-center mt10 pt10 pb10 bg-color-green color-white" @click="onFormSubmit">
            确定
          </div>
        </div>

        <div class="flex flex-row flex-center pt10 pb10">
          <van-icon name="close" size="30" color="#ffffff" @click="formPopupVisible = false" />
        </div>
      </div>
    </van-overlay>
  </section>
</template>

<script>
import PageHeader from "../componets/PageHeader";
import {healthAssistantAuditUri, healthAssistantListsUri} from "../../common/api";
import {
  HEALTH_ASSISTANT_STATUS_AUDIT_PASS,
  HEALTH_ASSISTANT_STATUS_AUDIT_REJECT,
  HEALTH_ASSISTANT_STATUS_WAIT_AUDIT
} from "../../common/constants";

export default {
  components: {
    PageHeader
  },
  data() {
    return {
      HEALTH_ASSISTANT_STATUS_WAIT_AUDIT: HEALTH_ASSISTANT_STATUS_WAIT_AUDIT,
      HEALTH_ASSISTANT_STATUS_AUDIT_PASS: HEALTH_ASSISTANT_STATUS_AUDIT_PASS,
      HEALTH_ASSISTANT_STATUS_AUDIT_REJECT: HEALTH_ASSISTANT_STATUS_AUDIT_REJECT,
      currentPage: 1,
      lastPage: 0,
      total: 0,
      pageSize: 10,
      loading: false,
      finished: false,
      dataList: [],

      formPopupVisible: false,
      currentIndex: -1,
      formData: {user_id: 0, status: 0, reason: ''}
    }
  },
  methods: {
    onShowFormPopupVisibleTap(index) {
      this.currentIndex = index;
      this.formPopupVisible = true;
    },
    onFormSubmit() {
      if(this.formData.status == 0) {
        this.$toast('请选择审核状态');
        return;
      }
      this.formData.user_id = this.dataList[this.currentIndex].user_id;
      if(this.formData.status == HEALTH_ASSISTANT_STATUS_AUDIT_REJECT && this.formData.reason.length == 0) {
        this.$toast('请输入未通过的原因');
        return;
      }
      this.formPopupVisible = false;
      const that = this;
      that.$http.post(healthAssistantAuditUri, that.formData, {emulateJSON: true}).then(response => {
        let {msg, code, data} = response.body
        if (code != 0) {
          that.$toast.fail(msg)
        } else {
          that.$toast.success('操作成功');
          that.$set(that.dataList, that.currentIndex, data.assistant);
        }
      }, response => {
        that.$toast.fail('发生错误');
      });
    },
    requestData() {
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
    this.requestData();
  }
}
</script>

<style scoped>
  .avatar {
    height: 30px;
    width: 30px;
    border-radius: 15px;
  }
</style>
