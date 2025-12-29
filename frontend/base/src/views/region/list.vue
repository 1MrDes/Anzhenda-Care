<!--
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-10 10:00
 * Description:
 -->
<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white" v-if="prevParentId>=0">
      <el-col :span="24" class="filter-box-left">
        <el-button type="primary" @click="fetchData(prevParentId)">返回上级</el-button>
      </el-col>
    </el-row>
    <el-table
      :data="regions"
      stripe
      class="bg-color-white w100 mb20">
      <el-table-column
        prop="region_id"
        label="ID"
        width="80">
      </el-table-column>
      <el-table-column
        prop="first_char"
        label="首字母"
        width="120">
      </el-table-column>
      <el-table-column
        prop="region_name"
        label="地区">
      </el-table-column>
      <el-table-column
        prop="sort_order"
        label="排序"
        width="100">
      </el-table-column>
      <el-table-column
        label="操作">
        <template slot-scope="scope">
          <el-button
            size="small"
            @click="$router.push({name: 'editRegion', params: {regionId: scope.row.region_id}})">编辑
          </el-button>
          <el-button
            size="small"
            type="danger"
            @click="del(scope.$index)">删除
          </el-button>
          <el-button
            size="small"
            @click="$router.push({name: 'addRegion', params: {parentId: scope.row.region_id, parentName: scope.row.region_name}})">添加下级
          </el-button>
          <el-button
            size="small"
            @click="fetchData(scope.row.region_id)">下级地区
          </el-button>
        </template>
      </el-table-column>
    </el-table>
  </section>
</template>
<script>
  import {regionListsUri, regionDeleteUri} from '../../common/api';
  export default {
    data() {
      return {
        parentId : 0,
        prevParentId: -1,
        regions: []
      }
    },
    methods: {
      fetchData(parentId) {
        let self = this;
        self.parentId = parentId;
        self.$http.get(regionListsUri + '?parent_id=' + parentId).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.regions = data.regions;
            if(data.info) {
              self.prevParentId = data.info.parent_id;
            } else {
              self.prevParentId = -1;
            }
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
          self.$http.get(regionDeleteUri + '?region_id=' + self.regions[index].region_id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              self.$message.error(msg)
            } else {
              self.$message({
                type: 'success',
                message: '删除成功!'
              });
              self.regions.splice(index, 1);
            }
          }, response => {
            // error callback
          });
        }).catch(() => {  // 取消按钮回调

        });
      }
    },
    mounted: function () {
      if(typeof this.$route.params != 'undefined' && typeof this.$route.params.parentId != 'undefined') {
        this.fetchData(this.$route.params.parentId);
      } else {
        this.fetchData(0);
      }
    }
  }
</script>
