<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white">
      <el-col :span="24" class="filter-box-left">
        <el-button type="primary" @click="$router.push({name: 'AdminAdd'})">添加帐号</el-button>
      </el-col>
    </el-row>

    <el-table
      :data="accounts"
      stripe
      style="width: 100%;" class="bg-color-white">
      <el-table-column
        prop="username"
        label="用户名">
      </el-table-column>
      <el-table-column
        prop="real_name"
        label="姓名">
      </el-table-column>
      <el-table-column
        prop="mobile"
        label="手机">
      </el-table-column>
      <el-table-column
        label="添加时间">
        <template slot-scope="scope">
          <span v-if="scope.row.create_time==0">N/A</span>
          <span v-if="scope.row.create_time>0">{{ scope.row.create_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}</span>
        </template>
      </el-table-column>
      <el-table-column
        prop="last_login_ip"
        label="最后登录IP">
      </el-table-column>
      <el-table-column
        label="最后登录时间">
        <template slot-scope="scope">
          <span v-if="scope.row.last_login_time==0">N/A</span>
          <span v-if="scope.row.last_login_time>0">{{ scope.row.last_login_time|timeFormat('yyyy-MM-dd hh:mm:ss') }}</span>
        </template>
      </el-table-column>
      <el-table-column
        label="操作">
        <template slot-scope="scope">
          <el-button
            size="small"
            type="primary"
            @click="$router.push({name: 'AdminEdit', params: {id: scope.row.id}})">编辑
          </el-button>
          <el-button
            size="small"
            type="danger"
            @click="lock(scope.$index)" v-if="scope.row.is_super==0 && scope.row.is_locked==0">锁定
          </el-button>
          <el-button
            size="small"
            type="primary"
            @click="unlock(scope.$index)" v-if="scope.row.is_super==0 && scope.row.is_locked==1">解锁
          </el-button>
        </template>
      </el-table-column>
    </el-table>
    <div class="pd10" style="text-align: center;">
      <el-pagination
        @current-change="requestData"
        :current-page.sync="currentPage"
        layout="total, prev, pager, next"
        :total="total"
        :page-size="pageSize">
      </el-pagination>
    </div>
  </section>
</template>

<script>
  import { adminListsUri, adminLockUri, adminUnlockUri } from '../../common/api';

  export default {
    data () {
      return {
        accounts: [],
        currentPage: 1,
        total: 0,
        pageSize: 10
      }
    },
    methods: {
      lock (index) {
        let self = this;
        let id = this.accounts[index].id;
        this.$http.get(adminLockUri + '?id=' + id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.accounts[index].is_locked = 1;
          }
        }, response => {
          // error callback
        });
      },
      unlock (index) {
        let self = this;
        let id = this.accounts[index].id;
        this.$http.get(adminUnlockUri + '?id=' + id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.accounts[index].is_locked = 0;
          }
        }, response => {
          // error callback
        });
      },
      requestData () {
        let self = this;
        this.$http.get(adminListsUri + '?page=' + self.currentPage + '&pageSize=' + self.pageSize).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.accounts = data.data;
            self.total = data.total;
          }
        }, response => {
          // error callback
        })
      }
    },
    mounted: function () {
      this.requestData()
    }
  }

</script>
