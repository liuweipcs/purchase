<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\MemberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use linslin\yii2\curl;
use yii\helpers\Json;
use app\models\TableFields;

/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class MemberController extends BaseController
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
                    'delete' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * 关闭Csrf
     */
    public function init(){
        $this->enableCsrfValidation = false;
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {

        $model = new User();
        if (Yii::$app->request->get('id'))
        {
            $model ->client_id = Yii::$app->request->get('id');
        }
        if ($model->load(Yii::$app->request->post())) {
          // $result = $this->checkUserNumber($model->user_number, $model->username);
            $result['status']=1;
           if($result['status'] == 0) {

               exit($result['errorMess']);

           } else {

               $model->password_hash=Yii::$app->security->generatePasswordHash(Yii::$app->request->post()['User']['password_hash']);
               $model->auth_key=Yii::$app->security->generateRandomString();
               //$model->password_reset_token=Yii::$app->security->generateRandomString().'_'.time();
               if($model->save()){
                   return $this->redirect(['index']);
               }





           }



        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            //$model->password_hash=Yii::$app->security->generatePasswordHash(Yii::$app->request->post()['User']['password_hash']);
            //$model->password_reset_token=Yii::$app->security->generateRandomString().'_'.time();
            $model->save(false);
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {   if($id==Yii::$app->params['admin'])
    {
        \Yii::$app->session->setFlash('error',Yii::t('app','无法删除的用户'));
        return $this->redirect(['index']);
    } else {
        $this->findModel($id)->delete();
    }


        return $this->redirect(['index']);
    }
    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function checkUserNumber($a, $b)
    {



        $url = 'http://120.78.243.154/services/api/system/index/method/getUserOnlyMarking';


        $curl = new curl\Curl();
        $s = $curl->setGetParams([
            'name' => $b,
            'code' => $a,
        ])->get($url);

        return Json::decode($s);
    }

    public function actionResetPassword($id){
        if(Yii::$app->request->isAjax){
            $model = User::find()->where(['id'=>$id])->one();
            if(empty($model)){
                echo json_encode(['status'=>'error','message'=>'用户不存在']);
                Yii::$app->end();
            }
            $model->password_hash=Yii::$app->security->generatePasswordHash('YB'.date('Ymd'));
            if($model->save()==false){
                echo json_encode(['status'=>'error','message'=>'密码重置失败']);
                Yii::$app->end();
            }
            echo json_encode(['status'=>'error','message'=>'密码重置成功，新密码为：YB'.date('Ymd')]);
            Yii::$app->end();
        }
    }
    
    public function actionUpdateTableFields()
    {
        $table = Yii::$app->request->post('table');
        $userid = Yii::$app->user->identity->getId();
        if (Yii::$app->request->isAjax) {
            $fields = TableFields::find()->where(['userid'=>$userid,'table_name'=>$table])->select('data')->scalar();
            $fields = $fields ? json_decode($fields, true) : array_keys($_POST);
            
            return $this->renderAjax('update-table-fields',['data'=>$_POST,'fields'=>$fields]);
        } else {
            
            $fields = Yii::$app->request->post('fields');
            $fields = array_filter($fields);
            asort($fields);
            $fields = array_keys($fields);
            $fields = $fields ? json_encode($fields) : '';
            
            $model = TableFields::find()->where(['userid'=>$userid,'table_name'=>$table])->one();
            if (!$model) {
                $model = new TableFields();
            }
            $model->data = $fields;
            $model->update_time = date('Y-m-d H:i:s');
            $model->userid = $userid;
            $model->table_name = $table;
            $model->save(false);
            
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    /**
     * 编辑 报表字段是否显示和顺序
     * @return string|\yii\web\Response
     */
    public function actionUpdateTableFieldsShow(){
        $data = Yii::$app->request->post();
        $table = $data['table'];
        if(isset($data['user_id']) and $data['user_id']){
            $userid = isset($data['user_id'])?$data['user_id']:'';
        }else{
            $userid = isset(Yii::$app->user->identity)?Yii::$app->user->identity->getId():0;
        }

        if (Yii::$app->request->isAjax) {
            unset($data['user_id'],$data['user'],$data['user_number']);
            $all_fields = $data;

            $fields = TableFields::find()->where(['userid'=>$userid,'table_name'=>$table])->select('data')->scalar();
            $fields = $fields ? json_decode($fields, true) : array_keys($all_fields);

            return $this->renderAjax('update-table-fields-show',['data'=>$data,'fields'=>$fields,'user_id' => $userid]);
        } else {
            $fields = $data;

            $fields_sort = $fields['fields_sort'];
            $fields_show = $fields['fields_show'];
             asort($fields_sort);

            $data_fields = [];
            foreach($fields_sort as $key_field => $value_field){
                $data_fields[$key_field] = ['sort' => $value_field,'show' => isset($fields_show[$key_field])?$fields_show[$key_field]:0 ];
            }
            $data_fields = $data_fields ? json_encode($data_fields) : '';

            $model = TableFields::find()->where(['userid' => $userid, 'table_name' => $table])->one();
            if(!$model){
                $model = new TableFields();
            }
            $model->data        = $data_fields;
            $model->update_time = date('Y-m-d H:i:s');
            $model->userid      = $userid;
            $model->table_name  = $table;
            $model->save(false);

            Yii::$app->getSession()->setFlash('success','修改成功');
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

}
