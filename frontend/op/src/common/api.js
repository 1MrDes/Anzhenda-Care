const apiHost = require('../../config/api');

const BASE_URL = process.env.BASE_URL;
const BASE_FILE_URL = process.env.BASE_FILE_URL;
const URL_SUFFIX = '';

export const STATIC_BASE_URL = process.env.BASE_STATIC_URL;
export const BASE_PATH = process.env.NODE_ENV === 'production' ? apiHost.BASE_PATH_RELEASE : apiHost.BASE_PATH_DEV;
/*-------------------------------------------------------------------------------------------------------------------*/
export const fileUploadUri = BASE_FILE_URL + '/upload/form' + URL_SUFFIX;
export const fileUploadByBase64Uri = BASE_FILE_URL + '/upload/base64' + URL_SUFFIX;
export const ueFileUploadUri = BASE_FILE_URL + '/upload/ue' + URL_SUFFIX;

export const regionTreeUri = `${BASE_URL}/region/tree${URL_SUFFIX}`;
export const regionAllUri = `${BASE_URL}/region/all${URL_SUFFIX}`;

export const userLoginUri = `${BASE_URL}/user/login${URL_SUFFIX}`;
export const userLogoutUri = `${BASE_URL}/user/logout${URL_SUFFIX}`;
export const userLoginByTokenUri = `${BASE_URL}/user/login_by_token${URL_SUFFIX}`;

export const roleListsUri = `${BASE_URL}/role/lists${URL_SUFFIX}`;
export const roleAllUri = `${BASE_URL}/role/all${URL_SUFFIX}`;
export const roleResourcesUri = `${BASE_URL}/role/resources${URL_SUFFIX}`;
export const roleInfoUri = `${BASE_URL}/role/info${URL_SUFFIX}`;
export const roleAddUri = `${BASE_URL}/role/add${URL_SUFFIX}`;
export const roleEditUri = `${BASE_URL}/role/edit${URL_SUFFIX}`;
export const roleDeleteUri = `${BASE_URL}/role/delete${URL_SUFFIX}`;

export const resourceListsUri = `${BASE_URL}/resource/lists${URL_SUFFIX}`;
export const resourceOptionsUri = `${BASE_URL}/resource/options${URL_SUFFIX}`;
export const resourceInfoUri = `${BASE_URL}/resource/info${URL_SUFFIX}`;
export const resourceSubmitUri = `${BASE_URL}/resource/submit${URL_SUFFIX}`;
export const resourceDeleteUri = `${BASE_URL}/resource/delete${URL_SUFFIX}`;

export const adminListsUri = `${BASE_URL}/admin/lists${URL_SUFFIX}`;
export const adminInfoUri = `${BASE_URL}/admin/info${URL_SUFFIX}`;
export const adminSubmitUri = `${BASE_URL}/admin/submit${URL_SUFFIX}`;
export const adminLockUri = `${BASE_URL}/admin/lock${URL_SUFFIX}`;
export const adminUnlockUri = `${BASE_URL}/admin/unlock${URL_SUFFIX}`;
export const adminPasswordUri = `${BASE_URL}/admin/password${URL_SUFFIX}`;

export const advPositionListsUri = `${BASE_URL}/adv_position/lists${URL_SUFFIX}`;
export const advPositionSaveUri = `${BASE_URL}/adv_position/save${URL_SUFFIX}`;
export const advPositionDeleteUri = `${BASE_URL}/adv_position/delete${URL_SUFFIX}`;
export const advPositionAllUri = `${BASE_URL}/adv_position/all${URL_SUFFIX}`;

export const advListsUri = `${BASE_URL}/adv/lists${URL_SUFFIX}`;
export const advSaveUri = `${BASE_URL}/adv/save${URL_SUFFIX}`;
export const advDeleteUri = `${BASE_URL}/adv/delete${URL_SUFFIX}`;
export const advInfoUri = `${BASE_URL}/adv/info${URL_SUFFIX}`;

export const siteConfigAllUri = `${BASE_URL}/site_config/all${URL_SUFFIX}`;
export const siteConfigSaveUri = `${BASE_URL}/site_config/save${URL_SUFFIX}`;

export const articleCategoryTreeUri = `${BASE_URL}/article_category/tree${URL_SUFFIX}`;
export const articleCategoryListsUri = `${BASE_URL}/article_category/lists${URL_SUFFIX}`;
export const articleCategorySaveUri = `${BASE_URL}/article_category/save${URL_SUFFIX}`;
export const articleCategoryDeleteUri = `${BASE_URL}/article_category/delete${URL_SUFFIX}`;

export const articleListsUri = `${BASE_URL}/article/lists${URL_SUFFIX}`;
export const articleSaveUri = `${BASE_URL}/article/save${URL_SUFFIX}`;
export const articleDeleteUri = `${BASE_URL}/article/delete${URL_SUFFIX}`;
export const articleInfoUri = `${BASE_URL}/article/info${URL_SUFFIX}`;

export const memberListsUri = `${BASE_URL}/member/lists${URL_SUFFIX}`;

export const withdrawCashListsUri = `${BASE_URL}/withdraw_cash/lists${URL_SUFFIX}`;
export const withdrawCashInfoUri = `${BASE_URL}/withdraw_cash/info${URL_SUFFIX}`;
export const withdrawCashAgreeUri = `${BASE_URL}/withdraw_cash/agree${URL_SUFFIX}`;
export const withdrawCashRejectUri = `${BASE_URL}/withdraw_cash/reject${URL_SUFFIX}`;
export const withdrawCashPayUri = `${BASE_URL}/withdraw_cash/pay${URL_SUFFIX}`;

export const goodsListsUri = `${BASE_URL}/health_service/lists${URL_SUFFIX}`;
export const goodsSaveUri = `${BASE_URL}/health_service/save${URL_SUFFIX}`;
export const goodsDeleteUri = `${BASE_URL}/health_service/delete${URL_SUFFIX}`;
export const goodsInfoUri = `${BASE_URL}/health_service/info${URL_SUFFIX}`;
export const goodsToggleSaleStatusUri = `${BASE_URL}/health_service/toggle_sale_status${URL_SUFFIX}`;

export const goodsCategoryTreeUri = `${BASE_URL}/health_service_category/tree${URL_SUFFIX}`;
export const goodsCategoryListsUri = `${BASE_URL}/health_service_category/lists${URL_SUFFIX}`;
export const goodsCategorySaveUri = `${BASE_URL}/health_service_category/save${URL_SUFFIX}`;
export const goodsCategoryDeleteUri = `${BASE_URL}/health_service_category/delete${URL_SUFFIX}`;
export const goodsCategoryInfoUri = `${BASE_URL}/health_service_category/info${URL_SUFFIX}`;

export const goodsTypeListsUri = `${BASE_URL}/health_service_type/lists${URL_SUFFIX}`;
export const goodsTypeSaveUri = `${BASE_URL}/health_service_type/save${URL_SUFFIX}`;
export const goodsTypeDeleteUri = `${BASE_URL}/health_service_type/delete${URL_SUFFIX}`;
export const goodsTypeInfoUri = `${BASE_URL}/health_service_type/info${URL_SUFFIX}`;

export const goodsAttrListsUri = `${BASE_URL}/health_service_type_attribute/lists${URL_SUFFIX}`;
export const goodsAttrAllUri = `${BASE_URL}/health_service_type_attribute/all${URL_SUFFIX}`;
export const goodsAttrSaveUri = `${BASE_URL}/health_service_type_attribute/save${URL_SUFFIX}`;
export const goodsAttrDeleteUri = `${BASE_URL}/health_service_type_attribute/delete${URL_SUFFIX}`;
export const goodsAttrInfoUri = `${BASE_URL}/health_service_type_attribute/info${URL_SUFFIX}`;

export const goodsTypeSpecAllUri = `${BASE_URL}/health_service_type_spec/all${URL_SUFFIX}`;
export const goodsTypeSpecSaveUri = `${BASE_URL}/health_service_type_spec/save${URL_SUFFIX}`;
export const goodsTypeSpecDeleteUri = `${BASE_URL}/health_service_type_spec/delete${URL_SUFFIX}`;
export const goodsTypeSpecInfoUri = `${BASE_URL}/health_service_type_spec/info${URL_SUFFIX}`;

export const orderListsUri = `${BASE_URL}/health_service_order/lists${URL_SUFFIX}`;
export const orderInfoUri = `${BASE_URL}/health_service_order/info${URL_SUFFIX}`;
export const orderOpUri = `${BASE_URL}/health_service_order/op${URL_SUFFIX}`;
export const orderShipUri = `${BASE_URL}/health_service_order/ship${URL_SUFFIX}`;
export const orderPayUri = `${BASE_URL}/health_service_order/pay${URL_SUFFIX}`;
export const orderModifyMoneyUri = `${BASE_URL}/health_service_order/modify_order_money${URL_SUFFIX}`;
export const orderAssignAssistantUri = `${BASE_URL}/health_service_order/assign_assistant${URL_SUFFIX}`;

export const healthAssistantListsUri = `${BASE_URL}/health_assistant/lists${URL_SUFFIX}`;
export const healthAssistantAuditUri = `${BASE_URL}/health_assistant/audit${URL_SUFFIX}`;


