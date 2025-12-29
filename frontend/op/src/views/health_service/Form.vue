<template>
  <section>
    <van-form @submit="onFormSubmit">
      <van-tabs v-model="activeTabName">
        <van-tab title="基本信息" name="basic">
          <div class="flex flex-row flex-center bg-color-white" style="padding: 10px 16px;">
            <div class="flex flex-row flex-left text-center font-big" style="width: 90px;">
              <span class="color-red">*</span>分类
            </div>
            <div class="flex flex-row flex-left flex-1">
              <Multiselect
                v-model="goods.categories"
                placeholder="请选择分类"
                label="name"
                :options="categories"
                :multiple="true"
                :close-on-select="false"
                :clear-on-select="false"
                :preserve-search="true"
                track-by="name">
                <template slot="singleLabel" slot-scope="{ props }">
                  {{ props.option.name }}
                </template>

                <template slot="option" slot-scope="props">
                  <span v-bind:style="{paddingLeft: props.option.level*10 + 'px'}">{{ props.option.name }}</span>
                </template>
              </Multiselect>
            </div>
          </div>

          <van-field
            v-model="goods.name"
            name="名称"
            label="名称"
            placeholder="请输入名称"
            required
            :rules="[{ required: true, message: '请输入名称' }]"
          />

          <van-field
            v-model="goods.short_name"
            name="short_name"
            label="简称"
            placeholder="简称可为空"
          />

          <van-field
            v-model="goods.subheading"
            name="subheading"
            label="副标题"
            placeholder="可为空"
          />

          <van-field
            v-model="goods.market_price"
            type="number"
            name="市场价"
            label="市场价"
            placeholder="请输入市场价"
            :rules="[{ numValidator, message: '请输入正确内容' }]">
            <template #right-icon>
              元
            </template>
          </van-field>

          <van-field
            v-model="goods.sale_price"
            type="number"
            name="销售价"
            label="销售价"
            placeholder="请输入销售价"
            :rules="[{ numValidator, message: '请输入正确内容' }]">
            <template #right-icon>
              元
            </template>
          </van-field>

          <van-field
            v-model="goods.stock"
            type="digit"
            name="库存"
            label="库存"
            placeholder="请输入库存"
            :rules="[{ numValidator, message: '请输入正确内容' }]"
          />

          <van-field
            v-model="goods.virtual_sales"
            type="digit"
            name="虚拟销量"
            label="虚拟销量"
            placeholder="请输入虚拟销量"
            :rules="[{ numValidator, message: '请输入正确内容' }]"
          />

          <van-field name="onSale" label="上架">
            <template #input>
              <van-switch v-model="goods.on_sale" size="20" :active-value="1" :inactive-value="0" />
            </template>
          </van-field>

          <van-field
            v-model="goods.sort_order"
            type="digit"
            name="排序"
            label="排序"
            placeholder="请输入排序"
            :rules="[{ numValidator, message: '请输入正确内容' }]"
          />

          <van-field name="defaultImage" label="默认图片">
            <template #input>
              <van-uploader :after-read="uploadDefaultImage" :before-read="beforeRead">
                <div class="img-uploader-wrapper">
                  <div class="btn-icon">
                    <van-icon name="plus"/>
                  </div>
                  <img :src="goods.default_image_url" v-if="goods.default_image_url" />
                </div>
              </van-uploader>
            </template>
          </van-field>

          <van-field name="regions" label="服务地区">
            <template #input>
              <div style="height: 200px; width: 80%; overflow-y: auto; overflow-x: hidden;" v-if="regionNodes">
                <tree :nodes="regionNodes"
                      :setting="ztreeSetting"
                      @onCheck="onRegionCheck"
                      @onCreated="onZtreeCreated" />
              </div>
            </template>
          </van-field>

          <van-field
            v-model="goods.tags"
            name="tags"
            label="标签"
            placeholder="多个标签请以英文逗号,分隔"
          />

          <van-field name="shipping_type" label="发货方式">
            <template #input>
              <van-radio-group v-model="goods.shipping_type" direction="horizontal">
                <van-radio :name="10">线下服务</van-radio>
                <van-radio :name="20">物流配送</van-radio>
              </van-radio-group>
            </template>
          </van-field>
        </van-tab>

        <van-tab title="属性规格" name="type">
          <div class="flex flex-row flex-center bg-color-white" style="padding: 10px 16px;">
            <div class="flex flex-row flex-left text-center font-big" style="width: 90px;">
              类型
            </div>
            <div class="flex flex-row flex-left flex-1">
              <Multiselect
                v-model="goods.health_service_type"
                placeholder="请选择服务类型"
                label="name"
                :options="goodsTypes"
                @select="onGoodsTypeChange"
                @remove="onGoodsTypeRemove">
                <template slot="singleLabel" slot-scope="{ option }">{{ option.name }}</template>
              </Multiselect>
            </div>
          </div>

          <div class="bg-color-white border-bottom-gray pd10 strong">属性</div>

          <div class="border-bottom-gray" v-for="(o, index) in attrs">
            <van-field
              v-model="o.value"
              :label="o.name + ':'"
              placeholder="请输入"
              v-if="o.input_type==1"
            />

            <van-field :label="o.name + ':'" v-else-if="o.input_type==2">
              <template #input>
                <select v-model="o.value">
                  <option :value="item2" v-for="(item2, index2) in o.values.split('\n')">{{item2}}</option>
                </select>
              </template>
            </van-field>

            <van-field
              v-model="o.value"
              :label="o.name + ':'"
              placeholder="请输入"
              type="textarea"
              autosize
              v-else-if="o.input_type==3"
            />
          </div>

          <div class="bg-color-white border-bottom-gray pd10 strong">规格</div>

          <div class="bg-color-white pt5 pb5 pl10">
            <van-button plain type="info" size="small" @click="addGoodsSpec">+ 添加规格</van-button>
          </div>

          <div class="pd10 bg-color-white">
            <table class="table table-bordered table-grid">
              <thead>
              <tr>
                <th class="relative" v-for="(item, index) in goodsSpecs"
                    :key="index">
                  <input v-model="item.name" class="spec-item-input" placeholder="规格名称" />
                  <van-icon name="cross" class="spec-item-icon-delete" @click="deleteGoodsSpec(index)" />
                </th>
                <th>价格</th>
                <th>库存</th>
                <th>SKU</th>
                <th>图片</th>
                <th>&nbsp;</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(item2, index2) in goodsSpecSkuList" :key="index2">
                <td class="horiz-center" v-for="(item, index) in goodsSpecs"
                    :key="index">
                  <input v-model="item2.values['spec_' + item.uuid]" class="spec-item-input" placeholder="规格值" />
                </td>
                <td class="horiz-center">
                  <input v-model.number="item2.price" class="spec-item-input" />
                </td>
                <td class="horiz-center">
                  <input v-model.number="item2.stock" class="spec-item-input" />
                </td>
                <td class="horiz-center">
                  <input v-model.number="item2.sku" class="spec-item-input" />
                </td>
                <td class="horiz-center">
                  <van-uploader :name="index2" :after-read="uploadSpecItemImage" :before-read="beforeRead">
                    <div class="spec-item-img-uploader-wrapper">
                      <div class="btn-icon">
                        <van-icon name="plus"/>
                      </div>
                      <img :src="item2.spec_file_url" v-if="item2.spec_file_url" />
                    </div>
                  </van-uploader>
                </td>
                <td class="horiz-center">
                  <van-icon name="cross" @click="deleteGoodsSpecItem(index2)" v-if="index2 > 0" />
                </td>
              </tr>
              </tbody>
            </table>
          </div>

          <div class="bg-color-white pt5 pb5 pl10">
            <van-button plain type="info" size="small" @click="addGoodsSpecItem">+ 添加一行</van-button>
          </div>
        </van-tab>

        <van-tab title="详情" name="description">
          <Ueditor :defaultMsg="goods.description" :config="ueConfig" id="ue" ref="ue" />
        </van-tab>

        <van-tab title="图片" name="images">
          <div class="bg-color-white pd10">
            <van-uploader v-model="imagePreviewList" multiple :after-read="uploadImage" :before-read="beforeRead" @delete="onImageDeleted" />
          </div>
        </van-tab>
      </van-tabs>

      <div style="margin: 16px;">
        <van-button round block type="info" native-type="submit">
          保存
        </van-button>
      </div>
    </van-form>
  </section>
</template>

<script>
  import tree from "vue-giant-tree";
  import Multiselect from "vue-multiselect";
  import 'vue-multiselect/dist/vue-multiselect.min.css';
  import Ueditor from "../../components/Ueditor";
  import util from '../../assets/js/util';
  import {
    BASE_PATH,
    fileUploadUri,
    fileUploadByBase64Uri,
    ueFileUploadUri,
    goodsSaveUri,
    goodsInfoUri,
    goodsTypeListsUri,
    goodsAttrAllUri,
    regionAllUri, goodsCategoryTreeUri
  } from "../../common/api";

  let ztreeObj = null;

  export default {
    components: {Ueditor, tree, Multiselect},
    data() {
      const ueServerUrl = ueFileUploadUri + '?upload_token=' + this.GLOBAL.userInfo.upload_token;
      return {
        ueConfig: {
          UEDITOR_HOME_URL: BASE_PATH + 'static/js/ueditor/',
          serverUrl: ueServerUrl,
          initialFrameWidth: null,
          initialFrameHeight: 400
        },
        uploadToken:this.GLOBAL.userInfo.upload_token,
        fileUploadUri: fileUploadUri,
        activeTabName: 'basic',
        categories: [],
        goodsTypes: [],
        goodsAttrs: [],
        attrs: [],
        goodsSpecs: [],
        goodsSpecSkuList: [
          {
            id: 0,
            key: '',
            price: 0.00,
            stock: 100,
            sku: '',
            spec_file_id: 0,
            spec_file_url: '',
            uid: '',
            values: {}
          }
        ],
        goods: {id: 0, category: '', categories: [], tag_ids: '', tags: '', type_id: 0, health_service_type: {id: 0, name: ''},
          name: '', subheading: '', short_name: '', market_price: 0.00, sale_price: 0.00, stock: 10000,
          virtual_sales: 0, on_sale: 1, sort_order: 1, description: '', default_image: 0, default_image_url: '',
          sale_regions: '', shipping_type: 10},
        imageList: [],
        imagePreviewList: [],
        goodsRegions: [],
        ztreeSetting: {
          check: {
            enable: true,
            chkboxType: { "Y" : "ps", "N" : "ps" }
          },
          data: {
            simpleData: {
              enable: true,
              pIdKey: "pid"
            }
          },
          view: {
            showIcon: false
          }
        },
        regionNodes: null
      }
    },
    watch: {
      'goods.categories': {
        handler(newName, oldName) {
          // console.log(newName);
        },
        deep: true,
        immediate: true
      },
    },
    methods: {
      numValidator(val) {
        if(typeof val != 'number' || val < 0) {
          return false;
        }
        return true;
      },
      getCategoryList() {
        const that = this;
        this.$http.get(goodsCategoryTreeUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            let categories = data.categories;
            // for (let i = 0; i < categories.length; i++) {
            //   let prefix = '';
            //   for (let j = 0; j < categories[i].level; j++) {
            //     prefix += '|+';
            //   }
            //   categories[i]['prefix'] = prefix;
            // }
            that.categories = categories;
          }
        }, response => {
          // error callback
        })
      },
      onGoodsTypeChange(value) {
        this.goods.type_id = value.id;
        this.goods.health_service_type = value;
        if(value.id > 0) {
          this.getGoodsAttrs();
        }
      },
      onGoodsTypeRemove(value) {
        if(value.id == this.goods.type_id) {
          this.goods.type_id = 0;
          this.goods.health_service_type = {id: 0, name: ''};
          this.attrs = [];
        }
      },
      getGoodsTypes() {
        const that = this;
        this.$http.get(goodsTypeListsUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            that.goodsTypes = data.types;
          }
        }, response => {
          // error callback
        })
      },
      getGoodsAttrs() {
        const that = this;
        this.$http.get(goodsAttrAllUri + '?type_id=' + this.goods.type_id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            for (let i = 0; i < data.attrs.length; i++) {
              data.attrs[i]['value'] = '';
              if(that.$route.name == 'HealthServiceEdit') {
                for (let j = 0; j < that.goodsAttrs.length; j++) {
                  if(data.attrs[i].id == that.goodsAttrs[j].attr_id) {
                    data.attrs[i]['value'] = that.goodsAttrs[j].attr_value;
                    continue;
                  }
                }
              }
            }
            that.attrs = data.attrs;
          }
        }, response => {
          // error callback
        })
      },
      uploadDefaultImage(file, detail) {
        const that = this;
        let imgData = file.content;
        imgData = imgData.indexOf('data:image') == -1 ? 'data:image/jpg;base64,' + imgData : imgData;
        that.uploadFile(imgData, true);
      },
      beforeRead(file) {
        if (file.type !== 'image/jpeg' && file.type !== 'image/jpg' && file.type !== 'image/png') {
          this.$toast('请上传 jpg/png 格式图片');
          return false;
        }
        return true;
      },
      uploadImage(file, detail) {
        const that = this;
        let imgData = file.content;
        imgData = imgData.indexOf('data:image') == -1 ? 'data:image/jpg;base64,' + imgData : imgData;
        that.uploadFile(imgData, false);
      },
      onImageDeleted(file, detail) {
        this.imageList.splice(detail.index, 1);
      },
      uploadFile(imgData, isDefaultImage) {
        const that = this;
        const params = {fileData: imgData};
        that.$http.post(fileUploadByBase64Uri + '?upload_token=' + this.GLOBAL.userInfo.upload_token, params, {emulateJSON: true}).then(response => {
          let {msg, code, file_id, url} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            if(isDefaultImage) {
              that.goods.default_image = file_id;
              that.goods.default_image_url = url;
            } else {
              that.imageList.push({id: 0, image_id: file_id, image_url: url});
            }
          }
        }, response => {
          that.$toast.fail('发生错误');
        })
      },
      onFormSubmit(values) {
        if(this.submiting) {
          return;
        }
        if(this.goods.categories.length == 0) {
          this.$toast.fail('请选择分类');
          return;
        }
        if(this.goodsSpecs.length > 0) {
          for (let i = 0; i < this.goodsSpecs.length; i++) {
            if(this.goodsSpecs[i].name.length == 0) {
              this.$toast.fail('请输入规格名称');
              return;
            }
          }

          if(this.goodsSpecSkuList.length > 0) {
            for (let i = 0; i < this.goodsSpecSkuList.length; i++) {
              if(this.goodsSpecSkuList[i].price.length == 0 || this.goodsSpecSkuList[i].stock.length == 0) {
                this.$toast.fail('请输入规格价格和库存');
                return;
              }

              for(let key in this.goodsSpecSkuList[i].values) {
                if(this.goodsSpecSkuList[i].values[key].length == 0) {
                  this.$toast.fail('请输入规格值');
                  return;
                }
              }
            }
          }
        }
        let goodsSpecSkuList = [];
        if(this.goodsSpecs.length > 0) {
          // for (let i = 0; i < this.goodsSpecSkuList.length; i++) {
          //   specItemPrices[i] = this.goodsSpecSkuList[i];
          // }
          goodsSpecSkuList = this.goodsSpecSkuList;
        }

        let goodsAttrs = [];
        for (let i = 0; i < this.attrs.length; i++) {
          goodsAttrs.push({
            attr_id: this.attrs[i].id,
            attr_value: this.attrs[i].value
          });
        }
        if(typeof this.$refs.ue != "undefined") {
          this.goods.description = this.$refs.ue.getUEContent();
        }
        this.goods.sale_regions = this.goodsRegions.join(',');
        this.submiting = true;
        const that = this;

        const postData = {
          goods:this.goods,
          goods_images: this.imageList,
          goods_specs: this.goodsSpecs,
          goods_spec_sku_list: goodsSpecSkuList,
          goods_attrs: goodsAttrs,
        };
        that.$http.post(goodsSaveUri, postData, {emulateJSON: true}).then(response => {
          let {msg, code, data} = response.body;
          that.submiting = false;
          if (code != 0) {
            that.$toast.fail(msg)
          } else {
            that.$toast.success('保存成功');
            that.$router.back();
          }
        }, response => {
          that.submiting = false;
          that.$toast.fail('发生错误')
        })
      },
      info() {
        const that = this;
        this.$http.get(goodsInfoUri + '?id=' + this.goods.id).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            that.goods = data.goods;
            if(data.goods_images) {
              that.imageList = data.goods_images;
              for (let key in data.goods_images) {
                that.imagePreviewList.push({url: data.goods_images[key].image_url});
              }
            }

            that.goodsSpecs = data.goods_specs;
            if(!data.goods_spec_sku_list || data.goods_spec_sku_list.length == 0) {
              that.goodsSpecSkuList = [
                {
                  id: 0,
                  key: '',
                  price: 0.00,
                  stock: 100,
                  sku: '',
                  spec_file_id: 0,
                  spec_file_url: '',
                  uid: '',
                  values: {}
                }
              ];
            } else {
              that.goodsSpecSkuList = data.goods_spec_sku_list;
            }
            that.goodsAttrs = data.goods_attrs;
            that.goodsRegions = data.goods.sale_regions ? data.goods.sale_regions.split(',') : [];
            if(data.goods.type_id > 0) {
              that.getGoodsAttrs();
            }
            that.getRegions();
          }
        }, response => {
          // error callback
        })
      },
      addGoodsSpec() {
        this.goodsSpecs.push({
          uuid: util.generateUUID(),
          id: 0,
          name: ''
        });
        let values = {};
        for (let i = 0; i < this.goodsSpecs.length; i++) {
          values['spec_' + this.goodsSpecs[i].uuid] = '';
        }
        for (let i = 0; i < this.goodsSpecSkuList.length; i++) {
          let goodsSpecSku = this.goodsSpecSkuList[i];
          goodsSpecSku.values = values;
          this.$set(this.goodsSpecSkuList, i, goodsSpecSku);
        }
      },
      addGoodsSpecItem() {
        let goodsSpecItem = {
          id: 0,
          key: '',
          price: 0.00,
          stock: 100,
          sku: '',
          spec_file_id: 0,
          spec_file_url: '',
          uid: '',
          values: {}
        };
        let values = {};
        for (let i = 0; i < this.goodsSpecs.length; i++) {
          values['spec_' + this.goodsSpecs[i].uuid] = '';
        }
        goodsSpecItem.values = values;
        this.goodsSpecSkuList.push(goodsSpecItem);
      },
      deleteGoodsSpec(index) {
        let goodsSpec = this.goodsSpecs[index];
        this.goodsSpecs.splice(index, 1);
        for (let i = 0; i < this.goodsSpecSkuList.length; i++) {
          let goodsSpecSku = this.goodsSpecSkuList[i];
          delete  goodsSpecSku.values['spec_' + goodsSpec.uuid];
          this.$set(this.goodsSpecSkuList, i, goodsSpecSku);
        }
      },
      deleteGoodsSpecItem(index) {
        this.goodsSpecSkuList.splice(index, 1);
      },
      uploadSpecItemImage(file, detail) {
        const that = this;
        let imgData = file.content;
        imgData = imgData.indexOf('data:image') == -1 ? 'data:image/jpg;base64,' + imgData : imgData;
        that.uploadSpecItemImageFile(imgData, detail.name);
      },
      uploadSpecItemImageFile(imgData, specItemPriceIndex) {
        const that = this;
        const params = {fileData: imgData};
        that.$http.post(fileUploadByBase64Uri + '?upload_token=' + this.GLOBAL.userInfo.upload_token, params, {emulateJSON: true}).then(response => {
          let {msg, code, file_id, url} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            let specSku = that.goodsSpecSkuList[specItemPriceIndex];
            specSku.spec_file_id = file_id;
            specSku.spec_file_url = url;
            that.$set(that.goodsSpecSkuList, specItemPriceIndex, specSku);
          }
        }, response => {
          that.$toast.fail('发生错误');
        })
      },
      getRegions() {
        const that = this;
        this.$http.get(regionAllUri).then(response => {
          let {code, msg, data} = response.body
          if (code != 0) {
            that.$toast.fail(msg);
          } else {
            const saleRegions = that.goods.sale_regions;
            let regionNodes = [];
            for (let key in data.regions) {
              if(data.regions[key].parent_id == 0 || data.regions[key].region_type == 3) {
                continue;
              }
              let checked = false;
              if(saleRegions.length > 0
                && (',' + saleRegions + ',').indexOf(',' + data.regions[key].region_id + ',') > -1) {
                checked = true;
              }
              regionNodes.push({
                id: data.regions[key].region_id,
                pid: data.regions[key].parent_id,
                name: data.regions[key].region_name,
                open: false,
                checked: checked
              });
            }
            that.regionNodes = regionNodes;
          }
        }, response => {
          // error callback
        })
      },
      onZtreeCreated(obj) {
        ztreeObj = obj;
      },
      onRegionCheck: function(evt, treeId, treeNode) {
        const checkedNodes = ztreeObj.getCheckedNodes(true);
        // console.log(checkedNodes);
        let goodsRegions = [];
        if(checkedNodes) {
          for (let i = 0; i < checkedNodes.length; i++) {
            goodsRegions.push(checkedNodes[i].id);
          }
        }
        if(!treeNode.checked) {
          const parentNode = treeNode.getParentNode();
          for (let i = 0; i < goodsRegions.length; i++) {
            if(goodsRegions[i] == treeNode.pid) {
              // delete goodsRegions[i];
              goodsRegions.splice(i, 1);
            } else if(parentNode && goodsRegions[i] == parentNode.pid) {
              goodsRegions.splice(i, 1);
            }
          }
        } else {
          let parentNode = treeNode.getParentNode();
          if(parentNode) {
            let isCheckedAll = true;
            let children = parentNode.children;
            for (let i = 0; i < children.length; i++) {
              if(!children[i].checked) {
                isCheckedAll = false;
                break;
              }
            }
            if(!isCheckedAll) {
              for (let i = 0; i < goodsRegions.length; i++) {
                if(goodsRegions[i] == parentNode.id) {
                  goodsRegions.splice(i, 1);
                  break;
                }
              }
            }

            parentNode = parentNode.getParentNode();
            if(parentNode) {
              let isCheckedAll = true;
              let children = parentNode.children;
              for (let i = 0; i < children.length; i++) {
                if(!children[i].checked) {
                  isCheckedAll = false;
                  break;
                }
              }
              if(!isCheckedAll) {
                for (let i = 0; i < goodsRegions.length; i++) {
                  if(goodsRegions[i] == parentNode.id) {
                    goodsRegions.splice(i, 1);
                    break;
                  }
                }
              }
            }
          }
        }
        // goodsRegions = util.arrayUniq(goodsRegions);
        this.goodsRegions = goodsRegions;
        // console.log(goodsRegions);
      }
    },
    mounted: function () {
      this.getCategoryList();
      this.getGoodsTypes();
      if(this.$route.name == 'HealthServiceEdit') {
        this.goods.id = this.$route.params.id;
        this.info();
      } else {
        this.getRegions();
      }
    }
  }
</script>

<style scoped>
  .spec-item-icon-delete {
    position:absolute;
    right: 0;
    top: 0;
    z-index: 1;
  }
  .spec-item-input {
    height: 20px;
    width: 60px;
    padding: 5px 5px;
  }

  .spec-item-img-uploader-wrapper {
    border: 1px dashed #d6d6d6;
    display: inline-block;
    height: 60px;
    width: 60px;
    position: relative;
    border-radius: 6px;
    background: #ffffff;
  }
  .spec-item-img-uploader-wrapper img {
    height: 60px;
    width: 60px;
    border-radius: 6px;
  }
  .spec-item-img-uploader-wrapper .btn-icon {
    display: inline-block;
    height: 20px;
    width: 20px;
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    top: 0;
    margin: auto;
    z-index: 1;
  }
</style>
