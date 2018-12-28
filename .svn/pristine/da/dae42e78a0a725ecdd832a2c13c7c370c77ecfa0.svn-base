<?php

namespace app\controllers;

use Yii;
use app\models\PurchaseQc;
use app\models\PurchaseQcSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * PurchaseQcPutawayController implements the CRUD actions for PurchaseQc model.
 */
class PurchaseQcPutawayController extends Controller
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
     * Lists all PurchaseQc models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseQcSearch();
        $map=Yii::$app->request->queryParams;
        $map['PurchaseQcSearch']['qc_status']=ArrayHelper::getValue($map, 'PurchaseQcSearch.qc_status', '2');
        $dataProvider = $searchModel->search($map);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * @desc 查看详情
     * @author Jimmy
     * @date 2017-04-26 15:41:11
     */
    public function actionViewDetail(){
        $model=new PurchaseQc();
        $map['express_no']=Yii::$app->request->get('express_no');
        $map['pur_number']=Yii::$app->request->get('pur_number');
        $map['handle_type']=Yii::$app->request->get('handle_type')?Yii::$app->request->get('handle_type'):null;
        $data=$model->find()->where($map)->asArray()->all();
        return $this->renderAjax('view-detail', [
            'data' => $data,
        ]);
    }
    /**
     * @desc 收货异常 不良品上架审核
     * @author Jimmy
     * @date 2017-04-26 17:20:11
     */
    public function actionAuditDetail(){
        $model=new PurchaseQc();
        $map['express_no']=Yii::$app->request->get('express_no');
        $map['pur_number']=Yii::$app->request->get('pur_number');
        $map['handle_type']=Yii::$app->request->get('handle_type');
        $data=$model->find()->where($map)->asArray()->all();
        return $this->renderAjax('audit-detail', [
            'data' => $data,
        ]);
    }
    /**
     * @desc 保存异常审核
     * @author Jimmy
     * @date 2017-04-26 12:45:11
     */
    public function actionAuditSave(){
        $is_pass=Yii::$app->request->post('is_pass');
        if($is_pass==1){
            $this->pass();
        }else{
            $this->nopass();
        }
    }
    /**
     * @desc 审核通过
     * @return string 成功的信息提示
     * @author Jimmy
     * @date 2017-04-26
     */
    protected function pass(){
        $data=Yii::$app->request->post('PurchaseQcPutaway');
        $transaction=Yii::$app->db->beginTransaction();
        foreach ($data as $key=>$val){
            $model=new PurchaseQc();
            $map['id']=$key;
            $val['qc_status']='3';//已处理
            $val['auditor']=Yii::$app->user->identity->username;
            $val['time_audit']=date('Y-m-d h:i:s');
            if(false==$model->updateAll($val, $map)){
                $errors=$model->getFirstErrors();
                $str="</br>";
                foreach ($errors as $error){
                    $str.=$error."</br>";
                }
                Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员:'.$str,true);
                $transaction->rollBack();
                return $this->redirect(['index']);
            }
        }
        $transaction->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
        return $this->redirect(['index']);
    }
    /**
     * @desc 审核不通过
     * @author Jimmy
     * @date 2017-04-26 
     */
    protected function nopass(){
        $data=Yii::$app->request->post('PurchaseQcPutaway');
        $transaction=Yii::$app->db->beginTransaction();
        foreach ($data as $key=>$val){
            $model=new PurchaseQc();
            $map['id']=$key;
            $map['qc_status']='2';
            $val['handle_type']=null;
            $val['time_handle']=null;
            $val['handler']=null;
            $val['qc_status']='1';
            if(false==$model->updateAll($val, $map)){
                $errors=$model->getFirstErrors();
                $str="</br>";
                foreach ($errors as $error){
                    $str.=$error."</br>";
                }
                Yii::$app->getSession()->setFlash('error','我去！操作失败,请联系管理员:'.$str,true);
                $transaction->rollBack();
                return $this->redirect(['index']);
            }
        }
        $transaction->commit();
        Yii::$app->getSession()->setFlash('success',"恭喜你，操作成功！",true);
        return $this->redirect(['index']);
    }
}
