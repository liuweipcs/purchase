<?php
namespace app\controllers;

use app\models\Product;
use app\models\ProductRepackage;
use app\models\ProductRepackageSearch;
use Yii;
use yii\filters\VerbFilter;

/**
 * FBA 二次包装列表 控制器
 * Class FbaProductRepackageController
 * @package app\controllers
 */
class FbaProductRepackageController extends BaseController
{
    const SKU_TYPE = 3;// FBA

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProductRepackageSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        $data = [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ];
        return $this->render('index', $data);
    }


    /**
     * 创建SKU 二次包装记录
     * @return string|\yii\web\Response
     */
    public function actionCreateData()
    {
        $model = new ProductRepackage();

        if (Yii::$app->request->isPost)
        {
            $data       = Yii::$app->request->post()['ProductRepackage'];
            $skuStr     = $data['sku'];
            if(empty($skuStr)){
                Yii::$app->getSession()->setFlash('warning','无数据');
                return $this->redirect(['index']);
            }


            $exists_list = $success_list = $no_sku_list = [];
            $search = array('，'," ","　"," ","\n","\r","\t");
            $replace = array(',',",",",",",",",",",",",");

            $skuStr = str_replace($search,$replace ,$skuStr);

            if(strpos($skuStr,',') !== false){
                $skuArr = explode(',',$skuStr);
                $skuArr = array_unique($skuArr);
                $skuArr = array_diff($skuArr,['']);
            }else{
                $skuArr = array($skuStr);
            }

            foreach($skuArr as $sku){
                $sku = trim($sku);
                $result = ProductRepackage::addSkuInfo($sku,self::SKU_TYPE);
                if($result['status'] == 'success'){
                    $success_list[] = $sku;
                }elseif($result['status'] == 'exists'){
                    $exists_list[] = $sku;
                }elseif($result['status'] == 'none'){
                    $no_sku_list[] = $sku;
                }
            }
            $no_sku_message = empty($no_sku_list)?"":'，不存在的SKU['.implode(',',$no_sku_list).']';

            if(count($success_list) > 0 AND count($exists_list) == 0){
                Yii::$app->getSession()->setFlash('success','恭喜你,添加成功'.$no_sku_message);
            }elseif(count($success_list) > 0 AND count($exists_list) > 0){
                Yii::$app->getSession()->setFlash('success','添加成功，部分SKU已存在['.implode(',',$exists_list).']'.$no_sku_message);
            }elseif(count($exists_list) > 0){
                Yii::$app->getSession()->setFlash('warning','SKU已存在['.implode(',',$exists_list).']'.$no_sku_message);
            }else{
                Yii::$app->getSession()->setFlash('error','抱歉，导入失败'.$no_sku_message);
            }

            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('create',['model' =>$model]);
        }
    }


    /**
     * 审核 二次包装记录
     * @return \yii\web\Response
     */
    public function actionAudit(){
        $data         = Yii::$app->request->get();
        $ids          = $data['ids'];
        $audit_status = $data['audit_status'];// 1.审核通过 2,审核不通过


        foreach($ids as $id){
            $model = ProductRepackage::findOne($id);
            if(!isset($model->audit_status) OR $model->audit_status == 1) continue;// 没记录或已审核的不再操作

            $model->audit_status = $audit_status;
            $res = $model->save(false);
            if($res){
                $productModel = Product::findOne(['sku' => $model->sku]);
                $productModel->is_repackage = 1;
                $productModel->save(false);
            }
        }

        Yii::$app->getSession()->setFlash('success','恭喜你,审核成功');
        return $this->redirect(Yii::$app->request->referrer);

    }


    /**
     * 删除 二次包装记录
     * @return \yii\web\Response
     */
    public function actionDelete(){
        $data         = Yii::$app->request->get();
        $ids          = $data['ids'];

        foreach($ids as $id){
            $model = ProductRepackage::findOne($id);
            $model->status = 2;// 设置状态为 已删除
            $res = $model->save(false);
            if($res){
                $productModel = Product::findOne(['sku' => $model->sku]);
                $productModel->is_repackage = 0;
                $productModel->save(false);
            }
        }

        Yii::$app->getSession()->setFlash('success','恭喜你,删除成功');
        return $this->redirect(Yii::$app->request->referrer);

    }


    /**
     * 导入 二次包装记录
     * @return string|\yii\web\Response
     */
    public function actionImport(){
        set_time_limit(0);
        ini_set('memory_limit','512M');

        $model = new ProductRepackage();
        if(Yii::$app->request->isPost AND $_FILES){
            $files      = $_FILES['ProductRepackage'];
            $file_name  = $files['name']['file_execl'];
            $tmp_name   = $files['tmp_name']['file_execl'];

            $fileExp = explode('.', $file_name);
            $fileExp = strtolower($fileExp[count($fileExp) - 1]);//文件后缀

            if ($fileExp != 'xls' AND $fileExp != 'xlsx' ) {
                Yii::$app->getSession()->setFlash('error','只能导入EXCEL文件');
                return $this->redirect(Yii::$app->request->referrer);
            }

            if ($fileExp == 'xls') $PHPReader = new \PHPExcel_Reader_Excel5();
            if ($fileExp == 'xlsx') $PHPReader = new \PHPExcel_Reader_Excel2007();

            // 文件保存路径
            $path       = Yii::$app->basePath.'/web/files/product-repackage/';
            $filePath   = $path . date('YmdHis') . '.' . $fileExp;

            if (move_uploaded_file($tmp_name, $filePath)) {

                $PHPReader      = $PHPReader->load($filePath);
                $currentSheet   = $PHPReader->getSheet(0);
                $totalRows      = $currentSheet->getHighestRow();

                //设置的上传文件存放路径
                $sheetData  = $currentSheet->toArray(null,true,true,true);

                $exists_list = $success_list = $no_sku_list = [];

                if($sheetData){
                    foreach($sheetData as $key => $data_value){
                        if($key == 1) continue;
                        
                        $sku    = $data_value['A'];
                        $result = ProductRepackage::addSkuInfo($sku,self::SKU_TYPE);
                        if($result['status'] == 'success'){
                            $success_list[] = $sku;
                        }elseif($result['status'] == 'exists'){
                            $exists_list[] = $sku;
                        }elseif($result['status'] == 'none'){
                            $no_sku_list[] = $sku;
                        }
                    }
                }

                $no_sku_message = empty($no_sku_list)?"":'，不存在的SKU['.implode(',',$no_sku_list).']';

                if(count($success_list) > 0 AND count($exists_list) == 0){
                    Yii::$app->getSession()->setFlash('success','恭喜你,添加成功'.$no_sku_message);
                }elseif(count($success_list) > 0 AND count($exists_list) > 0){
                    Yii::$app->getSession()->setFlash('success','添加成功，部分SKU已存在['.implode(',',$exists_list).']'.$no_sku_message);
                }elseif(count($exists_list) > 0){
                    Yii::$app->getSession()->setFlash('warning','SKU已存在['.implode(',',$exists_list).']'.$no_sku_message);
                }else{
                    Yii::$app->getSession()->setFlash('error','抱歉，导入失败'.$no_sku_message);
                }

            }else{
                Yii::$app->getSession()->setFlash('error','文件上传失败');
            }
            return $this->redirect(Yii::$app->request->referrer);

        }else{
            return $this->renderAjax('import',['model' =>$model]);
        }

    }
}
