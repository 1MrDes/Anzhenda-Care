<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/14 23:18
 * @description
 -->
<template>
  <div class="bg-color-white pd10">
    <el-tabs v-model="activeTabName" @tab-click="onTabClick">
      <el-tab-pane label="平台" name="platform">
      </el-tab-pane>
      <el-tab-pane label="模板" name="template">
        <el-row class="filter-box pd10 mb10 bg-color-white">
          <el-col :span="24" class="filter-box-left">
            <el-button type="primary" @click="$router.push({name:'addSmsTemplate'})">添加模板</el-button>
          </el-col>
        </el-row>
        <el-table
          :data="templates"
          stripe
          class="bg-color-white w100 mb20">
          <el-table-column
            prop="name"
            label="名称"
            width="160">
          </el-table-column>
          <el-table-column
            prop="code"
            label="编码"
            width="150">
          </el-table-column>
          <el-table-column
            label="状态"
            width="80">
            <template slot-scope="scope">
              {{ scope.row.state == 1 ? '启用' : '停用' }}
            </template>
          </el-table-column>
          <el-table-column
            prop="content"
            label="模板">
          </el-table-column>
          <el-table-column
            prop="description"
            label="备注">
          </el-table-column>
          <el-table-column
            label="操作">
            <template slot-scope="scope">
              <el-button
                size="small"
                @click="$router.push({name: 'editSmsTemplate', params: {id: scope.row.id}})">编辑
              </el-button>
              <el-button
                size="small"
                type="danger"
                @click="del(scope.$index)">删除
              </el-button>
            </template>
          </el-table-column>
        </el-table>
        <div class="pd10" style="text-align: center;">
          <el-pagination
            @current-change="fetchData"
            :current-page.sync="currentPage"
            layout="total, prev, pager, next"
            :total="total"
            :page-size="pageSize">
          </el-pagination>
        </div>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>
<script>
  import {smsTemplateListUri, smsTemplateDeleteUri} from '../../../common/api';
  export default {
    data () {
      return {
        activeTabName: 'template',
        templates: [],
        currentPage: 1,
        total: 1,
        pageSize: 10
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
        self.$http.get(smsTemplateListUri + '?page=' + self.currentPage + '&page_size=' + self.pageSize).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.templates = data.data;
            self.total = data.total;
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
          self.$http.get(smsTemplateDeleteUri + '?id=' + self.templates[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              self.$message.error(msg)
            } else {
              self.$message({
                type: 'success',
                message: '删除成功!'
              });
              self.templates.splice(index, 1);
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
