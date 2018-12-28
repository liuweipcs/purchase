<?php

namespace app\controllers;

use app\config\Vhelper;
use app\models\OperatLog;
use app\models\User;
use Yii;
use app\models\PurchaseUser;
use app\models\PurchaseUserSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PurchaseUserController implements the CRUD actions for PurchaseUser model.
 */
class PurchaseUserController extends BaseController
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
     * Lists all PurchaseUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PurchaseUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PurchaseUser model.
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
     * Creates a new PurchaseUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PurchaseUser();

        $post=Yii::$app->request->post('PurchaseUser');
        if(!empty($post)){
            $findone=PurchaseUser::find()->where(['pur_user_id'=>$post['pur_user_id']])->one();
            if($findone){
                Yii::$app->getSession()->setFlash('warning','此用户已存在！');
                return $this->redirect(['create']);
            }

            $model->pur_user_name=User::findOne($post['pur_user_id'])->username;
            $model->crate_time=time();
            $model->creator=Yii::$app->user->id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $data['type']=1;
            $data['pid']=$model->id;
            $data['module']='用户管理';
            $data['content']='新增数据：'.OperatLog::subLogstr($model).' 【成功】！';
            Vhelper::setOperatLog($data);
            return $this->redirect(['index',]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing PurchaseUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $post=Yii::$app->request->post('PurchaseUser');
        if(!empty($post)){
           /* $findone=PurchaseUser::find()->where(['pur_user_id'=>$post['pur_user_id']])->one();
            if($findone){
                Yii::$app->getSession()->setFlash('warning','此用户已存在！');
                return $this->redirect(['update','id'=>$model->id]);
            }

            $model->pur_user_name=User::findOne($post['pur_user_id'])->username;*/
            $model->edit_time=time();
            $model->editor=Yii::$app->user->id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $data['type']=2;
            $data['pid']=$model->id;
            $data['module']='用户管理';
            $data['content']='编辑数据：'.OperatLog::subLogstr($model).' 【成功】！';
            Vhelper::setOperatLog($data);

            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing PurchaseUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model=$this->findModel($id);
        $model->delete();
        $data['type']=3;
        $data['pid']=$model->id;
        $data['module']='用户管理';
        $data['content']='删除数据：'.OperatLog::subLogstr($model).' 【成功】！';
        Vhelper::setOperatLog($data);

        return $this->redirect(['index']);
    }

    /**
     * Finds the PurchaseUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PurchaseUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PurchaseUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
