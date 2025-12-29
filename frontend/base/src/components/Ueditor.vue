<template>
  <div><script :id=id type="text/plain"></script></div>
</template>
<script>
  import '../../static/js/ueditor/ueditor.config.js'
  import '../../static/js/ueditor/ueditor.all.min.js'
  import '../../static/js/ueditor/lang/zh-cn/zh-cn.js'
  import '../../static/js/ueditor/ueditor.parse.min.js'
  export default {
    name: 'Ueditor',
    data () {
      return {
        editor: null
      }
    },
    props: {
      defaultMsg: {
        type: String
      },
      config: {
        type: Object
      },
      id: {
        type: String
      },
    },
    mounted () {
      const _this = this
      this.editor = UE.getEditor(this.id, this.config) // 初始化UE
      this.editor.addListener('ready', function () {
        _this.editor.setContent(_this.defaultMsg) // 确保UE加载完成后，放入内容。
      })
    },
    methods: {
      getUEContent () { // 获取内容方法
        return this.editor.getContent()
      },
      setUEContent (content) {
        return this.editor.setContent(content);
      }
    },
    destroyed () {
      this.editor.destroy()
    }
  }
</script>
