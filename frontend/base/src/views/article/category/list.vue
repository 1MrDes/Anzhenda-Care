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

        </el-tab-pane>

        <el-tab-pane label="分类" name="category">
          <el-row class="filter-box pd10 mb10 bg-color-white">
            <el-col :span="24" class="filter-box-left">
              <el-button type="primary" @click="$router.push({name: 'addArticleCategory'})">添加分类</el-button>
            </el-col>
          </el-row>

          <el-table
            :data="categories"
            stripe
            style="width: 100%;" class="bg-color-white">
            <el-table-column
              prop="id"
              label="ID">
            </el-table-column>
            <el-table-column
              prop="name"
              label="名称">
            </el-table-column>
            <el-table-column
              prop="code"
              label="编码">
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
                  @click="$router.push({name: 'editArticleCategory', params: {id: scope.row.id}})">编辑
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
      </el-tabs>
    </div>
  </section>
</template>
<script>
  import {articleCategoryListUri, articleCategoryDeleteUri} from '../../../common/api';
  export default {
    data () {
      return {
        activeTabName: 'category',
        categories: []
      }
    },
    methods: {
      onTabClick(tab, event) {
        if(this.activeTabName == 'article') {
          this.$router.push({name: 'articleList'});
        }
      },
      del(index) {
        let self = this;
        this.$confirm('确定删除吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => { // 确定按钮回调
          self.$http.get(articleCategoryDeleteUri + '?id=' + self.categories[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              self.$message.error(msg)
            } else {
              self.$message({
                message: '删除成功',
                type: 'success'
              });
              self.categories.splice(index, 1);
            }
          }, response => {
            // error callback
          })
        }).catch(() => {  // 取消按钮回调

        });
      },
      requestData () {
        let self = this;
        this.$http.get(articleCategoryListUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.categories = data;
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
