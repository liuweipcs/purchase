<?php
namespace app\api\v1\controllers;

use app\models\User;
use Yii;
use app\models\LoginForm;


class LoginVerifController extends BaseController {
    public function actionCheckLogin(){
        $userName = Yii::$app->request->getQueryParam('username');
        $password = Yii::$app->request->getQueryParam('password');
        $model = new LoginForm();
        $model->password = $password;
        $model->username = $userName;
        $host = Yii::$app->request->getQueryParam('host');
        $user = User::find()->where(['username'=>$userName])->one();
        if ($user&&$model->login()&&Yii::$app->security->validatePassword($password, $user->password_hash)) {
            $pattern = "/(?=.*\d)(?=.*[a-zA-Z])(?=.*[^a-zA-Z0-9]).{7,30}/";
            $token = md5($user->id.$userName.date('Y-m-d H:i:s').$password);
            if(!preg_match($pattern, $password)){
                echo json_encode(['ack'=>true,'token'=>$token,'message'=>'密码太简单！','url'=>'http://'.$host.'/site/change-password-bytoken?username='.$userName]);
                Yii::$app->end();
            }
            $insert = Yii::$app->db->createCommand()->update(User::tableName(),['access_token'=>$token],['username'=>$userName])->execute();
            if($insert){
                $result=['ack'=>true,'token'=>$token,'message'=>'登录成功','url'=>'http://'.$host.'/site/login-by-token?token='.$token];
                echo json_encode($result);
                Yii::$app->end();
            }
        }
        echo json_encode(['ack'=>false,'message'=>'登录失败']);
        Yii::$app->end();
    }
    public function actionCheckToken(){
        $token = Yii::$app->request->getQueryParam('token');
        if($token&&!empty($token)){
            $user = User::find()->where(['access_token'=>$token])->one();
            if($user){
                $response = ['success'=>true,'data'=>['user_name'=>$user->username]];
                echo json_encode($response);
                Yii::$app->end();
            }
        }
        echo json_encode(['success'=>false]);
        Yii::$app->end();
    }
}