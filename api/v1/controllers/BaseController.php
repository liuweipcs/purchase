<?php
namespace app\api\v1\controllers;
use app\config\Vhelper;
use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class BaseController extends ActiveController
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];
    public $modelClass = 'app\api\v1\models\User';
    public $enableCsrfValidation = false;
    public function behaviors()
    {
        return [

            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
            //关闭token验证
//        'authenticator' => [
//           'class' => HttpBasicAuth::className(),
//           ],
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
        ];
    }
    public function actions()
    {
        $actions = parent::actions();

        // 禁用"delete" 和 "create" 动作
        unset($actions['index'], $actions['create'],$actions['delete'],$actions['view']);

    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }


    public function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
    public function method()
    {
        $M =[   
                'v1/purchase-order/buyer-num',
                'v1/purchase-order/purchase-all',
                'v1/purchase-order/purchase-cancel',
                'v1/purchase-order/get-sku-avg',
                'v1/purchase-order/tong-tool-purchase',
                'v1/supplier/supplier-quotation',
                'v1/supplier/proposal-result',
                'v1/supplier/proposal-template',
                'v1/purchase-order/purchase-ship-all',
                'v1/purchase-exception/push-receive-qc',
                'v1/purchase-exception/push-abnomal',
                'v1/purchase-exception/push-return-goods',
                'v1/purchase-exception/push-replacement',
                'v1/purchase-exception/reject',
                'v1/supplier/create-supplier-tong-tool',
                'v1/stock/get-stock',
                'v1/alibaba/get-logistics',
                'v1/alibaba/get-token',
                'v1/alibaba/refresh-token',
                'v1/alibaba/get-order-info',
                'v1/purchase-order/purchase-demand-all',
                'v1/product/get-product',
                'v1/warehouse/get-arriva',
                'v1/warehouse/get-boxskuqty',
                'v1/product/get-product-line',
                'v1/product/get-sku-sales',
                'v1/product/get-product-supplier',
                'v1/product/get-supplier',
                'v1/product/s',
                'v1/product/get-suggest',
                'v1/product/get-suggest-mrp',
                'v1/stock/get-less',
                'v1/stock/get-less-platform',
                'v1/platform-summary/erp-demand',
        		'v1/platform-summary/getlinebuyer',
        		'v1/platform-summary/skusalesdetail',
                'v1/purchase-order/update-purchase',
                'v1/supplier/get-cost-num',
                'v1/purchase-exception/pull-warehouse-exp',
                'v1/purchase-exception/get-warehouse-images',
                'v1/supplier/change-supplier',
                'v1/product/calculate-month-avg',
                'v1/product/push-month-avg',
                'v1/product/get-hwc-avg-arrival',
                'v1/purchase-exception/get-warehouse-result',
                'v1/supplier/get-supplier-kpi',
                'v1/product/push-avg-arrive',
                'v1/customer-service/get-service-data',
                'v1/purchase-order/get-early-warning-status',
                'v1/purchase-suggest-quantity/get-suggest-quantity',
                'v1/platform-summary/hwc-platform-demand',
                'v1/token/get-token',
                'v1/token/save-token',
                'v1/statistics/stat-purchase-num',
                'v1/login-verif/check-login',
                'v1/login-verif/check-token',
                'v1/supplier/get-supplier-product-line', 
                'v1/platform-summary/push-verif-result',
                'v1/supplier/push-buyer-info',
                'v1/sku-purchase-quantity/get-sku-quantity',
                'v1/ali-order-check/get-supplier-adress',
                'v1/product/get-erp-supplier',
                'v1/refund-qty/refund',
                'v1/refund-qty/refund-breakage',
                'v1/purchase-order/pull-cancel-stock',
                'v1/purchase-order/pull-cancel-overseas',
                'v1/overseas-purchase-taxes/get-taxes',
                'v1/develop-track/get-purchase-node',
                'v1/develop-track/get-develop-node',
                'v1/overseas-purchase-taxes/get-taxes',
                'v1/fba-outstock/get-purchase-suggest',
                'v1/purchase-exception/get-excep-return-info',
                'v1/erp-sync/push-product-supplier-to-erp',
                'v1/erp-sync/push-demand-rule-to-erp',
                'v1/erp-sync/push-supplier-info-to-erp',
                'v1/erp-sync/push-source-status-to-erp',
                'v1/erp-sync/get-sku-summary-info',
                'v1/erp-sync/push-purchasing-data',
                'v1/erp-sync/push-order-taxes-to-data-center',
                'v1/erp-sync/get-sku-family',
                'v1/erp-sync/test',
                'v1/statistics/stat-inland-avg-delivery',
                'v1/purchase-suggest-quantity/untreated-time',
                'v1/purchase-suggest-quantity/get-purchase-suggest',
                'v1/declare-customs/get-customs-details',
                'v1/supplier/integration',
                'v1/ufx-fuiou/accept-result',
                'v1/ufx-fuiou/get-pay-back',
                'v1/large-warehouse/add-large-warehouse',
                'v1/oa-account/get-change-account',
                'v1/transfer-order/query-sku-lock-status',
                'v1/transfer-order/create-transfer-order',
                'v1/transfer-order/create-transfer',
                'v1/product/push-tax-point',
                'v1/erp-sync/push-freight-to-data-center',
                'v1/erp-sync/push-avg-price-to-erp',
                'v1/purchase-tactics-config/get-mrp-config',
                'v1/purchase-tactics-config/get-mrp-warehouse-config',
                'v1/supplier/supplier-check',
                'v1/platform-summary/importcaiwu',
                'v1/platform-summary/importtaxes',
                'v1/product/receive-product-info',
                'v1/product/push-sku-weight-mark-to-data-center',
                'v1/product-supplier-change/push-to-erp',
                'v1/product-supplier-change/receive-block-result',
                'v1/product-supplier-change/push-supplier-check-result',
                'v1/erp-sync/auto-push-supplier-simple-info-to-erp',
        ];
        return $M;
    }
    public function beforeAction($action)
    {

        if(in_array($action->uniqueId,$this->method()))
        {
            return parent::beforeAction($action);
        } else {
            $token= Yii::$app->request->post();
            if (!isset($token['token']))
            {
                exit('token不能为空');
            }
            $token = Json::decode($token['token']);
            //验证通过
            $past= Vhelper::stockUnAuth($token['param'],$token['sign']);
            if ($past['error']=='-1')
            {
                return Json::encode($past);
            }
            return parent::beforeAction($action);
        }
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        $result['status']  = Yii::$app->response->statusCode;
        $result['message'] = Yii::$app->response->statusText;
        return $this->serializeData($result);
    }

    public function checkAccess($action, $model = null, $params = [])
    {

    }
}
