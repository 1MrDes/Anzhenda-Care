<!--
 *
 * @author 凡墙<jihaoju@qq.com>
 * @date 2017/8/13 12:11
 * @description
 -->
<template>
  <section>
    <el-row class="filter-box pd10 mb10 bg-color-white">
      <el-col :span="24" class="filter-box-left">
        <el-button type="primary" @click="$router.push({name: 'regionsList', params:{parentId: region.parent_id}})">返回上页</el-button>
      </el-col>
    </el-row>
    <div class="bg-color-white pd10">
      <el-form ref="form-region" :model="region" :rules="rules" label-width="80px">
        <el-form-item label="上级">
          {{ parentName }}
        </el-form-item>
        <el-form-item prop="region_name" label="名称">
          <el-input placeholder="请输入名称" v-model="region.region_name" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="first_char" label="首字母">
          <el-input placeholder="请输入首字母" v-model="region.first_char" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="sort_order" label="排序">
          <el-input v-model.number="region.sort_order" size="small"></el-input>
        </el-form-item>
        <el-form-item prop="area" label="区域">
          <el-input v-model="region.area" size="small"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="submitForm('form-region')">提交</el-button>
          <el-button @click="$router.push({name: 'regionsList', params:{parentId: region.parent_id}})">取消</el-button>
        </el-form-item>
      </el-form>
    </div>
  </section>
</template>
<script>
  import {regionSubmitUri, regionInfoUri} from '../../common/api';
  export default {
    data () {
      return {
        parentName : '',
        region: {
          region_id : 0,
          parent_id : 0,
          region_name : '',
          first_char : '',
          sort_order : '',
          area : ''
        },
        rules: {
          region_name: [
            {required: true, message: '请填写名称', trigger: 'blur'}
          ],
          first_char: [
            {required: true, message: '请填写首字母', trigger: 'blur'}
          ],
          sort_order: [
            {type:'number', required: true, message: '请填写排序', trigger: 'blur'}
          ]
        }
      }
    },
    methods: {
      fetchData () {
        let self = this;
        self.$http.get(regionInfoUri + '?region_id=' + self.$route.params.regionId).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            self.$message.error(msg)
          } else {
            self.region = data;
            if(data.parent) {
              self.parentName = data.parent.region_name;
            }
          }
        }, response => {
          // error callback
        })
      },
      submitForm(name) {
        const self = this;
        self.$refs[name].validate((valid) => {
          if (valid) {
            self.$http.post(regionSubmitUri, self.region, {emulateJSON: true}).then(response => {
              let {msg, code, data} = response.body
              if (code != 0) {
                self.$message.error(msg)
              } else {
                self.$message({
                  message: '提交成功',
                  type: 'success'
                });
                self.$router.push({name: 'regionsList', params: {parentId: self.region.parent_id}});
              }
            }, response => {
              self.$message.error('发生错误')
            })
          } else {
            self.$message.error('请按提示输入')
          }
        })
      }
    },
    mounted: function () {
      if (this.$route.name == 'editRegion') {
        this.fetchData();
      } else {
        this.region.parent_id = this.$route.params.parentId;
        this.parentName = this.$route.params.parentName;
      }
    }
  }
</script>
