<?php
namespace app\controllers;

use app\models\SupplierLog;
use Yii;
use app\models\Supplier;
use app\models\SupplierSearch;
use yii\filters\VerbFilter;

/**
 * Created by PhpStorm.
 * User: jolon
 * Date: 2018/11/15
 * Time: 18:41
 */
class SupplierSpecialController extends BaseController
{
    /**
     * @inheritdoc
     */
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
     * Lists all Stockin models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search2(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 删除 二次包装记录
     * @return \yii\web\Response
     */
    public function actionBatchDelete(){
        $data         = Yii::$app->request->get();
        $ids          = $data['ids'];
        foreach($ids as $id){
            $model = Supplier::findOne($id);
            $model->supplier_special_flag = 0;
            $res = $model->save(false);
            if($res){
                SupplierLog::saveSupplierLog('supplier-special/batch-delete','删除跨境宝',false,$model->supplier_name,$model->supplier_code);
            }
        }

        Yii::$app->getSession()->setFlash('success','恭喜你,删除成功');
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 导入 跨境宝
     */
    public function actionImport(){
        set_time_limit(0);
        ini_set('memory_limit','512M');

        $model = new SupplierSearch();
        if(Yii::$app->request->isPost AND $_FILES){
            $files      = $_FILES['SupplierSearch'];
            $file_name  = $files['name']['source'];
            $tmp_name   = $files['tmp_name']['source'];

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
                //设置的上传文件存放路径
                $sheetData  = $currentSheet->toArray(null,true,true,true);

                $error_list = $exists_list = $success_list = $no_su_list = [];// 已存在 成功 SKU不存在 个数

                if($sheetData){
                    foreach($sheetData as $key => $data_value){
                        $supplier_name   = trim($data_value['A']);
                        if($key == 1 OR empty($supplier_name)) continue;
                        //$supplier = Supplier::findOne(['supplier_name' => $supplier_name]);

                        $supplierList = Supplier::find()->where(['supplier_name' => $supplier_name])->all();
                        if(empty($supplierList)){
                            $no_su_list[] = $supplier_name;
                            continue;
                        }
                        foreach($supplierList as $supplier){
                            $supplier->supplier_special_flag = 1;
                            $supplier->special_flag_user = Yii::$app->user->identity->username;
                            $supplier->special_flag_time = date('Y-m-d H:i:s');
                            $res = $supplier->save(false);
                            if($res){
                                SupplierLog::saveSupplierLog('supplier-special/import','添加跨境宝',false,$supplier->supplier_name,$supplier->supplier_code);
                                $success_list[] = $supplier_name;
                            }else{
                                $error_list[] = $supplier_name;
                            }
                        }
                    }
                }
                $no_su_message      = empty($no_su_list)?"":'，不存在的供应商【'.implode(',',$no_su_list).'】';
                $exists_su_message  = empty($exists_list)?"":'，已是跨境商【'.implode(',',$exists_list).'】';
                $error_su_message   = empty($error_list)?"":'，添加失败【'.implode(',',$error_list).'】';
                $all_message        = trim($no_su_message.$exists_su_message.$error_su_message,'，');

                if(count($success_list) > 0 AND empty($all_message)){
                    Yii::$app->getSession()->setFlash('success','恭喜你,导入成功');
                }elseif(count($success_list) > 0 AND $all_message ){
                    Yii::$app->getSession()->setFlash('warning','部分导入成功<br/>'.$all_message);
                }else{
                    Yii::$app->getSession()->setFlash('error','抱歉，导入失败<br/>'.$all_message);
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
