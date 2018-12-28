<?php

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use app\models\AlibabaAccount;
use app\models\AlibabaAccountSearch;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use linslin\yii2\curl;
/**
 * AlibabaAccountController implements the CRUD actions for AlibabaAccount model.
 */
class AlibabaAccountController extends Controller
{


    protected $url ='https://gw.api.alibaba.com/openapi/param2/1/system.oauth2/';



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
     * Lists all AlibabaAccount models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AlibabaAccountSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // 阿里巴巴开放平台授权
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if ($model->status == '2') {
            exit('停用状态无法使用授权');
        }

        $u = "https://auth.1688.com/oauth/authorize?client_id={$model->app_key}&site=1688&redirect_uri={$model['redirect_uri']}";

        return $this->redirect($u);
       
    }

    // 修改账户信息或授权后跳转到这里获取 access token
    public function actionUpdate($id, $code=null)
    {
        $model = $this->findModel($id);
        if (!empty($code))
        {
            $model->code = $code;
            $model->save();


            $res = $this->GetToken($model->id, $code, $model->redirect_uri);


            if($res)
            {
                Yii::$app->getSession()->setFlash('success', '恭喜您获取成功');
                return $this->redirect(['index']);
            }
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    // app 使用 code 换取 access_token
    protected function GetToken($account, $code, $url)
    {
        $model = $this->findModel($account);

        try {
            $appkey = $model->app_key;
            $secret_key = $model->secret_key;
            $redirect_uri = $url;
// curl 访问https需要证书
            $_url ="https://gw.open.1688.com/openapi/http/1/system.oauth2/getToken/{$appkey}?grant_type=authorization_code&need_refresh_token=true&client_id={$appkey}&client_secret={$secret_key}&redirect_uri={$redirect_uri}&code={$code}";


            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $_url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);

            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查

            $s = curl_exec($curl);

            curl_close($curl);

            $responses = json_decode($s, 1);



            if ($responses)
            {
                $model->access_token          = $responses['access_token'];
                $model->refresh_token         = $responses['refresh_token'];
                $model->expires_in            = $responses['expires_in'];
                $model->refresh_token_timeout = $responses['refresh_token_timeout'];
                $flag = $model->save();
            } else {
                $flag = false;
            }




        } catch (Exception $e) {
            $flag = false;
        }
        return $flag;
    }

    // 创建账号
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new AlibabaAccount();
        if($request->isPost) {
            $data = Yii::$app->request->post();
            $model->load($data);
            if($model->validate()) {
                $model->save();
                Yii::$app->getSession()->setFlash('success', '恭喜您，添加成功');
                return $this->redirect(['index']);
            } else {
                Yii::$app->getSession()->setFlash('error', '对不起，添加失败');
                return $this->redirect(['index']);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }



    /**
     * Deletes an existing AlibabaAccount model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AlibabaAccount model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AlibabaAccount the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AlibabaAccount::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    // http://caigou.yibainetwork.com/alibaba-account/bind-user?id=191&uid=0
   public function actionBindUser()
   {
       $uid = Yii::$app->request->get('uid');
       $id = Yii::$app->request->get('id');
       if($id <= 0) {
           exit;
       }
       $model = AlibabaAccount::findOne($id);
       $model->bind_account = $uid;
       $res = $model->save(false);
       if($res) {
           echo 'SUCCESS';
       } else {
           echo 'FAILURE';
       }

   }



}
