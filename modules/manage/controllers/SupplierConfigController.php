<?php
namespace app\modules\manage\controllers;

use app\config\Vhelper;
use app\controllers\BaseController;
use app\models\Supplier;
use app\modules\manage\models\ProductLine;
use app\modules\manage\models\SupplierManageConfig;
use app\modules\manage\models\SupplierManageConfigItems;
use Yii;
use yii\helpers\Html;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 18:15
 */
class SupplierConfigController extends BaseController{
    public function actionQuotesConfig(){
        $ids = Yii::$app->request->getQueryParam('ids');
        $ids = is_array($ids) ?$ids : explode(',',$ids);
        if(empty($ids)||count($ids)>1){
            Yii::$app->end('一次只能配置一个供应商');
        }
        $supplier_code = Supplier::find()->select('supplier_code')->where(['in','id',$ids])->scalar();
        $model = SupplierManageConfig::find()->where(['supplier_code'=>$supplier_code])->one();
        if(empty($model)){
            $model = new SupplierManageConfig();
        }
        $haveProductLine = !empty($model->product_line_limit) ? explode(',',$model->product_line_limit) :[];
        $model->supplier_code_limit = explode(',',$model->supplier_code_limit);
        if(Yii::$app->request->isPost){
            $response = SupplierManageConfig::saveConfig(Yii::$app->request->getBodyParam('SupplierManageConfig'),$supplier_code);
            Yii::$app->getSession()->setFlash($response['status'],$response['message']);
            return $this->redirect(Yii::$app->request->referrer);
        }
        $productLineTreeDatas = Vhelper::getProductLineTreeDatas();
        return $this->renderAjax('quotes-config',['model'=>$model,'productLineTreeDatas'=>$productLineTreeDatas,'haveProductLine'=>$haveProductLine]);
    }
}