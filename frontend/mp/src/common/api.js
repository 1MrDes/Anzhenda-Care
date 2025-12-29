const apiHost = require('../../config/api')

const BASE_URL = process.env.BASE_URL;
const BASE_FILE_URL = process.env.BASE_FILE_URL;
const URL_SUFFIX = '';

export const STATIC_BASE_URL = process.env.BASE_STATIC_URL;
export const BASE_PATH = process.env.NODE_ENV === 'production' ? apiHost.BASE_PATH_RELEASE : apiHost.BASE_PATH_DEV;

export const fileUploadUri = BASE_FILE_URL + '/upload/form' + URL_SUFFIX;
export const fileUploadByBase64Uri = BASE_FILE_URL + '/upload/base64' + URL_SUFFIX;
export const ueFileUploadUri = BASE_FILE_URL + '/upload/ue' + URL_SUFFIX;

export const siteConfigUri = `${BASE_URL}/index/site_config${URL_SUFFIX}`;

export const userLoginUri = `${BASE_URL}/user/login${URL_SUFFIX}`;
export const userLogoutUri = `${BASE_URL}/user/logout${URL_SUFFIX}`;
export const userLoginByTokenUri = `${BASE_URL}/user/login_by_token${URL_SUFFIX}`;

export const smsCaptchaSendUri = `${BASE_URL}/sms_captcha/send${URL_SUFFIX}`;
export const captchaEntryUri = `${BASE_URL}/captcha/entry${URL_SUFFIX}`;

export const feedbackSubmitUri = `${BASE_URL}/feedback/submit${URL_SUFFIX}`;
