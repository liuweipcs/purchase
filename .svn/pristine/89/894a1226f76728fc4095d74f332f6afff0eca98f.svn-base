<?php
namespace app\api\v1\controllers;
use app\config\Vhelper;
use app\models\User;
use linslin\yii2\curl\Curl;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/25
 * Time: 10:28
 */
class OaAccountController extends BaseController{
    //接口密钥
    const OA_API_SECRET = 'TIX2QqE26aa3';
    //OA 请求接口链接
    //测试地址
    //const OA_REQUEST_API = 'http://192.168.71.128:82';
    const OA_REQUEST_API = 'http://oa.yibainetwork.com';

    //轨迹系统对接OA系统对应的appid值
    const APPID = 3;


    public function actionGetChangeAccount(){
        $appid=self::APPID;
        $params = ['appid'=>$appid];
        $token=self::generateToken($params);
        $url= self::OA_REQUEST_API.'/services/account_api/getchangeaccount';
        $curl = new  Curl();
        $curl->setGetParams(['appid'=>$appid,'token'=>$token]);
        $response = $curl->get($url);
        if(Vhelper::is_json($response)&&!empty($response)){
            $responseData = json_decode($response,true);
            $successUpdate=[];
            if(isset($responseData['status'])&&$responseData['status']==1){
                if(isset($responseData['list'])&&is_array($responseData['list'])&&!empty($responseData['list'])){
                    foreach ($responseData['list']  as $key=>$value){
                        if(!isset($value['is_del'])||!isset($value['user_number'])){
                            continue;
                        }
                        if($value['is_del']==0){
                            if(empty($value['user_number'])||empty($value['login_name'])){
                                continue;
                            }
                            $user = User::find()->where(['user_number'=>$value['user_number']])->one();
                            if(empty($user)){
                                //用户名和员工编号都查询不到则新增用户
                                $user = new  User();
                                $user->username = isset($value['login_name'])&&!empty($value['login_name']) ? $value['login_name'] : '';
                                $user->alias_name = isset($value['user_name'])&&!empty($value['user_name']) ? $value['user_name'] : '';
                                $user->password_hash = \Yii::$app->security->generatePasswordHash('123456');
                                $user->user_number   = $value['user_number'];
                                $user->email         = 'info@yibainetwork.com';
                                $user->auth_key = \Yii::$app->security->generateRandomString();
                                $user->status=10;
                                if($user->save()){
                                    $successUpdate[]=$value['user_number'];
                                }
                            }else{
                                //员工编号查询到用户数据则更新状态
                                User::updateAll(['status'=>10],['user_number'=>$value['user_number']]);
                                $successUpdate[]=$value['user_number'];
                            }

                        }
                        if($value['is_del']==1){
                            if(empty($value['user_number'])){
                                continue;
                            }
                            User::updateAll(['status'=>0],['user_number'=>$value['user_number']]);
                            $successUpdate[]=$value['user_number'];
                        }
                    }
                }
            }
        }
        //推送更新结果
        self::pushSuccessData($successUpdate);
        \Yii::$app->end();
    }

    public static function pushSuccessData($successData){
        $appid = self::APPID;
        $getParams = ['appid'=>$appid,'id'=>json_encode($successData)];
        $token = self::generateToken($getParams);
        $url = self::OA_REQUEST_API.'/services/account_api/updatestatus';
        $curl = new Curl();
        $curl->setGetParams(['appid'=>$appid,'id'=>json_encode($successData),'token'=>$token]);
        $responseData = $curl->get($url);
    }

    /**
     * token生成
     * md5("a=a&b=b&c=c".API密钥)
     */
    protected static function generateToken($params)
    {
        ksort($params);
        reset($params);
        $token = self::createLinkstring($params) . self::OA_API_SECRET;

        return strtolower(md5($token));
    }

    /**
     * 将API数组参数转换为字符串a=a&b=b&c=c
     * @param $params API请求参数
     * @return string
     */
    protected static function createLinkstring($params)
    {
        $arg  = "";
        foreach($params as $key => $val) {
            $arg .= $key ."=". urlencode($val)."&";
        }

        $arg = substr($arg,0,count($arg) - 2);
        if(get_magic_quotes_gpc()){
            $arg = stripslashes($arg);
        }

        return $arg;
    }
}