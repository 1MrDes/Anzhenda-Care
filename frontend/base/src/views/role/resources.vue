<!--
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-4 18:13
 * Description:
 -->
<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white">
      <el-col :span="24" class="filter-box-left">
        <el-button @click="$router.back()">返回上页</el-button>
      </el-col>
    </el-row>
    <section class="bg-color-white pd10">
      <el-checkbox-group v-model="resourceIds">
        <el-row v-for="(item1, index) in resources" :key="index" class="pd5">
          <el-row class="pl10">
            <el-col :span="24">
              <el-checkbox :label="item1.id" name="resourceIds[]">{{ item1.value }}</el-checkbox>
            </el-col>
          </el-row>
          <el-row v-for="(item2, index) in item1.children" :key="index">
            <el-row class="pl10 pt5 pb5">
              <el-col :span="24">
                <span class="ml10 font15 fw500">
                <el-checkbox :label="item2.id" name="resourceIds[]">{{ item2.value }}</el-checkbox>
                </span>
              </el-col>
            </el-row>

            <el-row>
              <el-col :span="3" v-for="(item3, index) in item2.children" :key="index">
              <span class="ml20">
                <el-checkbox :label="item3.id" name="resourceIds[]">{{ item3.value }}</el-checkbox>
              </span>
              </el-col>
            </el-row>

          </el-row>
        </el-row>
        <el-row class="pd15">
          <el-button type="primary" @click="submitForm" size="small">提交</el-button>
          <el-button @click="$router.back()" size="small">取消</el-button>
        </el-row>
      </el-checkbox-group>
    </section>
  </section>
</template>
<script>
  import {roleInfoUri, resourceListsUri, roleResourcesUri} from '../../common/api';
  export default {
    data() {
      return {
        resources: [],
        resourceIds: []
      }
    },
    methods: {
      fetchData() {
        let self = this;
        self.$http.get(resourceListsUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.resources = data;
          }
        }, response => {
          // error callback
        });
        self.$http.get(roleInfoUri + '?id=' + self.$route.params.id).then(response => {
          let {code, msg, data} = response.body;
          if (code != 0) {
            self.$message.error(msg);
          } else {
            self.resourceIds = data.resource_ids;
          }
        }, response => {
          // error callback
        });
      },
      submitForm() {
        let self = this;
        self.$http.post(roleResourcesUri, {id: self.$route.params.id, resource_ids: self.resourceIds}, {emulateJSON: true}).then(response => {
          let {msg, code, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.$message({
              message: '提交成功',
              type: 'success'
            });
            self.$router.push({name: 'RoleList'});
          }
        }, response => {
          self.$message.error('发生错误')
        })
      }
    },
    mounted: function () {
      this.fetchData();
    }
  }
</script>
