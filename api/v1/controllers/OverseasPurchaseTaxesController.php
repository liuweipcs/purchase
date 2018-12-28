<?php
namespace app\api\v1\controllers;

use app\api\v1\models\PurchaseOrderTaxes;
use Yii;
use yii\db\Exception;


class OverseasPurchaseTaxesController extends BaseController {
    public function actionGetTaxes(){
        try{
            $sku = Yii::$app->request->getQueryParam('sku');
            if(empty($sku)){
                throw new Exception('确少必要参数');
            }
            $taxes = PurchaseOrderTaxes::find()->select('taxes')->where(['sku'=>$sku])->orderBy('id DESC')->scalar();
            $taxes = $taxes ? $taxes : 0;
            echo json_encode(['status'=>'success','taxes'=>$taxes]);
            Yii::$app->end();
        }catch (Exception $e){
            echo json_encode(['status'=>'error','message'=>'开票点获取失败']);
            Yii::$app->end();
        }
    }
}