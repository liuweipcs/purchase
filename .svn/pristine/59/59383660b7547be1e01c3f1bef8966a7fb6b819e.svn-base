<?php

namespace app\controllers;

use app\models\DeclareCustoms;
use Yii;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderItemsSearch;
use app\models\PurchaseTicketOpen;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CustomsTicketController implements the CRUD actions for PurchaseOrderItems model.
 */
class CustomsTicketController extends Controller
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
     * Lists all PurchaseOrderItems models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseOrderItemsSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search2($params);

        //采购员
        if(isset($params['PurchaseOrderItemsSearch']['buyer'])){
            $searchModel->buyer = $params['PurchaseOrderItemsSearch']['buyer'];
        }
        //发票编码
        if(isset($params['PurchaseOrderItemsSearch']['buyer'])){
            $searchModel->invoice_code = $params['PurchaseOrderItemsSearch']['invoice_code'];
        }    
        //开票日期
        if(isset($params['PurchaseOrderItemsSearch']['open_time'])){
            $searchModel->open_time = $params['PurchaseOrderItemsSearch']['open_time'];
        }
        //报关品名
        if(isset($params['PurchaseOrderItemsSearch']['buyer'])){
            $searchModel->declare_name = $params['PurchaseOrderItemsSearch']['declare_name'];
        }    
        //报关品名
        if(isset($params['PurchaseOrderItemsSearch']['buyer'])){
            $searchModel->custom_number = $params['PurchaseOrderItemsSearch']['custom_number'];
        }    

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'open_time' => isset($params['PurchaseOrderItemsSearch']['open_time'])?$params['PurchaseOrderItemsSearch']['open_time']:''
        ]);
    }
    /**
     * 更新开票
     */
    public function actionUpdateTicket()
    {
        if (Yii::$app->request->isGet) {
            $get = Yii::$app->request->get();
            $declare_model = DeclareCustoms::findOne(['key_id' => $get['key_id']]);// 查找对应的报关单信息，用来展示
            $model = PurchaseTicketOpen::find()
                ->where(['pur_number'=> $get['pur_number'], 'sku' => $get['sku'],'key_id' => $get['key_id']])
                ->one();
            if (empty($model)) $model = new PurchaseTicketOpen();

            return $this->renderAjax('update-ticket', ['model'=>$model,'get'=>$get,'declare_model' => $declare_model]);
        } else {
            $post = Yii::$app->request->post();
            $pur_number = $post['PurchaseTicketOpen']['pur_number'];
            $sku = $post['PurchaseTicketOpen']['sku'];
            $key_id = $post['PurchaseTicketOpen']['key_id'];
            $model = PurchaseTicketOpen::findOne(['pur_number'=> $pur_number, 'sku'=> $sku,'key_id' => $key_id]);
            
            if ($post['PurchaseTicketOpen']['status'] == 0) {
                $post['PurchaseTicketOpen']['create_user'] = Yii::$app->user->identity->username;
                $post['PurchaseTicketOpen']['create_time'] = date('Y-m-d H:i:s', time());
            }

            if (empty($model)) {
                $model_save = new PurchaseTicketOpen();
                
                if ($model_save->load($post) && $model_save->save()) {
                    $res = ['success', '新增成功'];
                } else {
                    $res = ['error', '修改失败'];
                }
            } else {
                
                if ($model->load($post) && $model->save()) {
                    PurchaseTicketOpen::updateAll(['audit_time'=>null],['pur_number'=> $pur_number, 'sku'=> $sku]);
                    $res = ['success', '修改成功'];
                } else {
                    $res = ['error', '修改失败'];
                }
            }

            Yii::$app->getSession()->setFlash($res[0],$res[1]);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }
    /**
     * 财务审批
     */
    public function actionFinancialAudit()
    {
        //financial-audit
        if (Yii::$app->request->isGet) {
            $get = Yii::$app->request->get();
            $model = PurchaseTicketOpen::find()
                ->where(['pur_number'=> $get['pur_number'], 'sku' => $get['sku'],'key_id' => $get['key_id']])
                ->one();
            if (empty($model)) $model = new PurchaseTicketOpen();
            return $this->renderAjax('financial-audit', ['model'=>$model,'get'=>$get]);
        } else {
            $post = Yii::$app->request->post();
            $model = PurchaseTicketOpen::findOne(['id'=> $post['PurchaseTicketOpen']['id']]);
            $model->audit_user = Yii::$app->user->identity->username;
            $model->audit_time = date('Y-m-d H:i:s', time());

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $res = ['success', '审核成功'];
            } else {
                $res = ['error', '审核失败'];
            }
            Yii::$app->getSession()->setFlash($res[0],$res[1]);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * Deletes an existing PurchaseOrderItems model.
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the PurchaseOrderItems model based on its primary key value.
     */
    protected function findModel($id)
    {
        if (($model = PurchaseTicketOpen::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
