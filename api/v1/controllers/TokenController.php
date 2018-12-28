<?php
namespace app\api\v1\controllers;


class TokenController extends BaseController {

    public function actionGetToken() {
        $url = 'http://tkk.yibainetwork.com/token.php?callback=http://caigou.yibainetwork.com/v1/token/save-token?token={token}';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    public function actionSaveToken(){
        $token = \Yii::$app->request->getQueryParam('token');
        if($_SERVER['REMOTE_ADDR']!='120.78.180.49'){
            exit(json_encode(array(
                'error'=>-1,
                'message'=>'访问被禁止'
            )));
        }
        if(!empty($token)){
            \Yii::$app->db->createCommand()->insert(\app\api\v1\models\Token::tableName(),['token'=>$token,'create_time'=>time()])->execute();
        }
        exit(json_encode(array(
            'error'=>0
        )));
    }
}