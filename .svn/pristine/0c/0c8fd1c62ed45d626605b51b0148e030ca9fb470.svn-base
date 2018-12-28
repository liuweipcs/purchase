<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\LockWarehouseConfig;
use app\models\LockWarehouseConfigSearch;
use app\models\PurchaseSuggest;
use Yii;
use yii\filters\VerbFilter;
/**
 * LockWarehouseConfigController implements the CRUD actions for LockWarehouseConfig model.
 */
class LockWarehouseConfigController extends BaseController
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
     * Lists all Warehouse models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LockWarehouseConfigSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 新增sku
     * DATE: 2018-8-31
     */
    public function actionCreate(){
        $return  = ['status'=>1 , 'msg'=>''];
        $sku = trim(Yii::$app->request->post('sku'));
        //验证sku是否是FBA虚拟仓   默认为FBA虚拟仓的sku
        $suggest_exist = PurchaseSuggest::find()->where(['sku'=>$sku,'warehouse_code'=>'FBA_SZ_AA'])->exists();
        if(!$suggest_exist){
            $return['status'] = 0;
            $return['msg'] = '该sku的仓库不是FBA虚拟仓，不能新增';
            die(json_encode($return));
        }
        //判断该sku是否已经新增
        $lock_exist = LockWarehouseConfig::find()->where(['sku'=>$sku])->exists();
        if($lock_exist){
            $return['status'] = 0;
            $return['msg'] = '该sku已存在,不能新增';
            die(json_encode($return));
        }

        $model_lock = new LockWarehouseConfig();
        $model_lock->sku = $sku;
        $model_lock->warehouse_code = 'FBA_SZ_AA';
        $model_lock->create_user = Yii::$app->user->identity->username;
        $model_lock->create_time = date('Y-m-d H:i:s');
        $res = $model_lock->save();
        if(!$res){
            $return['status'] = 0;
            $return['msg'] = '新增失败';
        }
        die(json_encode($return));
    }

    /**
     * 删除sku
     * DATE: 2018-8-31
     */
    public function actionDeleteSku(){
        $return  = ['status'=>1 , 'msg'=>''];

        $ids_arr = Yii::$app->request->post('ids');
        if($ids_arr){
            $model = new LockWarehouseConfig();
            $ids = join(',',$ids_arr);
            $condition = 'id in('.$ids.')';
            $res = $model->deleteAll($condition);
            if(!$res){
                $return['status'] = 0;
                $return['msg'] = '删除失败';
            }
        }else{
            $return['status'] = 0;
            $return['msg'] = '删除失败';
        }

        die(json_encode($return));
    }

    /**
     * 修改sku状态
     * DATE: 2018-8-31
     */
    public function actionChangeStatus(){
        $return  = ['status'=>1 , 'msg'=>''];

        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        if($id){
            $model = LockWarehouseConfig::find()->where(['id'=>$id])->one();
            if(!$model){
                $return['status'] = 0;
                $return['msg'] = '修改失败';
                die(json_encode($return));
            }
            $model->is_lock = $status;
            $res = $model->save();
            if(!$res){
                $return['status'] = 0;
                $return['msg'] = '修改失败';
            }
        }else{
            $return['status'] = 0;
            $return['msg'] = '修改失败';
        }

        die(json_encode($return));
    }

    /**
     * 采购需求导入
     * @return string|\yii\web\Response
     */
    public function actionImport(){
        $model = new LockWarehouseConfig();
        if (Yii::$app->request->isPost && $_FILES)
        {
            $extension=pathinfo($_FILES['LockWarehouseConfig']['name']['file_execl'], PATHINFO_EXTENSION);

            $filessize=$_FILES['LockWarehouseConfig']['size']['file_execl']/1024/1024;
            $filessize=round($filessize,2);

            if($filessize>10)
            {
                Yii::$app->getSession()->setFlash('warning',"文件大小不能超过 10M，当前大小： $filessize M",true);
                return $this->redirect(['index']);
            }


            if($extension!='csv')
            {
                Yii::$app->getSession()->setFlash('warning',"格式不正确,只接受 .csv 格式的文件",true);
                return $this->redirect(['index']);
            }
            $name= 'LockWarehouseConfig[file_execl]';
            $data = Vhelper::upload($name);

            if(empty($data))
            {
                Yii::$app->getSession()->setFlash('error',"文件上传失败",true);
                return $this->redirect(['index']);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $Name = [];
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }

                $suggest = PurchaseSuggest::find()->where(['sku'=>trim($datas[0])])->one();
                if(empty($suggest) || !in_array($suggest->warehouse_code,['FBA_SZ_AA'])){
                    Yii::$app->getSession()->setFlash('warning','导入的sku:' . $datas[0].'不存在或不属于FBA虚拟仓，不能新增',false);
                    return $this->redirect(['sales-index']);
                }

                $Name[$line_number][] = $datas[0];
                $Name[$line_number][] = 'FBA_SZ_AA';
                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s');
                $line_number++;
            }

            if(empty($Name))
            {
                Yii::$app->getSession()->setFlash('warning','导入有sku不存在或不属于FBA虚拟仓',false);
                return $this->redirect(['sales-index']);
            }


            //数据一次性入库
            $error_message='导入失败了';
            $transaction=\Yii::$app->db->beginTransaction();
            try{
                $statu= Yii::$app->db->createCommand()->batchInsert(LockWarehouseConfig::tableName(), ['sku', 'warehouse_code', 'create_user', 'create_time'], $Name)->execute();

                $transaction->commit();
            }catch (Exception $e){
                $statu =false;
                $error_message=$e->getMessage();
                $transaction->rollBack();
            }

            fclose($file);

            // die;
            if ($statu) {
                Yii::$app->getSession()->setFlash('success',"恭喜你，导入成功!",true);
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error',$error_message,true);
                return $this->redirect(['index']);
            }

        } else {
            return $this->renderAjax('import', ['model' => $model]);
        }
    }
}
