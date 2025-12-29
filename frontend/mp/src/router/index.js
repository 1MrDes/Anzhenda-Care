import Vue from 'vue';
import Router from 'vue-router';
Vue.use(Router);

import {APP_NAME} from '../common/constants';
const apiHost = require('../../config/api');

export default new Router({
  mode: 'history',
  base: process.env.NODE_ENV === 'production' ? apiHost.BASE_PATH_RELEASE : apiHost.BASE_PATH_DEV,
  routes: [
    {
      path: '/',
      component: () => import('../views/Home'),
      children: [
        {
          path: '',
          redirect: {name:'Main'}
        },
        {
          path: '/main',
          component: () => import('../views/Main'),
          name: 'Main',
          meta: {
            title: APP_NAME,
            authentication: 'non'
          }
        },
        {
          path: '/article/detail',
          component: () => import('../views/article/Detail'),
          name: 'ArticleDetail',
          meta: {
            title: APP_NAME,
            authentication: 'non'
          }
        },
      ]
    },
    {
      path: '/about',
      component: () => import('../views/About'),
      name: 'About',
      meta: {
        title: '关于我们',
        authentication: 'non'
      }
    },
    {
      path: '/privacy_policy',
      component: () => import('../views/PrivacyPolicy'),
      name: 'PrivacyPolicy',
      meta: {
        title: '隐私政策',
        authentication: 'non'
      }
    },
    {
      path: '/third_privacy_policy',
      component: () => import('../views/ThirdPrivacyPolicy'),
      name: 'ThirdPrivacyPolicy',
      meta: {
        title: '第三方合作伙伴及共享信息说明',
        authentication: 'non'
      }
    },
    {
      path: '/terms_conditions',
      component: () => import('../views/TermsConditions'),
      name: 'TermsConditions',
      meta: {
        title: '用户协议',
        authentication: 'non'
      }
    },
    {
      path: '/faq',
      component: () => import('../views/Faq'),
      name: 'Faq',
      meta: {
        title: '常见问题',
        authentication: 'non'
      }
    },
    {
      path: '/feedback',
      component: () => import('../views/Feedback'),
      name: 'Feedback',
      meta: {
        title: '用户反馈',
        authentication: 'non'
      }
    },
    {
      path: '/service_conditions',
      component: () => import('../views/ServiceConditions'),
      name: 'ServiceConditions',
      meta: {
        title: '服务协议',
        authentication: 'non'
      }
    },
    {
      path: '/assistant_conditions',
      component: () => import('../views/AssistantConditions'),
      name: 'AssistantConditions',
      meta: {
        title: '陪诊师合作协议',
        authentication: 'non'
      }
    },
    {
      path: '/partner_conditions',
      component: () => import('../views/PartnerConditions'),
      name: 'PartnerConditions',
      meta: {
        title: '城市合伙人合作协议',
        authentication: 'non'
      }
    },

    {
      path: '/404',
      component: () => import('../components/404'),
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
