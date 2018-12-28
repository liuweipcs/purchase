<?php

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use app\models\Warehouse;
use app\models\WarehouseSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\WarehousePurchaseTactics;
use app\models\WarehouseMin;
/**
 * WarehouseController implements the CRUD actions for Warehouse model.
 */
class WarehouseController extends BaseController
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
        $searchModel = new WarehouseSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Warehouse model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Warehouse model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Warehouse();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {


            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Warehouse model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = Warehouse::findOne(['id'=>$id]);
        $model->modify_user_id  = Yii::$app->user->identity->username;
        $model->modify_time = date('Y-m-d H:i:s');
        if ($model->load(Yii::$app->request->post()) && $model->save(false))
        {
            Yii::$app->getSession()->setFlash('success','恭喜你！修改成功！',true);
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Warehouse model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Warehouse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Warehouse the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Warehouse::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    /**
     * @desc 仓库补货策略
     * @author Jimmy
     * @date 2017-04-08 09:20:11
     */
    public function actionWarehouseCreate(){
        $model = new Warehouse();
        if (Yii::$app->request->isGet||Yii::$app->request->isAjax) {
            $map['`pur_warehouse`.id']=Yii::$app->request->get('ids');
            $pattern=$model->find()->select('pattern')->where($map)->scalar();
            if($pattern=='def'){
                $data = $model->find()->joinWith('warehousePurchaseTactics')->asArray()->where($map)->one();
            }elseif($pattern=='min'){
                $data = $model->find()->joinWith('warehouseMin')->asArray()->where($map)->one();
            }else{
                $data = $model->find()->asArray()->where($map)->one();
            }
            return $this->renderAjax('warehouse-create', ['model' => $model,'data'=>$data]);
        }elseif(Yii::$app->request->isPost){
            $data=Yii::$app->request->post('data');
            $transactions=Yii::$app->db->beginTransaction();
            if($data['pattern']=='def'){
                $this->updateDef($data);
            }elseif ($data['pattern']=='min') {
                $this->updateMin($data);
            }
            $transactions->commit();
            Yii::$app->getSession()->setFlash('success','操作成功！',true);
            return $this->redirect(['index']);
        }
    }
    /**
     * @desc 更新仓库的最小模式
     * @author Jimmy
     * @date 2017-04-10 11:46:11
     */
    protected function updateMin($data){

        $model = new Warehouse();
        //主表用来更新
        $map['warehouse_code']=$data['warehouse_code'];
        $ids=$model->find()->where($map)->one();
        $ids->pattern=$data['pattern'];
        $ids->save(false);
        //子表如果之前没有就插入，有就更新
        $model_min = WarehouseMin::find()->where($map)->one();
        if(!$model_min)
        {

            $model =new WarehouseMin;
            $model->warehouse_code = $data['warehouse_code'];
            $model->days_safe =$data['warehouseMin']['days_safe'];
            $model->days_min  =$data['warehouseMin']['days_min'];
            $model->days_safe_transfer  =$data['warehouseMin']['days_safe_transfer'];
            $model->save(false);
            $days_safe  = 0 . '->' . $data['warehouseMin']['days_safe'].';';
            $days_safe .= 0 . '->' . $data['warehouseMin']['days_min'].';';
            $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username .'=====' .'对id:'.$model->attributes['id'].'========'.$days_safe.'进行了插入';
            $datas['type']    = 9;
            $datas['pid']     = $model->attributes['id'];
            $datas['module']  = '仓库最小补货模式';
            $datas['content'] = $msg;
            Vhelper::setOperatLog($datas);
        } else{
            $days_safe = $model_min->days_safe . '->' . $data['warehouseMin']['days_safe'] . ';';
            $days_safe .= $model_min->days_min . '->' . $data['warehouseMin']['days_min'] . ';';
            $days_safe .= $model_min->days_safe_transfer . '->' . $data['warehouseMin']['days_safe_transfer'] . ';';
            $msg             = '在' . date('Y-m-d H:i:s') . '由' . Yii::$app->user->identity->username . '对id:' . $model_min->id . '=====' .$days_safe. '进行了更新';
            $datas['type']    = 9;
            $datas['pid']     = $model_min->id;
            $datas['module']  = '仓库最小补货模式';
            $datas['content'] = $msg;
            Vhelper::setOperatLog($datas);
            $model_min->days_safe      = $data['warehouseMin']['days_safe'];
            $model_min->days_min       = $data['warehouseMin']['days_min'];
            $model_min->days_safe_transfer       = $data['warehouseMin']['days_safe_transfer'];
            $model_min->save(false);
        }

    }
    /**
     * @desc 更新仓库的默认模式
     * 1、主表用来更新
     * 2、子表如果之前没有就插入，有就更新
     * @author Jimmy
     * @date 2017-04-10 11:47:11
     */
    protected function updateDef($data){
        $model = new Warehouse();
        //主表用来更新
        $map['warehouse_code']=$data['warehouse_code'];
        $ids=$model->find()->where($map)->one();
        $ids->pattern=$data['pattern'];
        $ids->save(false);
        //先删后增
        $model_purchase=new WarehousePurchaseTactics();
        $model_purchase->deleteAll($map);
        $data['warehousePurchaseTactics']=$this->changeData($data['warehousePurchaseTactics']);
        foreach ($data['warehousePurchaseTactics'] as $key=> $val) {
            $data['warehousePurchaseTactics'][$key]['warehouse_code']=$data['warehouse_code'];
        }
        Yii::$app->db->createCommand()
                ->batchInsert($model_purchase::tableName(),['type','days_product','days_logistics','days_safe_stock','days_frequency_purchase','warehouse_code'],$data['warehousePurchaseTactics'])
                ->execute();
    }

    /**
     * @desc 添加仓库
     * @author Herman
     * @date 2017-06-17
     */
    public function actionWarehouseAdd(){
        $model = new Warehouse();
        if ( Yii::$app->request->isGet || Yii::$app->request->isAjax )
        {

            return $this->renderAjax('warehouse_add', ['model' => $model]);
        } elseif($model->load(Yii::$app->request->post())) {
            $model->create_user_id  = Yii::$app->user->identity->username;
            $model->create_time = date('Y-m-d H:i:s');
            $model->save(false);
            Yii::$app->getSession()->setFlash('success','恭喜你!仓库新增成功',true);
            return $this->redirect(['index']);
        }
    }
}
