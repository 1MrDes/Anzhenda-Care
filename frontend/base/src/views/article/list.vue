<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/11/14 22:58
 * @description
 -->
<template>
  <section>
    <div class="bg-color-white pd10">
      <el-tabs v-model="activeTabName" @tab-click="onTabClick">
        <el-tab-pane label="文章" name="article">
          <el-row class="filter-box pd10 mb10 bg-color-white">
            <el-col :span="24" class="filter-box-left">
              <el-button type="primary" @click="$router.push({name: 'addArticle'})">添加文章</el-button>
            </el-col>
          </el-row>

          <el-table
            :data="articles"
            stripe
            style="width: 100%;" class="bg-color-white">
            <el-table-column
              prop="id"
              label="ID">
            </el-table-column>
            <el-table-column
              prop="title"
              label="标题">
            </el-table-column>
            <el-table-column
              prop="code"
              label="编码">
            </el-table-column>
            <el-table-column
              label="显示">
              <template slot-scope="scope">
                <span v-if="scope.row.is_show==1">是</span>
                <span v-if="scope.row.is_show==0">否</span>
              </template>
            </el-table-column>
            <el-table-column
              prop="update_time"
              label="最后更新">
            </el-table-column>
            <el-table-column
              prop="sort_order"
              label="排序">
            </el-table-column>
            <el-table-column
              label="操作"
              :min-width="100">
              <template slot-scope="scope">
                <el-button
                  size="small"
                  type="primary"
                  @click="$router.push({name: 'editArticle', params: {id: scope.row.id}})">编辑
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
              @current-change="requestData"
              :current-page.sync="currentPage"
              layout="total, prev, pager, next"
              :total="total"
              :page-size="pageSize">
            </el-pagination>
          </div>
        </el-tab-pane>

        <el-tab-pane label="分类" name="category">

        </el-tab-pane>
      </el-tabs>
    </div>
  </section>
</template>
<script>
  import {articleListUri, articleDeleteUri} from '../../common/api';
  export default {
    data () {
      return {
        activeTabName: 'article',
        currentPage: 1,
        total: 0,
        pageSize: 10,
        articles: []
      }
    },
    methods: {
      onTabClick(tab, event) {
        if(this.activeTabName == 'category') {
          this.$router.push({name: 'articleCategoryList'});
        }
      },
      del(index) {
        let self = this;
        this.$confirm('确定删除吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => { // 确定按钮回调
          self.$http.get(articleDeleteUri + '?id=' + self.articles[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              self.$message.error(msg)
            } else {
              self.$message({
                message: '删除成功',
                type: 'success'
              });
              self.articles.splice(index, 1);
            }
          }, response => {
            // error callback
          })
        }).catch(() => {  // 取消按钮回调

        });
      },
      requestData () {
        let self = this;
        this.$http.get(articleListUri + '?page=' + self.currentPage + '&pageSize=' + self.pageSize).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.articles = data.data;
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
