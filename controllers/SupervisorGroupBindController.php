<?php

namespace app\controllers;

use app\models\User;
use app\services\BaseServices;
use Yii;
use app\models\SupervisorGroupBind;
use app\models\SupervisorGroupBindSearch;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SupervisorGroupBindController implements the CRUD actions for SupervisorGroupBind model.
 */
class SupervisorGroupBindController extends BaseController
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
     * Lists all SupervisorGroupBind models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SupervisorGroupBindSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SupervisorGroupBind model.
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
     * Creates a new SupervisorGroupBind model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SupervisorGroupBind();
        if(Yii::$app->request->isPost){
            try{
                $post=Yii::$app->request->post('SupervisorGroupBind');
                if(empty($post['supervisor_name']) || empty($post['group_id'])){
                    throw new HttpException(500,'缺少必要参数');
                }
                $userGroup = SupervisorGroupBind::find()->where(['supervisor_name'=>trim($post['supervisor_name']),'group_id'=>$post['group_id']])->one();
                if(!empty($userGroup)){
                    throw new HttpException(500,'该用户已在该分组了');
                }
                $groupId = BaseServices::getGroupByUserName(2);
                $role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
                if(!in_array($post['group_id'],$groupId) && !isset($role['超级管理员组'])){
                    throw new HttpException(500,'该用户无权分配该分组组员');
                }
                $user = User::find()->where(['username'=>$post['supervisor_name']])->one();
                $model->creator_id      =   Yii::$app->user->id;
                $model->supervisor_id   =   !empty($user) ? $user->id : 0;
                $model->supervisor_name =   trim($post['supervisor_name']);
                $model->group_id        =   $post['group_id'];
                $model->creator_name    =   Yii::$app->user->identity->username;
                $model->create_time     =   time();
                if($model->save() == false){
                    throw new HttpException(500,'销售名称添加失败！');
                }
                return $this->redirect(['index']);
            }catch(HttpException $e){
                Yii::$app->getSession()->setFlash('error',$e->getMessage());
                return $this->redirect(['create','model'=>$model]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SupervisorGroupBind model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $post=Yii::$app->request->post('SupervisorGroupBind');
        if(!empty($post)){
            $findone=SupervisorGroupBind::find()->where(['supervisor_name'=>trim($post['supervisor_name']),'group_id'=>$post['group_id']])->one();
            if($findone && $findone->id !=$id){
                Yii::$app->getSession()->setFlash('warning','此销售名称已存在！');
                return $this->redirect(['update','id'=>$model->id]);
            }
            $model->supervisor_name=$post['supervisor_name'];
            $model->group_id       =$post['group_id'];
            $model->editor_name    =Yii::$app->user->identity->username;
            $model->edit_time      =time();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SupervisorGroupBind model.
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
     * Finds the SupervisorGroupBind model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return SupervisorGroupBind the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupervisorGroupBind::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
