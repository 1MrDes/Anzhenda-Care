<!--
 * Author: 凡墙<jihaoju@qq.com>
 * Time: 2017-8-4 18:13
 * Description:
 -->
<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white">
      <el-col :span="24" class="filter-box-left">
        <el-button type="primary" @click="$router.push({name: 'ResourceAdd'})">添加资源</el-button>
      </el-col>
    </el-row>
    <section class="bg-color-white pd10">
      <el-row v-for="(item1, index) in resources" :key="index" class="pd5">
        <el-row class="pl10">
          <el-col :span="24">
            {{ item1.value }}
            <i class="icon iconfont icon-bianji ml5 button"
               @click="$router.push({name:'ResourceEdit', params:{id:item1.id}})" />
            <i class="icon iconfont icon-shanchu ml5 button" @click="del(item1.id)" />
          </el-col>
        </el-row>
        <el-row v-for="(item2, index) in item1.children" :key="index">
          <el-row class="pl10">
            <el-col :span="24">
              <span class="ml10">{{ item2.value }}
                <i class="icon iconfont icon-bianji ml5 button"
                   @click="$router.push({name:'ResourceEdit', params:{id:item2.id}})" />
                <i class="icon iconfont icon-shanchu ml5 button" @click="del(item2.id)" />
              </span>
            </el-col>
          </el-row>
          <el-row>
            <el-col :span="3" v-for="(item3, index) in item2.children" :key="index">
              <span class="ml20">{{ item3.value }}
                    <i class="icon iconfont icon-bianji-copy ml5 button"
                       @click="$router.push({name:'ResourceEdit', params:{id:item3.id}})" />
                    <i class="icon iconfont icon-shanchu ml5 button" @click="del(item3.id)" />
              </span>
            </el-col>
          </el-row>
        </el-row>
      </el-row>
    </section>
  </section>
</template>
<script>
  import {resourceListsUri, resourceDeleteUri} from '../../common/api';

  export default {
    data() {
      return {
        resources: []
      }
    },
    methods: {
      fetchData() {
        let self = this
        self.$http.get(resourceListsUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.resources = data
          }
        }, response => {
          // error callback
        })
      },
      del(id) {
        let self = this;
        this.$confirm('确定删除吗?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => { // 确定按钮回调
          self.$http.get(resourceDeleteUri + '?id=' + id).then(response => {
            let {code, msg, data} = response.body
            if (code != 0) {
              self.$message.error(msg)
            } else {
              self.$message({
                message: '删除成功',
                type: 'success'
              });
              self.fetchData();
            }
          }, response => {
            // error callback
          })
        }).catch(() => {  // 取消按钮回调

        });
      }
    },
    mounted: function () {
      this.fetchData()
    }
  }
</script>
