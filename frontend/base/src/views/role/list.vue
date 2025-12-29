<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white">
      <el-col :span="24" class="filter-box-left">
        <el-button type="primary" @click="$router.push({name: 'RoleAdd'})">添加角色</el-button>
      </el-col>
    </el-row>

    <el-table
      :data="roles"
      stripe
      class="bg-color-white w100">
      <el-table-column
        prop="name"
        label="角色">
      </el-table-column>
      <el-table-column
        prop="remark"
        label="备注">
      </el-table-column>
      <el-table-column
        label="操作">
        <template slot-scope="scope">
          <el-button
            size="small"
            @click="$router.push({name: 'RoleEdit', params: {id: scope.row.id}})">编辑
          </el-button>
          <el-button
            size="small"
            type="danger"
            @click="del(scope.$index)">删除
          </el-button>
          <el-button
            size="small"
            @click="$router.push({name: 'RoleResources', params: {id: scope.row.id}})">权限设置
          </el-button>
        </template>
      </el-table-column>
    </el-table>
  </section>
</template>

<script>
  import { roleAllUri, roleDeleteUri } from '../../common/api';
  export default {
    data() {
      return {
        roles: []
      }
    },
    methods: {
      del(index) {
        this.$confirm('确定删除?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => { //  确定按钮回调
          let self = this;
          self.$http.get(roleDeleteUri + '?id=' + self.roles[index].id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              self.$message.error(msg)
            } else {
              self.$message({
                type: 'success',
                message: '删除成功!'
              });
              self.roles.splice(index, 1);
            }
          }, response => {
            // error callback
          });
        }).catch(() => {  // 取消按钮回调

        });
      },
      fetchData() {
        let self = this;
        self.$http.get(roleAllUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.roles = data
          }
        }, response => {
          // error callback
        })
      }
    },
    mounted: function () {
      this.fetchData();
    }
  }

</script>
