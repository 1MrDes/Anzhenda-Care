'use strict'

const apiHost = require('./api')

let argv;
try {
  argv = JSON.parse(process.env.npm_config_argv).original;
} catch (ex) {
  argv = process.argv;
}

let env = 'release';
let BASE_URL = apiHost.BASE_URL_RELEASE;
let BASE_FILE_URL = apiHost.BASE_FILE_URL_RELEASE;

if(argv) {
  for (let i = 0; i < argv.length; i++) {
    if(argv[i] == 'test') {
      env = 'test';
      break;
    }
  }
}

if(env == 'test') {
  BASE_URL = apiHost.BASE_URL_DEV;
  BASE_FILE_URL = apiHost.BASE_FILE_URL_DEV;
}

module.exports = {
  NODE_ENV: '"production"',
  BASE_URL: BASE_URL,
  BASE_FILE_URL: BASE_FILE_URL,
  BASE_PATH: apiHost.BASE_PATH_RELEASE
}
