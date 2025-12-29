'use strict'
const merge = require('webpack-merge')
const prodEnv = require('./prod.env')

const apiHost = require('./api')

module.exports = merge(prodEnv, {
  NODE_ENV: '"development"',
  BASE_URL: apiHost.BASE_URL_DEV,
  BASE_FILE_URL: apiHost.BASE_FILE_URL_DEV,
  BASE_ASSETS_PATH: apiHost.BASE_PATH_DEV,
  BASE_STATIC_URL: apiHost.BASE_STATIC_URL_DEV
})
