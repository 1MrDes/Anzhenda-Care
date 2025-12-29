<?php
return [
    'alipay' => [
        'miniapp' => [
            'app_id' => '',
            'aes_key' => '',
            'app_cert_path' => DOC_PATH . 'data/alipay_mini/.crt',
            'alipay_cert_path' => DOC_PATH . 'data/.crt',
            'root_cert_path' => DOC_PATH . 'data/alipay_mini/alipayRootCert.crt',
            'app_cert_private_path' => DOC_PATH . 'data/alipay_mini/.vanmai.cn_private.txt'
        ],
        'payment' => [
            'app_cert_path' => DOC_PATH . 'data/alipay/.crt',
            'alipay_cert_path' => DOC_PATH . 'data/alipay/.crt',
            'root_cert_path' => DOC_PATH . 'data/alipay/.crt',
            'app_cert_private_path' => DOC_PATH . 'data/alipay/.txt'
        ]
    ],
    'partner_pay' => [
        'appid' => '',
        'sign_key' => '',
        'api_token' => ''
    ],
    'express_company' => [
        'yuantong' => [
            'code' => 'yuantong',
            'name' => '圆通快递'
        ],
        'yuanda' => [
            'code' => 'yuanda',
            'name' => '韵达快递'
        ],
        'ems' => [
            'code' => 'ems',
            'name' => 'EMS快递'
        ],
        'youzhengguonei' => [
            'code' => 'youzhengguonei',
            'name' => '邮政包裹'
        ],
        'shentong' => [
            'code' => 'shentong',
            'name' => '申通'
        ],
        'shunfeng' => [
            'code' => 'shunfeng',
            'name' => '顺丰速运'
        ],
        'rufengda' => [
            'code' => 'rufengda',
            'name' => '如风达'
        ],
        'zhongtong' => [
            'code' => 'zhongtong',
            'name' => '中通速递'
        ],
        'jingdong' => [
            'code' => 'jingdong',
            'name' => '京东快递'
        ],
        'dada' => [
            'code' => 'dada',
            'name' => '达达'
        ],
        'sfexpress' => [
            'code' => 'sfexpress',
            'name' => '顺丰同城'
        ],
        'jitu' => [
            'code' => 'jitu',
            'name' => '极兔快递'
        ],
        'other' => [
            'code' => 'other',
            'name' => '其他快递'
        ],
    ]
];