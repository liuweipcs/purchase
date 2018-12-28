<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\PurchaseAbnormals;
use Yii;
use app\models\PurchaseAbnomal;
use app\models\PurchaseAbnomalSearch;
use app\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\LogisticsCarrier;
use app\models\PurchaseOrderShip;
/**
 * PurchaseAbnomalController implements the CRUD actions for PurchaseAbnomal model.
 */
class PurchaseAbnomalController extends BaseController
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
        $searchModel  = new PurchaseAbnomalSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
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
    protected function actionBatchHandle(){
        $model         = new PurchaseAbnomal();
        $map['id']     = Yii::$app->request->post('ids');
        $map['status'] = 3;
        $res=$model->updateAll(['status'=>4], $map);
        if($res){
            Yii::$app->getSession()->setFlash('success',"恭喜，{$res}条数据操作成功！",true);
        }else{
            Yii::$app->getSession()->setFlash('error',"我去!,操作失败,请确认单据是否已处理！",true);
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
    public function actionHandleSave()
    {
        $model_ship     = new PurchaseOrderShip();
        $model_abnormal = new PurchaseAbnomal();
        $transaction    = Yii::$app->db->beginTransaction();
        $data           = Yii::$app->request->post()['PurchaseOrderShip'];
        $ship           = $model_ship->find()->where(['express_no' =>$data['express_no'],'pur_number'=>$data['pur_number']])->one();
        //存在物流信息,不做任何添加,只处理PO异常和消息状态。否则添加物流信息,修改PO异常和消息状态
        if ($ship)
        {
            //标识已处理
            $map['id']     = Yii::$app->request->post('id');
            $map['status'] = 3;
            $maps['status'] =4;
            $maps['handle_note'] =$data['note'];
            $maps['handle_id']=Yii::$app->user->identity->username;
            $maps['handle_time']=date('Y-m-d H:i:s',time());
            $model_abnormal->updateAll($maps,$map);
            $express_no = PurchaseAbnomal::find()->select('express_no')->where(['id'=>$map['id']])->scalar();
            PurchaseAbnormals::UpdateOne($express_no);
        } else {
            $model_ship->load(Yii::$app->request->post());
            $model_ship->save(false);
            //标识已处理
            $map['id']     = Yii::$app->request->post('id');
            $map['status'] = 3;
            $maps['status'] =4;
            $maps['status'] =4;
            $maps['handle_note'] =$data['note'];
            $maps['handle_id']=Yii::$app->user->identity->username;
            $maps['handle_time']=date('Y-m-d H:i:s',time());
            $model_abnormal->updateAll($maps,$map);
            $express_no = PurchaseAbnomal::find()->select('express_no')->where(['id'=>$map['id']])->scalar();
            PurchaseAbnormals::UpdateOne($express_no);
        }
        $transaction->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
        return $this->redirect(['index']);
    }
}
