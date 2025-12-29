<template>
  <div><script :id=id type="text/plain"></script></div>
</template>
<script>
  import '../../static/js/ueditor/ueditor.config.js'
  import '../../static/js/ueditor/ueditor.all.min.js'
  import '../../static/js/ueditor/lang/zh-cn/zh-cn.js'
  import '../../static/js/ueditor/ueditor.parse.min.js'
  // import '../../static/js/ueditor/third-party/xiumi/xiumi-ue-dialog-v5.js'
  import '../../static/js/ueditor/third-party/xiumi/xiumi-ue-v5.css'
  export default {
    name: 'UE',
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
      xiumiEnable: {
        type: Boolean,
        default: false
      }
    },
    mounted () {
      const _this = this;
      this.config = this.config || {};
      // this.config.toolbars = [[
      //   'fullscreen', 'source', 'undo', 'redo', 'bold', '|',
      //   'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
      //   'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
      //   'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
      // ]];

      this.editor = UE.getEditor(this.id, this.config); // 初始化UE
      this.editor.addListener('ready', function () {
        _this.editor.setContent(_this.defaultMsg) // 确保UE加载完成后，放入内容。
      });

      if(_this.xiumiEnable) {
        UE.registerUI('dialog', function (editor, uiName) {
          var btn = new UE.ui.Button({
            name   : 'xiumi-connect',
            title  : '秀米',
            onclick: function () {
              var dialog = new UE.ui.Dialog({
                iframeUrl: './static/js/ueditor/third-party/xiumi/xiumi-ue-dialog-v5.html',
                editor   : editor,
                name     : 'xiumi-connect',
                title    : "秀米图文消息助手",
                cssRules : "width: " + (window.innerWidth - 60) + "px;" + "height: " + (window.innerHeight - 60) + "px;",
              });
              dialog.render();
              dialog.open();
            }
          });
          return btn;
        });
      }
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
