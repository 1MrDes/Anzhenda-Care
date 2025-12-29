import Vue from 'vue';
import Router from 'vue-router';
import {BASE_PATH} from "../common/api";
Vue.use(Router);

export default new Router({
  mode: 'history',
  base: BASE_PATH,
  routes: [
    {
      path: '/',
      // component: Home,
      name: 'Home',
      redirect: {name:'Main'}
    },
    {
      path: '',
      // component: Main,
      redirect: {name:'Main'}
    },
    {
      path: '/main',
      component: () => import('../views/Main'),
      name: 'Main',
      meta: {
        title: '首页',
        authentication: 'login',
        keepAlive: false //此组件需要被缓存
      }
    },
    {
      path: '/user/login',
      component: () => import('../views/user/Login'),
      name: 'UserLogin',
      meta: {
        title: '登录',
        authentication: 'non',
        keepAlive: false
      }
    },

    {
      path: '/setting/index',
      component: () => import('../views/setting/Index'),
      name: 'SettingIndex',
      meta: {
        title: '设置',
        authentication: 'login',
        keepAlive: false
      }
    },
    {
      path: '/adv/lists',
      component: () => import('../views/adv/Lists'),
      name: 'AdvLists',
      meta: {
        title: '广告',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/adv/add',
      component: () => import('../views/adv/Form'),
      name: 'AdvAdd',
      meta: {
        title: '添加广告',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/adv/edit/:id',
      component: () => import('../views/adv/Form'),
      name: 'AdvEdit',
      meta: {
        title: '编辑广告',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/adv/position',
      component: () => import('../views/adv/Position'),
      name: 'AdvPosition',
      meta: {
        title: '广告位',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/setting/site_config',
      component: () => import('../views/setting/SiteConfig'),
      name: 'SiteConfig',
      meta: {
        title: '系统设置',
        authentication: 'role',
        keepAlive: false
      }
    },

    {
      path: '/article/lists',
      component: () => import('../views/article/Lists'),
      name: 'ArticleLists',
      meta: {
        title: '文章',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/article/add',
      component: () => import('../views/article/Form'),
      name: 'ArticleAdd',
      meta: {
        title: '添加文章',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/article/edit/:id',
      component: () => import('../views/article/Form'),
      name: 'ArticleEdit',
      meta: {
        title: '编辑文章',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/article_category/lists',
      component: () => import('../views/article/category/Lists'),
      name: 'ArticleCategoryLists',
      meta: {
        title: '文章分类',
        authentication: 'role',
        keepAlive: false
      }
    },

    {
      path: '/page/lists',
      component: () => import('../views/page/Lists'),
      name: 'PageLists',
      meta: {
        title: '页面管理',
        authentication: 'role',
        keepAlive: false
      }
    },

    {
      path: '/member/lists',
      component: () => import('../views/member/Lists'),
      name: 'MemberLists',
      meta: {
        title: '用户管理',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/withdraw/lists',
      component: () => import('../views/withdraw/Lists'),
      name: 'WithdrawLists',
      meta: {
        title: '提现管理',
        authentication: 'role',
        keepAlive: false
      }
    },

    {
      path: '/health_service/lists',
      component: () => import('../views/health_service/Lists'),
      name: 'HealthServiceLists',
      meta: {
        title: '服务',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service/add/',
      component: () => import('../views/health_service/Form'),
      name: 'HealthServiceAdd',
      meta: {
        title: '添加服务',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service/edit/:id',
      component: () => import('../views/health_service/Form'),
      name: 'HealthServiceEdit',
      meta: {
        title: '编辑服务',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service/select',
      component: () => import('../views/health_service/Select'),
      name: 'HealthServiceSelect',
      meta: {
        title: '选择服务',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_category/lists',
      component: () => import('../views/health_service/category/Lists'),
      name: 'HealthServiceCategoryLists',
      meta: {
        title: '服务分类',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_category/add',
      component: () => import('../views/health_service/category/Form'),
      name: 'HealthServiceCategoryAdd',
      meta: {
        title: '服务分类',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_category/edit/:id',
      component: () => import('../views/health_service/category/Form'),
      name: 'HealthServiceCategoryEdit',
      meta: {
        title: '服务分类',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_attr/lists/:typeId',
      component: () => import('../views/health_service/attr/Lists'),
      name: 'HealthServiceAttrLists',
      meta: {
        title: '服务属性',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_attr/add/:typeId',
      component: () => import('../views/health_service/attr/Form'),
      name: 'HealthServiceAttrAdd',
      meta: {
        title: '服务属性',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_attr/edit/:typeId/:id',
      component: () => import('../views/health_service/attr/Form'),
      name: 'HealthServiceAttrEdit',
      meta: {
        title: '服务属性',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_spec/lists/:typeId',
      component: () => import('../views/health_service/spec/Lists'),
      name: 'HealthServiceSpecLists',
      meta: {
        title: '服务规格',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_spec/add/:typeId',
      component: () => import('../views/health_service/spec/Form'),
      name: 'HealthServiceSpecAdd',
      meta: {
        title: '服务规格',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_spec/edit/:typeId/:id',
      component: () => import('../views/health_service/spec/Form'),
      name: 'HealthServiceSpecEdit',
      meta: {
        title: '服务规格',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_type/lists',
      component: () => import('../views/health_service/type/Lists'),
      name: 'HealthServiceTypeLists',
      meta: {
        title: '服务类型',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_type/add',
      component: () => import('../views/health_service/type/Form'),
      name: 'HealthServiceTypeAdd',
      meta: {
        title: '服务类型',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_type/edit/:id',
      component: () => import('../views/health_service/type/Form'),
      name: 'HealthServiceTypeEdit',
      meta: {
        title: '服务类型',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_order/lists',
      component: () => import('../views/health_service_order/Lists'),
      name: 'HealthServiceOrderLists',
      meta: {
        title: '订单',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_service_order/detail/:sn',
      component: () => import('../views/health_service_order/Detail'),
      name: 'HealthServiceOrderDetail',
      meta: {
        title: '订单',
        authentication: 'role',
        keepAlive: false
      }
    },
    {
      path: '/health_assistant/lists',
      component: () => import('../views/health_assistant/Lists'),
      name: 'HealthAssistantLists',
      meta: {
        title: '陪诊师',
        authentication: 'role',
        keepAlive: false
      }
    },

    {
      path: '/privacy_policy',
      component: () => import('../views/PrivacyPolicy'),
      name: 'PrivacyPolicy',
      meta: {
        title: '隐私政策',
        authentication: 'non',
        keepAlive: false
      }
    },
    {
      path: '/terms_conditions',
      component: () => import('../views/TermsConditions'),
      name: 'TermsConditions',
      meta: {
        title: '用户协议',
        authentication: 'non',
        keepAlive: false
      }
    },
    {
      path: '/third_info',
      component: () => import('../views/ThirdInfo'),
      name: 'ThirdInfo',
      meta: {
        title: '第三方SDK使用说明文档',
        authentication: 'non',
        keepAlive: false
      }
    },

    {
      path: '/404',
      component: () => import('../components/404.vue'),
      name: 'NotFound',
      meta: {
        title: '找不到页面',
        authentication: 'non',
        keepAlive: false
      }
    },
    {
      path: '/unauthorized',
      component: () => import('../components/Unauthorized'),
      name: 'Unauthorized',
      meta: {
        title: '未授权',
        authentication: 'non',
        keepAlive: false
      }
    },
    {
      path: '*',
      // redirect: {path: '/404'}
      redirect: {name:'Home'}
    }
  ]
})
