<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/14 23:16
 * @description
 -->
<template>
  <div class="bg-color-white pd10">
    <el-tabs v-model="activeTabName" @tab-click="onTabClick">
      <el-tab-pane label="平台" name="platform">
        <el-row class="filter-box pd10 mb10 bg-color-white">
          <el-col :span="24" class="filter-box-left">
            <el-button type="primary" @click="$router.push({name:'addSmsPlatform'})">添加平台</el-button>
          </el-col>
        </el-row>
        <el-table
          :data="platforms"
          stripe
          class="bg-color-white w100 mb20">
          <el-table-column
            prop="sms_name"
            label="名称">
          </el-table-column>
          <el-table-column
            prop="sms_code"
            label="编码">
          </el-table-column>
          <el-table-column
            label="开启"
            width="80">
            <template slot-scope="scope">
              {{ scope.row.enable == 1 ? '是' : '否' }}
            </template>
          </el-table-column>
          <el-table-column
            prop="weight"
            label="权重"
            width="80">
          </el-table-column>
          <el-table-column
            prop="sms_desc"
            label="备注">
          </el-table-column>
          <el-table-column
            label="操作">
            <template slot-scope="scope">
              <el-button
                size="small"
                @click="$router.push({name: 'editSmsPlatform', params: {id: scope.row.id}})">编辑
              </el-button>
              <el-button
                size="small"
                type="danger"
                @click="del(scope.$index)">删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-tab-pane>
      <el-tab-pane label="模板" name="template">

      </el-tab-pane>
    </el-tabs>
  </div>
</template>
<script>
  import {smsPlatformListUri, smsPlatformDeleteUri} from '../../../common/api';
  export default {
    data () {
      return {
        activeTabName: 'platform',
        platforms: []
      }
    },
    methods: {
      onTabClick(tab, event) {
        if(this.activeTabName == 'platform') {
          this.$router.push({name: 'smsPlatformList'});
        } else if(this.activeTabName == 'template') {
          this.$router.push({name: 'smsTemplateList'});
        }
      },
      fetchData() {
        let self = this;
        self.$http.get(smsPlatformListUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.platforms = data;
          }
        }, response => {
          // error callback
        })
      },
      del(index) {
        let self = this;
        self.$confirm('确定删除吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => { // 确定按钮回调
          self.$http.get(smsPlatformDeleteUri + '?id=' + self.platforms[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              self.$message.error(msg)
            } else {
              self.$message({
                type: 'success',
                message: '删除成功!'
              });
              self.platforms.splice(index, 1);
            }
          }, response => {
            // error callback
          });
        }).catch(() => {  // 取消按钮回调

        });
      }
    },
    mounted: function () {
      this.fetchData();
    }
  }
</script>
