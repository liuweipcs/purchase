<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\ProductDescription;
use app\models\ProductSearch;
use app\models\PurchaseOrderItems;
use app\services\CommonServices;
use app\services\PurchaseOrderServices;
use Yii;
use app\models\TablesChangeLog;
use app\models\SkuSingleTacticMainSearch;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\SkuSingleTacticMain;
use app\models\SkuSingleTacticMainContent;

/**
 * Created by PhpStorm.
 * 海外仓sku补货策略
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class OverseasWarehouseSkuReplenishmentController extends BaseController
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
     * Lists all PurchaseOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SkuSingleTacticMainSearch();
        $dataProvider = $searchModel->search1(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    /**
     * Displays a single SkuSingleTacticMain model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SkuSingleTacticMain model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SkuSingleTacticMain();
        $contentmodel = new SkuSingleTacticMainContent();

        if ($model->load(Yii::$app->request->post())) {

            $skumodel=$model;

            $skumodel->sku=$model->sku;
            $skumodel->warehouse=$model->warehouse;
            $skumodel->date_start=$model->date_start;
            $skumodel->date_end=$model->date_end;
            $skumodel->status=$model->status;
            $skumodel->user=Yii::$app->user->identity->username;
            $skumodel->create_date=date('Y-m-d H:i:s',time());

            $tran = Yii::$app->db->beginTransaction();
            try {
                $skusave=$skumodel->save(false);
                //表修改日志-新增
                $change_content = "insert:新增id值为{$skumodel->id}的记录";
                $change_data = [
                    'table_name' => 'pur_sku_single_tactic_main', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                if($skusave){
                    $contentmodel->supply_days=$model->supply_days;
                    $contentmodel->single_tactic_main_id=$skumodel->attributes['id'];
                    $contentmodel->status=$model->status;
                    $contsave=$contentmodel->save(false);
                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$contentmodel->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    if($contsave){
                        $tran->commit();

                        Yii::$app->session->setFlash('success','恭喜你,创建成功');
                        return $this->redirect(['index']);
                    }else{
                        Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
                    }
                }
                $tran->commit();

                return Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
            } catch (Exception $e) {
                $tran->rollBack();
                return Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
            }

        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SkuSingleTacticMain model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $contentmodel=SkuSingleTacticMainContent::findOne(['single_tactic_main_id'=>$id]);


        if ($model->load(Yii::$app->request->post())) {

            $skumodel=$model;

            $skumodel->sku=$model->sku;
            $skumodel->warehouse=$model->warehouse;
            $skumodel->date_start=$model->date_start;
            $skumodel->date_end=$model->date_end;
            $skumodel->status=$model->status;

            $tran = Yii::$app->db->beginTransaction();
            try {

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($skumodel->attributes, $skumodel->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_sku_single_tactic_main', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $skusave = $skumodel->save(false);

                if ($skusave) {
                    $contentmodel->supply_days = $model->supply_days;
                    $contentmodel->status = $model->status;

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($contentmodel->attributes, $contentmodel->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                    $contsave = $contentmodel->save(false);

                    $tran->commit();
                    if ($contsave) {
                        Yii::$app->session->setFlash('success', '恭喜你,操作成功');
                        return $this->redirect(['index']);
                    } else {
                        Yii::$app->session->setFlash('error', '我去！操作失败,请联系管理员:');
                    }
                }
                $tran->commit();
                Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
            } catch (Exception $e) {
                $tran->rollBack();
                Yii::$app->session->setFlash('error','我去！操作失败,请联系管理员:');
            }

        } else {
            $model->supply_days=$contentmodel->supply_days;

            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SkuSingleTacticMain model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {


        $tran = Yii::$app->db->beginTransaction();
        try {
            //表修改日志-删除
            $change_content = "delete:删除id值为{$id}的记录";
            $change_data = [
                'table_name' => 'pur_sku_single_tactic_main', //变动的表名称
                'change_type' => '3', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $this->findModel($id)->delete();

            //表修改日志-删除
            $change_content = "delete:删除single_tactic_main_id值为{$id}的记录";
            $change_data = [
                'table_name' => 'pur_sku_single_tactic_main_content', //变动的表名称
                'change_type' => '3', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            SkuSingleTacticMainContent::findOne(['single_tactic_main_id'=>$id])->delete();

        } catch (Exception $e) {
            $tran->rollBack();
            return $this->render(Yii::$app->request->referrer);
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the SkuSingleTacticMain model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SkuSingleTacticMain the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SkuSingleTacticMain::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


}
