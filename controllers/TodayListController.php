<?php

namespace app\controllers;

use app\models\InventoryBadGoods;
use app\models\InventoryChukou1Au;
use app\models\InventoryChukou1De;
use app\models\InventoryChukou1Uk;
use app\models\InventoryChukou1Us;
use app\models\InventoryFba;
use app\models\InventoryGucangUse;
use app\models\InventoryGucangUsw;
use app\models\InventoryLazada;
use app\models\InventoryMaterial;
use app\models\InventoryNewemooDe;
use app\models\InventorySample;
use app\models\InventoryShTransfer;
use app\models\InventorySzTransfer;
use app\models\InventoryVirtual;
use app\models\InventoryWinitAu;
use app\models\InventoryWinitGb;
use app\models\InventoryWinitUkma;
use app\models\InventoryWinitUs;
use app\models\InventoryWinitUse;
use app\models\InventoryWinitUsw;
use app\models\InventoryYbUs;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\TodayListSearch;
use app\models\InventoryWinitDe;

class TodayListController extends BaseController
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
     * Lists all PurchaseAbnomal models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel  = new TodayListSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Displays a single PurchaseAbnomal model.
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
     * Creates a new PurchaseAbnomal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PurchaseAbnomal();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PurchaseAbnomal model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing PurchaseAbnomal model.
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
     * Finds the PurchaseAbnomal model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseAbnomal the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseAbnomal::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    public function actionBatchHandle(){
        $model         = new PurchaseAbnomal();
        $map['id']     = Yii::$app->request->post('ids');
        $map['status'] = 3;
        $res=$model->updateAll(['status'=>4], $map);
        if($res){
            Yii::$app->getSession()->setFlash('success',"恭喜，{$res}条数据操作成功！",true);
        }else{
            Yii::$app->getSession()->setFlash('error',"操作失败,请确认单据是否已处理！",true);
        }
        return $this->redirect(['index']);
    }
    /**
     * @desc 到货异常处理
     * @author Jimmy
     * @date 2017-04-25 14:10:11
     */
    public function actionHandle(){
        $model         = new PurchaseAbnomal();
        $model_carrier = new LogisticsCarrier();
        $map['id']     = Yii::$app->request->get('id');
        $map['status'] = 3;
        $data=$model->find()->where($map)->asArray()->one();
        $data_carrier=$model_carrier->find()->all();
        return $this->renderAjax('handle', [
            'data' => $data,
            'data_carrier'=>$data_carrier
        ]);
    }
    /**
     * @desc 保存异常处理
     * 1、往采购单物流信息表 插入一条数据信息
     * 2、标识该异常已经处理
     * @author Jimmy
     * @date 2017-04-25 16:20:11
     */
    public function actionHandleSave(){
        $model_ship     = new PurchaseOrderShip();
        $model_abnormal = new PurchaseAbnomal();
        $transaction    = Yii::$app->db->beginTransaction();
        if($model_ship->load(Yii::$app->request->post())&&$model_ship->save()){
            //标识已处理
            $map['id']     = Yii::$app->request->post('id');
            $map['status'] = 3;
            $model_abnormal->updateAll(['status'=>4,'note'=>Yii::$app->request->post()['PurchaseOrderShip']['note']],$map);
        }else{
            $errors=$model_ship->getFirstErrors();
            $str="</br>";
            foreach ($errors as $error){
                $str.=$error."</br>";
            }
            Yii::$app->getSession()->setFlash('error','操作失败:'.$str,true);
            $transaction->rollBack();
            return $this->redirect(['index']);
        }
        $transaction->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜，操作成功！",true);
        return $this->redirect(['index']);
    }

    /**
     * 读取通途采购单
     */
    public function actionImportCsv()
    {
        /*$path = Yii::$app->basePath.'/web/谷仓美国东仓.csv';
        $file = fopen($path, 'r');
        $line = -1;
        $data = [];
        while ($row = fgetcsv($file)) {
            if ($line < 0) {
                $line++;
                continue;
            }
            $fieldsTotal = count($row);
            for ($i=0; $i < $fieldsTotal; $i++) {
                $data[$line][]  = iconv('gbk//ignore', 'utf-8', trim($row[$i]));
            }
            $data[$line][]  = Yii::$app->user->identity->username;
            $data[$line][]  = date('Y-m-d H:i:s');
            $line++;
        }
        array_pop($data);array_pop($data);
        $params = ['goodsSku', 'goodsName', 'goodsAlias', 'warehouseName', 'location', 'goodsAvgCost'
            , 'availableStockQuantity', 'intransitStockQuantity', 'waitingShipmentStockQuantity', 'defectsStockQuantity'
            , 'totalWorth', 'headlineCost', 'safetyInventory', 'create_user', 'create_time'];
        $res= Yii::$app->db->createCommand()->batchInsert(InventoryGucangUse::tableName(), $params , $data)->execute();
        if($res) {
            echo '导入成功';
        } else {
            echo '导入失败';
        }
        fclose($file);*/
    }
}
