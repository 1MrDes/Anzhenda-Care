// 提现状态
export const WITHDRAW_CASH_STATUS_AUDITTING = 5; // 审核中
export const WITHDRAW_CASH_STATUS_WAIT_PAY = 10; // 待打款
export const WITHDRAW_CASH_STATUS_PAIED = 20;    // 已打款
export const WITHDRAW_CASH_STATUS_REJECTED = 30;    // 被拒绝
// 提现打款方式
export const WITHDRAW_CASH_PAY_TYPE_WEPAY = 10;  // 微信支付
export const WITHDRAW_CASH_PAY_TYPE_ALIPAY = 20; // 支付宝

// 订单状态
// 10-未确认,20-已确认,25-待发货,30-已发货,40-已完成,50-已取消,60-已退货
export const ORDER_STATUS_UNCONFIRMED = 10;
export const ORDER_STATUS_CONFIRMED = 20;
export const ORDER_STATUS_WAIT_SHIP = 25;
export const ORDER_STATUS_SHIPPED = 30;
export const ORDER_STATUS_FINISHED = 40;
export const ORDER_STATUS_CANCELLED = 50;
export const ORDER_STATUS_REFUNDED = 60;
export const ORDER_STATUS_REFUNDING = 70;
export const ORDER_STATUS_ACTING = 80; // 服务中

// 付款状态
export const PAYMENT_STATUS_UNPAIED = 0;    // 未付款
export const PAYMENT_STATUS_PAIED = 1;      // 已付款

// 配送状态 10-待发货；20-已发货；30-已退货
export const SHIPPING_STATUS_WAIT_SHIP = 10;
export const SHIPPING_STATUS_SHIPPED = 20;
export const SHIPPING_STATUS_REFUNDED = 30;

// 退款状态
export const REFUND_STATUS_AUDITING = 10;  //  审核中
export const REFUND_STATUS_GOODS_RETURNING = 20;  //  退货中
export const REFUND_STATUS_PAYING = 30;  //  打款中
export const REFUND_STATUS_FINISHED = 40;  //  已完成

// 配送方式 10-线下服务；20-物流配送
export const HEALTH_SERVICE_ORDER_SHIPPING_TYPE_OFFLINE = 10;
export const HEALTH_SERVICE_ORDER_SHIPPING_TYPE_EXPRESS = 20;

// 陪诊师申请状态
export const HEALTH_ASSISTANT_STATUS_WAIT_AUDIT = 0;    // 待审核
export const HEALTH_ASSISTANT_STATUS_AUDIT_PASS = 1;    // 通过审核
export const HEALTH_ASSISTANT_STATUS_AUDIT_REJECT = 2;    // 申请被拒绝
