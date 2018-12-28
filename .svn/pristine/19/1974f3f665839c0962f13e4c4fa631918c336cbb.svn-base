<?php

$params = require(__DIR__ . '/params.php');
$midDb = require(__DIR__ . '/midDb.php');
Yii::$classMap['BCGColor']= '@app/config/libs/barcode/class/BCGColor.php';
Yii::$classMap['BCGDrawing']= '@app/config/libs/barcode/class/BCGDrawing.php';
Yii::$classMap['BCGcode128']= '@app/config/libs/barcode/class/BCGcode128.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'zh-CN',
    'timezone' => 'Asia/Shanghai',

    'modules' => [
        'admin' => [
            'class' => 'mdm\admin\Module',
        ],
        'v1' => [
            'class' => 'app\api\v1\Module',
        ],
        'gridview' =>  [
            'class' => 'kartik\grid\Module',
            // enter optional module parameters below - only if you need to
            // use your own export download action or custom translation
            // message source
            // 'downloadAction' => 'gridview/export/download',
            // 'i18n' => []
        ],
        'synchcloud' =>  [
            'class' => 'app\synchcloud\SynchcloudModule',
        ],
        'manage' => [
            'class' => 'app\modules\manage\manage',
        ]
    ],

    'aliases' => [
        '@mdm/admin' => '@vendor/mdmsoft/yii2-admin',
        '@template'  => '@app/views/template/tpls',
    ],
    'as access' => [

        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            //'*',
            'v1/*',
            'debug/*',
            'tong-tool-purchase/read-purchase',
            'purchase-tactics/purchasing-advice',
            'purchase-tactics/owed-goods',
            'purchase-tactics/ji-suan',
            'purchase-tactics/update-ji-suan',
            'overseas-warehouse-sku-sales-list/purchasing-advice',
            'overseas-warehouse-sku-sales-list/owed-goods-sales',
            'purchase-order-confirm/add-temporary',
            'purchase-order-confirm/import-product',
            'purchase-order-confirm/template',
            'purchase-order-confirm/eliminate',
            'purchase-order-confirm/product-index',
            'overseas-purchase-demand/add-temporary',
            'overseas-purchase-demand/import-product',
            'overseas-purchase-demand/template',
            'overseas-purchase-demand/eliminate',
            'overseas-purchase-demand/product-index',
            'purchase-order/edit',
            'purchase-suggest/img',
            'gii/*',
            'purchase-tactics/statistics-owed-goods',
            'purchase-tactics/calculate',
            'purchase-tactics/check',
            'purchase-tactics/checks',
            'purchase-tactics/session-count',
            'purchase-order-cashier-pay/export-cvs',
            'purchase-order-receipt-notification/export-cvs',
            'site/login',
            'site/checktoken',
            'site/login-by-token',
            'site/change-password-bytoken',
            'site/check',
            'bulletin-board/upload',
            'product/get-name',
            'product/create-purchase-bind',
            'product/desc',
            'supplier/ex-supplier',
            'overseas-purchase-demand/purchase',
            'overseas-purchase-demand/purchase-fba',
            'purchase-order/fb',
            'purchase-order/one',
            'purchase-order/get',
            'purchase-order/gets',
            'purchase-order/s',
            'purchase-order/get-pur',
            'purchase-order/qty',
            'purchase-order/back-up',
            'overseas-purchase-demand/test',
            'overseas-purchase-demand/update-push',
            'purchase-tactics/qian-huo',
            'purchase-tactics/del-qian',
            'purchase-order-real-time-sku/test',
            'purchase-tactics/purchasing-advice-owe',
            'exp-ruku/push-handler-info',
            'lower-rate-statistics/total-lower-rate-statistics',
            'logistics-import/push-logistics-info',
            'overseas-purchase-demand/suggest-agree',
            'overseas-tracking/index',
            'synchcloud/*',
            'supplier/erp-create',
            'supplier/erp-update',
            'stock-detail/index',
            'lock-warehouse-config/index',
            'overseas-warehouse-sku-paid-wait/index-embedded',
            'overseas-warehouse-sku-paid-wait/view-sku',
            'overseas-warehouse-sku-paid-wait/export-sku-paid-wait',
            'supplier/get-supplier-info',
            'supplier-kpi-check/push-sku-price',
            'supplier-kpi-check/push-erp-sku-price',
            'supplier-kpi-check/push-purchase-price',
            'member/*',

        ]
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '_csrf-ssss',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
       /* 'session' => [
            'class' => 'yii\web\DbSession',
            // 'db' => 'mydb',  // 数据库连接的应用组件ID，默认为'db'.
            'sessionTable' => 'pur_session', // session 数据表名，默认为'session'.
        ],*/
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            // 'enableAutoLogin' => true,
            'authTimeout'     =>'6000',



        ],

        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',

        ],

       'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => YII_DEBUG ? true : false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.qq.com',  //每种邮箱的host配置不一样
//                'username' => '897034908@qq.com',//邮箱账号
//                'password' => 'qcldtoyqtdbrbdhf',//邮箱密码（填授权码！！！）
                'username' => '1159240689@qq.com',//邮箱账号
                'password' => 'wxcyqpawsecogbbj',//邮箱密码（填授权码！！！）
                'port' => '465',
                'encryption' => 'ssl',

            ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['1159240689@qq.com'=>'admin']
             //   'from'=>['897034908@qq.com'=>'admin']
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                'email' => [
                    'class' => 'yii\log\EmailTarget',
                    'levels' => ['error', 'warning'],
                    'message' => [
                        'to' => ['1159240689@qq.com','361870038@qq.com','15211527620@163.com'],
                        'subject' => '少年,采购系统报错了啊！快去处理啊',
                    ],
                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                    'logVars'=>[],
                ],
            ],
        ],




        'db' => require(__DIR__ . '/db.php'),

        'mid_cloud' => $midDb['mid_cloud'],
        'cloud_basic' => $midDb['cloud_basic'],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,

            'rules' => [
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<modules:\w+>/<controller:\w+>/<action:\w+>'=>'<modules>/<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ],
        ],

        'pdf' => [
            'class' => kartik\mpdf\Pdf::classname(),
            'mode' => kartik\mpdf\Pdf::MODE_UTF8,
            'format' => kartik\mpdf\Pdf::FORMAT_A4,
            'orientation' => kartik\mpdf\Pdf::ORIENT_PORTRAIT,
            'destination' => kartik\mpdf\Pdf::DEST_BROWSER,
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',

        'allowedIPs' => ['127.0.0.1', '::1', '192.168.13.*', '*'] // 按需调整这里
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.13.*', '*'] // 按需调整这里
    ];
}

return $config;
