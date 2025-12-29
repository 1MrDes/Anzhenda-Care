const apiHost = require('../../config/api');

const BASE_URL = process.env.BASE_URL;
const BASE_FILE_URL = process.env.BASE_FILE_URL;

export const BASE_PATH = process.env.NODE_ENV === 'production' ? apiHost.BASE_PATH_RELEASE : apiHost.BASE_PATH_DEV;

export const fileUploadUri = BASE_FILE_URL + '/upload/form';
export const ueFileUploadUri = BASE_FILE_URL + '/upload/ue';

export const loginUri = `${BASE_URL}/user/login`;
export const logoutUri = `${BASE_URL}/user/logout`;

export const roleListsUri = `${BASE_URL}/role/lists`;
export const roleAllUri = `${BASE_URL}/role/all`;
export const roleResourcesUri = `${BASE_URL}/role/resources`;
export const roleInfoUri = `${BASE_URL}/role/info`;
export const roleSaveUri = `${BASE_URL}/role/save`;
export const roleDeleteUri = `${BASE_URL}/role/delete`;

export const resourceListsUri = `${BASE_URL}/resource/lists`;
export const resourceOptionsUri = `${BASE_URL}/resource/options`;
export const resourceInfoUri = `${BASE_URL}/resource/info`;
export const resourceSubmitUri = `${BASE_URL}/resource/submit`;
export const resourceDeleteUri = `${BASE_URL}/resource/delete`;

export const adminListsUri = `${BASE_URL}/admin/lists`;
export const adminInfoUri = `${BASE_URL}/admin/info`;
export const adminSubmitUri = `${BASE_URL}/admin/submit`;
export const adminLockUri = `${BASE_URL}/admin/lock`;
export const adminUnlockUri = `${BASE_URL}/admin/unlock`;
export const adminPasswordUri = `${BASE_URL}/admin/password`;

export const siteConfigAllUri = `${BASE_URL}/site_config/all`;
export const siteConfigSaveUri = `${BASE_URL}/site_config/save`;

export const regionListsUri = `${BASE_URL}/region/lists`;
export const regionTreeUri = `${BASE_URL}/region/tree`;
export const regionInfoUri = `${BASE_URL}/region/info`;
export const regionSubmitUri = `${BASE_URL}/region/submit`;
export const regionDeleteUri = `${BASE_URL}/region/delete`;
export const regionChildUri = `${BASE_URL}/region/child`;

export const smsPlatformListUri = `${BASE_URL}/sms_platform/lists`;
export const smsPlatformInfoUri = `${BASE_URL}/sms_platform/info`;
export const smsPlatformSubmitUri = `${BASE_URL}/sms_platform/submit`;
export const smsPlatformDeleteUri = `${BASE_URL}/sms_platform/delete`;
export const smsPlatformCheckCodeExistsUri = `${BASE_URL}/sms_platform/check_code_exists`;

export const smsTemplateListUri = `${BASE_URL}/sms_template/lists`;
export const smsTemplateInfoUri = `${BASE_URL}/sms_template/info`;
export const smsTemplateSubmitUri = `${BASE_URL}/sms_template/submit`;
export const smsTemplateDeleteUri = `${BASE_URL}/sms_template/delete`;
export const smsTemplateCheckCodeExistsUri = `${BASE_URL}/sms_template/check_code_exists`;
export const smsTemplatePlatformsUri = `${BASE_URL}/sms_template/platforms`;

export const articleListUri = `${BASE_URL}/article/lists`;
export const articleInfoUri = `${BASE_URL}/article/info`;
export const articleDeleteUri = `${BASE_URL}/article/delete`;
export const articleSubmitUri = `${BASE_URL}/article/submit`;
export const articleCategoryListUri = `${BASE_URL}/article_category/lists`;
export const articleCategoryInfoUri = `${BASE_URL}/article_category/info`;
export const articleCategorySubmitUri = `${BASE_URL}/article_category/submit`;
export const articleCategoryDeleteUri = `${BASE_URL}/article_category/delete`;

