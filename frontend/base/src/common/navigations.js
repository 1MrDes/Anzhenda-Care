//
// @author 凡墙<jihaoju@qq.com>
// @date 2017/8/8 23:50
// @description
export default [
  {
    name: '首页',
    icon: '&#xe625;',
    url: '/main',
    enable: true,
    subMenus: null
  },
  {
    name: '运营',
    icon: '&#xe625;',
    url: null,
    enable: true,
    subMenus: [
      {name: '文章管理', url: '/article/lists', enable: true},
    ]
  },
  {
    name: '设置',
    icon: '&#xe625;',
    url: null,
    enable: true,
    subMenus: [
      {name: '系统设置', url: '/setting/basic', enable: true},
      {name: '地区', url: '/region/lists', enable: true},
      {name: '短信', url: '/sms_platform/lists', enable: true},
      {name: '账号', url: '/admin/lists', enable: true},
      {name: '角色', url: '/role/lists', enable: true},
      {name: '资源', url: '/resource/lists', enable: true}
    ]
  }
]
