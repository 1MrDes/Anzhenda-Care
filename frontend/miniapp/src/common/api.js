const ENV = process.env.NODE_ENV;

const API_PATH_CONFIG = {
	development: '/api.php',
	production: '/api.php',
};

const FILE_API_PATH_CONFIG = {
	development: '/file/upload.php',
	production: '/upload.php',
};

const STATIC_BASE_URL_CONFIG = {
	development: '/static/health_assist/',
	production: '/static/health_assist/',
};

const API_PATH = API_PATH_CONFIG[ENV];
const FILE_API_PATH = FILE_API_PATH_CONFIG[ENV];
const STATIC_BASE_URL = STATIC_BASE_URL_CONFIG[ENV];

export default {
	STATIC_BASE_URL: STATIC_BASE_URL,

	FILE_UPLOAD_BASE64_URL: FILE_API_PATH + '/upload/base64',
	FILE_UPLOAD_IMG_BASE64_URL: FILE_API_PATH + '/upload/img2base64',
	FILE_UPLOAD_FORM_URL: FILE_API_PATH + '/upload/form',

	SITE_CONFIG_URL: API_PATH + '/index/site_config',
	DECODE_SCENE_URL: API_PATH + '/index/decode_scene',

	REGION_ALL_URL: API_PATH + '/region/all',
	REGION_TREE_URL: API_PATH + '/region/tree',
	REGION_GEO_DECODER_URL: API_PATH + '/region/geodecoder',

	DECRYPT_MSG_URL: API_PATH + '/user/decrypt_msg',
	WEAPP_LOGIN_AUTH_URL: API_PATH + '/user/weapp_login_auth',
	WEAPP_LOGIN_BY_CODE_URL: API_PATH + '/user/weapp_login_by_code',
	WEAPP_BIND_PHONE_NUMBER_URL: API_PATH + '/user/weapp_bind_phone_number',
	USER_LOGIN_BY_TOKEN_URL: API_PATH + '/user/login_by_token',
	ALIPAY_LOGIN_AUTH_URL: API_PATH + '/user/alipay_login_auth',

	ADV_POSITION_URL: API_PATH + '/adv/position',

	MESSAGE_LISTS_URL: API_PATH + '/message/lists',
	MESSAGE_UNREAD_URL: API_PATH + '/message/unread',
	
	MEMBER_ACCOUNT_URL: API_PATH + '/member/account',
	
	PAY_TRADE_QUERY_URL: API_PATH + '/pay_trade/query',
	PAY_TRADE_CREATE_URL: API_PATH + '/pay_trade/create',
	
	CONSIGNEER_ALL_URL: API_PATH + '/consigneer/all',
	CONSIGNEER_SAVE_URL: API_PATH + '/consigneer/save',
	CONSIGNEER_DELETE_URL: API_PATH + '/consigneer/delete',
	CONSIGNEER_FIRST_URL: API_PATH + '/consigneer/first',
	CONSIGNEER_INFO_URL: API_PATH + '/consigneer/info',
	
	USER_ACCOUNT_LISTS_URL: API_PATH + '/user_account/lists',
	
	USER_WITHDRAW_CASH_LISTS_URL: API_PATH + '/user_withdraw_cash/lists',
	USER_WITHDRAW_CASH_APPLY_URL: API_PATH + '/user_withdraw_cash/apply',
	
	EXPRESS_COMPANY_URL: API_PATH + '/index/express_company',
	
	HEALTH_SERVICE_LISTS_URL: API_PATH + '/health_service/lists',
	HEALTH_SERVICE_INFO_URL: API_PATH + '/health_service/info',
	
	HEALTH_SERVICE_ORDER_LISTS_URL: API_PATH + '/health_service_order/lists',
	HEALTH_SERVICE_ORDER_DETAIL_URL: API_PATH + '/health_service_order/detail',
	HEALTH_SERVICE_ORDER_CREATE_URL: API_PATH + '/health_service_order/create',
	HEALTH_SERVICE_ORDER_OP_URL: API_PATH + '/health_service_order/op',
	
	HEALTH_HOSPITAL_LISTS_URL: API_PATH + '/health_hospital/lists',
	HEALTH_HOSPITAL_INFO_URL: API_PATH + '/health_hospital/info',
	HEALTH_HOSPITAL_LABS_URL: API_PATH + '/health_hospital/labs',
	HEALTH_HOSPITAL_REGIONS_URL: API_PATH + '/health_hospital/regions',
	
	REALNAME_VERIFY_TASK_CREATE_URL: API_PATH + '/realname_verify_task/create',
	REALNAME_VERIFY_TASK_QUERY_URL: API_PATH + '/realname_verify_task/query',
	
	HEALTH_ASSISTANT_MY_URL: API_PATH + '/health_assistant/my',
	HEALTH_ASSISTANT_APPLY_URL: API_PATH + '/health_assistant/apply',
	
	HEALTH_ASSISTANT_ORDER_UNASSIGNED_URL: API_PATH + '/health_assistant_order/unassigned',
	HEALTH_ASSISTANT_ORDER_MY_ORDERS_URL: API_PATH + '/health_assistant_order/my_orders',
	HEALTH_ASSISTANT_ORDER_DETAIL_URL: API_PATH + '/health_assistant_order/detail',
	HEALTH_ASSISTANT_ORDER_TAKE_ORDER_URL: API_PATH + '/health_assistant_order/take_order',
	HEALTH_ASSISTANT_ORDER_OP_URL: API_PATH + '/health_assistant_order/op',
	HEALTH_ASSISTANT_ORDER_SHIP_URL: API_PATH + '/health_assistant_order/ship',
}
