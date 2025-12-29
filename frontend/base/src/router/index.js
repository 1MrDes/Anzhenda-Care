import Vue from 'vue';
import Router from 'vue-router';

import {APP_NAME} from '../common/constants';
import {BASE_PATH} from "../common/api";

Vue.use(Router);

export default new Router({
  mode: 'history',
  base: BASE_PATH,
  routes: [
    {
      path: '/',
      component: () => import('../views/Home'),
      meta: {
        title: APP_NAME,
        authentication: 'login'
      },
      children: [
        {
          path: '',
          component: () => import('../views/Main'),
          redirect: {name: 'Main'},
          meta: {
            title: APP_NAME,
            authentication: 'login'
          }
        },
        {
          path: '/main',
          component: () => import('../views/Main'),
          name: 'Main',
          meta: {
            title: APP_NAME,
            authentication: 'login'
          }
        },
        {
          path: '/admin/lists',
          component: () => import('../views/admin/list'),
          name: 'AdminList',
          meta: {
            title: '管理员',
            authentication: 'role'
          }
        },
        {
          path: '/admin/add',
          component: () => import('../views/admin/form'),
          name: 'AdminAdd',
          meta: {
            title: '管理员',
            authentication: 'role'
          }
        },
        {
          path: '/admin/edit/:id',
          component: () => import('../views/admin/form'),
          name: 'AdminEdit',
          meta: {
            title: '管理员',
            authentication: 'role'
          }
        },
        {
          path: '/admin/password',
          component: () => import('../views/admin/password'),
          name: 'AdminPassword',
          meta: {
            title: '修改密码',
            authentication: 'login'
          }
        },

        {
          path: '/resource/lists',
          component: () => import('../views/resource/list'),
          name: 'ResourceList',
          meta: {
            title: '资源',
            authentication: 'role'
          }
        },
        {
          path: '/resource/add',
          component: () => import('../views/resource/form'),
          name: 'ResourceAdd',
          meta: {
            title: '资源',
            authentication: 'role'
          }
        },
        {
          path: '/resource/edit/:id',
          component: () => import('../views/resource/form'),
          name: 'ResourceEdit',
          meta: {
            title: '资源',
            authentication: 'role'
          }
        },

        {
          path: '/role/lists',
          component: () => import('../views/role/list'),
          name: 'RoleList',
          meta: {
            title: '角色',
            authentication: 'role'
          }
        },
        {
          path: '/role/add',
          component: () => import('../views/role/form'),
          name: 'RoleAdd',
          meta: {
            title: '角色',
            authentication: 'role'
          }
        },
        {
          path: '/role/edit/:id',
          component: () => import('../views/role/form'),
          name: 'RoleEdit',
          meta: {
            title: '角色',
            authentication: 'role'
          }
        },
        {
          path: '/role/resources/:id',
          component: () => import('../views/role/resources'),
          name: 'RoleResources',
          meta: {
            title: '角色',
            authentication: 'role'
          }
        },

        {
          path: '/setting/basic',
          component: () => import('../views/setting/Basic'),
          name: 'BasicSiteConfig',
          meta: {
            title: '基本设置',
            authentication: 'role'
          }
        },

        {
          path: '/region/lists',
          component: () => import('../views/region/list'),
          name: 'regionList',
          meta: {
            title: '地区',
            authentication: 'role'
          }
        },
        {
          path: '/region/lists/:parentId',
          component: () => import('../views/region/list'),
          name: 'regionsList',
          meta: {
            title: '地区',
            authentication: 'role'
          }
        },
        {
          path: '/region/add/:parentId/:parentName',
          component: () => import('../views/region/form'),
          name: 'addRegion',
          meta: {
            title: '地区',
            authentication: 'role'
          }
        },
        {
          path: '/region/edit/:regionId',
          component: () => import('../views/region/form'),
          name: 'editRegion',
          meta: {
            title: '地区',
            authentication: 'role'
          }
        },

        {
          path: '/sms_platform/lists',
          component: () => import('../views/sms/platform/list'),
          name: 'smsPlatformList',
          meta: {
            title: '短信平台',
            authentication: 'role'
          }
        },
        {
          path: '/sms_platform/add',
          component: () => import('../views/sms/platform/form'),
          name: 'addSmsPlatform',
          meta: {
            title: '短信平台',
            authentication: 'role'
          }
        },
        {
          path: '/sms_platform/edit/:id',
          component: () => import('../views/sms/platform/form'),
          name: 'editSmsPlatform',
          meta: {
            title: '短信平台',
            authentication: 'role'
          }
        },
        {
          path: '/sms_template/lists',
          component: () => import('../views/sms/template/list'),
          name: 'smsTemplateList',
          meta: {
            title: '短信模板',
            authentication: 'role'
          }
        },
        {
          path: '/sms_template/add',
          component: () => import('../views/sms/template/form'),
          name: 'addSmsTemplate',
          meta: {
            title: '短信模板',
            authentication: 'role'
          }
        },
        {
          path: '/sms_template/edit/:id',
          component: () => import('../views/sms/template/form'),
          name: 'editSmsTemplate',
          meta: {
            title: '短信模板',
            authentication: 'role'
          }
        },

        {
          path: '/article/lists',
          component: () => import('../views/article/list'),
          name: 'articleList',
          meta: {
            title: '文章',
            authentication: 'role'
          }
        },
        {
          path: '/article/add',
          component: () => import('../views/article/form'),
          name: 'addArticle',
          meta: {
            title: '文章',
            authentication: 'role'
          }
        },
        {
          path: '/article/edit/:id',
          component: () => import('../views/article/form'),
          name: 'editArticle',
          meta: {
            title: '文章',
            authentication: 'role'
          }
        },

        {
          path: '/article_category/lists',
          component: () => import('../views/article/category/list'),
          name: 'articleCategoryList',
          meta: {
            title: '文章分类',
            authentication: 'role'
          }
        },
        {
          path: '/article_category/add',
          component: () => import('../views/article/category/form'),
          name: 'addArticleCategory',
          meta: {
            title: '文章分类',
            authentication: 'role'
          }
        },
        {
          path: '/article_category/edit/:id',
          component: () => import('../views/article/category/form'),
          name: 'editArticleCategory',
          meta: {
            title: '文章分类',
            authentication: 'role'
          }
        }

      ]
    },
    {
      path: '/user/login',
      component: () => import('../views/user/Login'),
      name: 'UserLogin',
      meta: {
        title: APP_NAME,
        authentication: 'non'
      }
    },
    {
      path: '/404',
      component: () => import('../components/404.vue'),
      name: 'NotFound',
      meta: {
        title: '找不到页面',
        authentication: 'non'
      }
    },
    {
      path: '/unauthorized',
      component: () => import('../components/Unauthorized'),
      name: 'Unauthorized',
      meta: {
        title: '未授权',
        authentication: 'non'
      }
    },
    {
      path: '*',
      redirect: {path: '/404'}
    }
  ]
})
