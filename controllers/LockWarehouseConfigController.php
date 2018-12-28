<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\LockWarehouseConfig;
use app\models\LockWarehouseConfigSearch;
use app\models\PurchaseSuggest;
use app\models\Warehouse;
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

        return $this->renderAjax('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'msg' => ''
        ]);
    }

    /**
     * 新增sku
     * DATE: 2018-8-31
     */
    public function actionCreate(){
        //获取sku和仓库code
        $sku = trim(Yii::$app->request->post('sku'));
        $warehouse_code = trim(Yii::$app->request->post('warehouse_code'));

        $model_lock = new LockWarehouseConfig();
        $searchModel = new LockWarehouseConfigSearch();
        $dataProvider = $searchModel->search([]);
        $msg = '';
        if($sku && $warehouse_code) {
            //判断仓库是否是FBA虚拟仓/执御虚拟仓/海外虚拟仓/FBL虚拟仓/易佰东莞仓的
            if(!in_array($warehouse_code,['FBA_SZ_AA','ZDXNC','HW_XNC','LAZADA-XNC','SZ_AA'])){
                $msg = "新增失败:当前只能选择 (东莞仓FBA虚拟仓/执御虚拟仓/海外虚拟仓/FBL虚拟仓/易佰东莞仓库)!";
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }
            //判断该sku是否已经新增
            $lock_exist = LockWarehouseConfig::find()->where(['sku' => $sku,'warehouse_code'=>$warehouse_code])->exists();
            if ($lock_exist) {
                $msg = "新增失败:该sku已存在相同的记录,不能新增!".$sku.'-'.$warehouse_code;
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }

            $model_lock->sku            = $sku;
            $model_lock->warehouse_code = $warehouse_code;
            $model_lock->create_user    = Yii::$app->user->identity->username;
            $model_lock->create_time    = date('Y-m-d H:i:s');
            $res                        = $model_lock->save();
            if ($res) {
                $msg = "恭喜你，新增成功!";
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            } else {
                $msg = "对不起，新增失败!";
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }
        }else{
            return $this->renderAjax('create', ['model' => $model_lock]);
        }
    }

    /**
     * 删除sku
     * DATE: 2018-8-31
     */
    public function actionDeleteSku(){
        $return  = ['status'=>1 , 'msg'=>''];

        $ids_arr = Yii::$app->request->post('ids');
        if($ids_arr){
            LockWarehouseConfig::updateAll(['update_user'=>Yii::$app->user->identity->username,'update_time'=>date('Y-m-d H:i:s',time()),'is_lock'=>0],['in','id',$ids_arr]);
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
            $model->update_user = Yii::$app->user->identity->username;
            $model->update_time = date('Y-m-d H:i:s',time());
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

            $model_lock = new LockWarehouseConfig();
            $searchModel = new LockWarehouseConfigSearch();
            $dataProvider = $searchModel->search([]);
            $msg = '';
            if($filessize>10)
            {
                $msg = "文件大小不能超过 10M，当前大小： $filessize M";
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }


            if($extension!='csv')
            {
                $msg = "格式不正确,只接受 .csv 格式的文件";
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }
            $name= 'LockWarehouseConfig[file_execl]';
            $data = Vhelper::upload($name);

            if(empty($data))
            {
                $msg = "文件上传失败";
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }
            $file        = fopen($data, 'r');
            $line_number = 0;
            $Name = [];
            while ($datas = fgetcsv($file)) {
                if ($line_number == 0) { //跳过表头
                    $line_number++;
                    continue;
                }
                $sku = mb_convert_encoding(trim($datas[0]),'utf-8','gbk');
                //判断仓库是否存在
                $warehouse_name = mb_convert_encoding(trim($datas[1]),'utf-8','gbk');
                $lockStatus  = mb_convert_encoding(isset($datas[2]) ? trim($datas[2]):'是','utf-8','gbk');
                $warehouse = Warehouse::find()->where(['warehouse_name'=>$warehouse_name])->one();
                if(empty($warehouse)){
                    $msg = '导入的sku:' . $datas[0].'的仓库不存在，请检查';
                    return $this->renderAjax('index', [
                        'model' => $model_lock,
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'msg' => $msg
                    ]);
                }
                //判断仓库是否是FBA虚拟仓的
                if(!in_array($warehouse->warehouse_code,['FBA_SZ_AA','ZDXNC','HW_XNC','LAZADA-XNC','SZ_AA'])){
                    $msg = '导入的sku:' . $datas[0].'-'.$datas[1].'的仓库不在(东莞仓FBA虚拟仓/执御虚拟仓/海外虚拟仓/FBL虚拟仓/易佰东莞仓库)中，不能导入';
                    return $this->renderAjax('index', [
                        'model' => $model_lock,
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                        'msg' => $msg
                    ]);
                }
                $lockStatus = $lockStatus=='否' ? 0 : 1;

                $Name[$line_number][] = $sku;
                $Name[$line_number][] = $warehouse->warehouse_code;
                $Name[$line_number][] = $lockStatus;
                $Name[$line_number][] = Yii::$app->user->identity->username;
                $Name[$line_number][] = date('Y-m-d H:i:s');
                $line_number++;
            }

            if(empty($Name))
            {
                $msg = '导入有sku不存在或不属于(东莞仓FBA虚拟仓/执御虚拟仓/海外虚拟仓/FBL虚拟仓/易佰东莞仓库)';
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }
            //数据一次性入库
            $error_message='导入失败了';

            $transaction=\Yii::$app->db->beginTransaction();
            $statu=true;
            try{
                foreach ($Name as $value){
                    $exist = LockWarehouseConfig::find()->where(['sku'=>$value[0],'warehouse_code'=>$value[1]])->exists();
                    if($exist){
                        LockWarehouseConfig::updateAll(['is_lock'=>$value[2],'update_user'=>Yii::$app->user->identity->username,'update_time'=>date('Y-m-d H:i:s',time())],
                            ['sku'=>$value[0],'warehouse_code'=>$value[1]]);
                    }else{
                        $insertArray[]=$value;
                    }
                }
                if(!empty($insertArray)) {
                    $statu = Yii::$app->db->createCommand()->batchInsert(LockWarehouseConfig::tableName(), ['sku', 'warehouse_code', 'is_lock','create_user', 'create_time'], $insertArray)->execute();
                }
                $transaction->commit();
            }catch (\Exception $e){
                $statu =false;
                $error_message=$e->getMessage();
                $transaction->rollBack();
            }

            fclose($file);

            if ($statu) {
                $msg = "恭喜你，导入成功!";
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            } else {
                $msg = $error_message;
                return $this->renderAjax('index', [
                    'model' => $model_lock,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'msg' => $msg
                ]);
            }

        } else {
            return $this->renderAjax('import', ['model' => $model]);
        }
    }
}
