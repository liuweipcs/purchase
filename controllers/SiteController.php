<?php

namespace app\controllers;

use app\config\Vhelper;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ChangePassword;
use yii\web\Controller;
use linslin\yii2\curl\Curl;
use app\models\User;
use app\models\PurchaseWarehouseAbnormal;
use app\models\InformMessage;
use yii\web\HttpException;

/**
 *                             _ooOoo_
 *                            o8888888o
 *                            88" . "88
 *                            (| -_- |)
 *                            O\  =  /O
 *                         ____/`---'\____
 *                       .'  \\|     |//  `.
 *                      /  \\|||  :  |||//  \
 *                     /  _||||| -:- |||||-  \
 *                     |   | \\\  -  /// |   |
 *                     | \_|  ''\---/''  |   |
 *                     \  .-\__  `-`  ___/-. /
 *                   ___`. .'  /--.--\  `. . __
 *                ."" '<  `.___\_<|>_/___.'  >'"".
 *               | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *               \  \ `-.   \_ __\ /__ _/   .-` /  /
 *          ======`-.____`-.___\_____/___.-`____.-'======
 *                             `=---='
 *          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *                     佛祖保佑        永无BUG
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
*/
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'index';


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','index','login','change-password'],
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout','index','change-password','login'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
//            'error' => [
//                'class' => 'yii\web\ErrorAction',
//            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     * 登录首页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionError(){
        $error = Yii::$app->errorHandler->exception;
        if($error){
            $message = 'error_file:'.$error->getFile().PHP_EOL.'line:'.$error->getLine().PHP_EOL.'message:'.$error->getMessage().PHP_EOL.$error->xdebug_message;
            $model = Yii::$app->db->createCommand()->insert('pur_log',
                [
                    'log_time'=>time(),
                    'prefix'=>Yii::$app->user->isGuest ? 0 : Yii::$app->user->id,
                    'message'=>$message
                ]
                )->execute();
        }
        return $this->render('error',['name'=>'报错了！','message'=>'请联系技术处理']);
    }

    /**
     * Login action.
     *  登录
     * @return string
     */
    public function actionLogin()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *  退出
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect('login');
    }

    public function actionCheck(){
        $user_name=Yii::$app->request->getBodyParam('user_name');
        if ($user_name){
            $data = User::find()->where('username=:user_name',[':user_name'=>$user_name])->one();
            if(!empty($data)){
                $josnData = array(
                    'msg'=>'通过!',
                    'status'=>2
                );
//                if($data->is_intranet==0){
//                    $josnData = array(
//                        'msg'=>'你的采购帐号不允许在公司以外的网络登录，如有需要，请联系上级主管开通外网登录权限。',
//                        'status'=>1
//                    );
//                }else {
//                    $josnData = array(
//                        'msg'=>'通过!',
//                        'status'=>2
//                    );
//                }
            }else {
                $josnData = array(
                    'msg'=>'账号不存在!!',
                    'status'=>3
                );
            }
        }
        echo json_encode($josnData);exit;

    }
    /**
     * 清理缓存
     */
    public function actionClear()
    {
        $clear = Yii::$app->cache->flush();
        if ($clear)
        {
            Yii::$app->getSession()->setFlash('success', '清理成功');
        } else {
            Yii::$app->getSession()->setFlash('error', '我去！清理失败了');
        }
        return $this->render('clear');
    }
    /**
     * Reset password
     * @return string
     */
    public function actionChangePassword()
    {
        $model = new ChangePassword();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
            Yii::$app->user->logout();
            return $this->redirect('login');
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }
    public function actionChangePasswordBytoken(){
        $this->layout='main-login';
        $username = Yii::$app->request->getQueryParam('username');
        if(empty($username)){
            exit('非法请求');
        }
        $model = User::find()->where(['username'=>$username])->one();
        if(Yii::$app->request->isPost){
            try{
                $form = Yii::$app->request->getBodyParam('User');
                if(empty($form['oldPassword'])||!Yii::$app->security->validatePassword($form['oldPassword'],$model->password_hash)){
                    throw new HttpException('原密码错误');
                }
                $login = User::loginByErpToken($model->username);
                if($login){
                    $model->password_hash = Yii::$app->security->generatePasswordHash($form['newPassword']);
                    $model->auth_key      = Yii::$app->security->generateRandomString();
                    if($model->save()==false){
                        throw new HttpException(500,'密码修改失败');
                    }
                }
                Yii::$app->user->logout();
                return $this->redirect('login');
            }catch (HttpException $e){
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        return $this->render('change-password-token',['model'=>$model]);
    }
    public function actionLoginByToken(){
        $token = $_GET['token'];
        $curl = new Curl();
        $result = $curl->setGetParams([
            'token'=>$token
        ])->get(Yii::$app->params['CAIGOU_URL'].'/v1/login-verif/check-token');
        $result = json_decode($result);
        if(!empty($result->success)){
            if($result->data->user_name == 'admin'){
                exit();
            }
            if(User::loginByErpToken($result->data->user_name)){
                return $this->redirect('index');
            }else{
                return $this->redirect('login');
            }
        }
    }

    public function actionChecktoken(){
    	$token = $_GET['token'];
    	$curl = new Curl();
    	$result = $curl->setGetParams([
    			'token'=>$token,
    			'platform'=>'purchase'
    	])->get(Yii::$app->params['ERP_URL'].'/services/api/system/index/method/checktoken');
    	$result = json_decode($result);
    	if(!empty($result->success)){
            if($result->data->user_name == 'admin'){
                exit();
            }
    		if(User::loginByErpToken($result->data->user_name)){
    			return $this->redirect('index');
    		}else{
    			return $this->redirect('login');
    		}
    	}
    }

    // 消息提醒
    public function actionMessages()
    {
        if(Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('tab');
            if(isset($data['PurchaseWarehouseAbnormal'])) {
                PurchaseWarehouseAbnormal::updateAll(['is_reading' => 1], ['in', 'id', $data['PurchaseWarehouseAbnormal']]);
            }
            if(isset($data['InformMessage'])) {
                InformMessage::updateAll(['status' => 1], ['in', 'id', $data['InformMessage']]);
            }
            return true;
        } else {
            $username = Yii::$app->user->identity->username;
            $num = PurchaseWarehouseAbnormal::find()
                ->select(['id', 'abnormal_type', 'defective_id'])
                ->where(['buyer' => $username, 'is_handler' => 0, 'is_reading' => 0])
                ->orderBy('id desc')
                ->asArray()
                ->all();

            $msg = ["你好，<strong style='color: red'>{$username}</strong>，系统检测到你有以下异常信息待处理："];
            $tab = [];
            if(!empty($num)) {

                $a = [
                    1 => [],
                    2 => [],
                    3 => []
                ];
                $ids = [];
                foreach($num as $v) {
                    if($v['abnormal_type'] == 1) {
                        $a[1][] = $v;
                    }
                    if($v['abnormal_type'] == 2) {
                        $a[2][] = $v;
                    }
                    if($v['abnormal_type'] == 3) {
                        $a[3][] = $v;
                    }
                    $ids[] = $v['id'];
                }
                $tab['PurchaseWarehouseAbnormal'] = $ids;
                if(!empty($a[1])) {
                    $msg[] = "查找入库单：".count($a[1])." 条，最新异常单号为：<strong style='color: red'>".$a[1][0]['defective_id']."</strong>";
                }
                if(!empty($a[2])) {
                    $msg[] = "入库有次品：".count($a[2])." 条，最新异常单号为：<strong style='color: red'>".$a[2][0]['defective_id']."</strong>";
                }
                if(!empty($a[3])) {
                    $msg[] = "质检不合格：".count($a[3])." 条，最新异常单号为：<strong style='color: red'>".$a[3][0]['defective_id']."</strong>";
                }
            }
            $b = InformMessage::find()
                ->select(['id', 'pur_number'])
                ->where(['inform_user' => $username, 'status' => 0])
                ->asArray()
                ->orderBy('id desc')
                ->all();
            if(count($b) > 0) {
                $msg[] = "仓库点货少数量：".count($b)." 条，最新采购单号为：<strong style='color: red'>".$b[0]['pur_number']."</strong>";
                $tab['InformMessage'] = array_column($b, 'id');
            }
            if(count($msg) > 1) {
                $data = ['msg' => $msg, 'tab' => $tab];
                return json_encode($data);
            } else {
                return '';
            }
        }
    }

}
