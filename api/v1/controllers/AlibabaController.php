<?php

namespace app\api\v1\controllers;
use app\api\v1\models\AliOrderBaseinfo;
use app\api\v1\models\AliOrderLog;
use app\api\v1\models\AliOrderLogisticsInfo;
use app\api\v1\models\AliOrderProductItems;
use app\api\v1\models\PurchaseOrder;
use app\models\AlibabaAccount;
use app\api\v1\models\PurchaseOrderOrders;
use app\api\v1\models\PurchaseOrderShip;
use app\models\PurchaseOrderPayType;
use app\models\User;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;


/**
 * 拉取1688物流信息
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class AlibabaController extends BaseController
{

    protected $url ='https://gw.api.alibaba.com/openapi/param2/1/system.oauth2/';
    protected $_posturl ='http://gw.open.1688.com/openapi/';
    /**
     * 获取到token 定时任务为8小时请求一次
     */
    public function actionGetToken()
    {


        set_time_limit(3600);
        if (Yii::$app->request->get('account'))
        {

            $this->_result('getToken/');

        } else {
            $url = Yii::$app->request->hostInfo.Yii::$app->request->getUrl();
            $this->_results($url);

        }

    }

    /**
     * 换取新的refreshToken 此是有效为半年,可以定时1个月触发一次
     */
    public function actionRefreshToken()
    {

        set_time_limit(3600);
        if (Yii::$app->request->get('account'))
        {
            $this->_result_r('postponeToken/');

        } else {
            $url = Yii::$app->request->hostInfo.Yii::$app->request->getUrl();
            $this->_results($url);

        }

    }

    protected function  _result_r($action)
    {
        $accounts       = Yii::$app->request->get('account');
        $account        = AlibabaAccount::find()->where(['id'=>$accounts,'status'=>1])->one();
        //判断是否为空
        if(empty($account))
        {
            Vhelper::ToMail('貌似有帐号被限制了','少年采购系统有点小问题哦！');
            exit('貌似有帐号被限制了,少年采购系统有点小问题哦！');
        }
        $jsonDate       = $account->refresh_token_timeout;
        preg_match('/\d{14}/',$jsonDate,$matches);
        $day            = isset($matches[0]) ? (strtotime($matches[0]) - time())/86400 : 0;
        if ($day < 30)
        {
            $client_id      = urlencode($account->app_key);
            $client_secret  = urlencode($account->secret_key);
            $refresh_token  = urlencode($account->refresh_token);
            $access_token   = urlencode($account->access_token);
            $curl           = new curl\Curl();
            $s              = $curl->setGetParams([
                'access_token'  => $access_token,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
            ])->post($this->url.$action.$account['app_key']);
            $s              = Json::decode($s);
            if ($s)
            {
                /*获取失败则把账号状态改成token过期状态*/
                if(empty($s['refresh_token']))
                {
                    $account->status =1;
                    $account->save();
                }else{
                    $account->refresh_token = $s['refresh_token'];
                    $account->access_token  = $s['access_token'];
                    $account->save();
                }
            } else {
                echo '保存失败';
            }
        } else {
            exit('还没到达1688限定的时间');
        }


    }

    protected  function  _result($action)
    {
        $accounts       = Yii::$app->request->get('account');
        $account        = AlibabaAccount::find()->where(['id'=>$accounts,'status'=>1])->one();
        //判断是否为空
        if(empty($account))
        {
            Vhelper::ToMail('貌似有帐号被限制了','少年采购系统有点小问题哦！');
            exit('貌似有帐号被限制了,少年采购系统有点小问题哦！');
        }
        $client_id      = urlencode($account->app_key);
        $client_secret  = urlencode($account->secret_key);
        $refresh_token  = urlencode($account->refresh_token);
        $curl           = new curl\Curl();
        $s              = $curl->setPostParams([
            'grant_type'    =>'refresh_token',
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
        ])->post($this->url.$action.$account['app_key']);
        $s              = Json::decode($s);
        if ($s)
        {
            /*获取失败则把账号状态改成token过期状态*/
            if(empty($s['access_token']))
            {
                $account->status =1;
                $account->save();
            }else{
                $account->access_token  = $s['access_token'];
                $account->save();
            }
        } else {
            echo '保存失败';
        }
    }

    protected  function _results($url)
    {
        $AliAccounts = AlibabaAccount::find()->where(['status'=>1])->asArray()->all();

        if (!empty($AliAccounts))
        {
            foreach ($AliAccounts as $id=>$val)
            {

                $_url = $url;
                $curl = new curl\Curl();
                $curl->setGetParams(['account'=>$val['id']])->get($_url);


            }
        } else {
            die('there are no any account!');
        }
    }

    /**
     * 拉取物流编号

     */
    public function  actionGetLogistics()
    {
        set_time_limit(3600);
        $id= Yii::$app->request->get('account');
        $pur_number= Yii::$app->request->get('pur_number');
        $page  = (int)Yii::$app->request->get('page', 0);
        $limit = (int)Yii::$app->request->get('limit', 500);

        if(!empty($id))
        {
//            $order = PurchaseOrderOrders::find()->joinWith(['orders'])->where(['is_request'=>0,'create_id'=>$id])->asArray()->limit(500)->all();
            $buyer = User::find()->select('username')->where(['id'=>$id])->asArray()->one()['username'];
            $where = ['is_request'=>0,'buyer'=>$buyer];
            if (!empty($pur_number)) {
                $where['pur_purchase_order_pay_type.pur_number'] = $pur_number;
            }
            $order = PurchaseOrderPayType::find()->offset($page*$limit)->joinWith(['orders'])->where($where)->andWhere(['not',['platform_order_number'=>null]])->asArray()->all();
//            $order = PurchaseOrderPayType::find()->joinWith(['orders'])->where($where)->andWhere(['not',['platform_order_number'=>null]])->limit(500)->asArray()->all();

//            echo 1 . "--{$buyer}\r";
            $res = $this->_pullLogistics($order);
            /*if ($res == -1) {
                exit('没有要拉取的物流');
            }*/
        } else {
            //方法一：
            $count     = AlibabaAccount::find()->where(['status'=>1])->count();
            $mout      = abs($count - date('i'));
            $account = AlibabaAccount::find()->select('bind_account')->offset($mout)->limit(1)->asArray()->orderBy('id desc')->scalar();
            $buyer = User::find()->select('username')->where(['id'=>$account])->asArray()->one()['username'];
            $order = PurchaseOrderPayType::find()->joinWith(['orders'])->where(['is_request'=>0,'buyer'=>$buyer])->limit(200)->asArray()->all();
            $res = $this->_pullLogistics($order);
            $buyer_info = [];
            $buyer_info[] = $buyer;
            $buyer_info[] = $mout;

            //方法二：
            /*$accounts = AlibabaAccount::find()->select('bind_account')->where(['status'=>1])->asArray()->all();
            $accounts = !empty($accounts) ? Vhelper::changeData($accounts) : '';
            if (empty($accounts)) {
                exit('无用户');
            }
            $accounts = $accounts['bind_account'];

            $buyer_info = [];
            foreach ($accounts as $v) {
                $buyer = User::find()->select('username')->where(['id'=>$v])->asArray()->one()['username'];
                $order = PurchaseOrderPayType::find()->joinWith(['orders'])->where(['is_request'=>0,'buyer'=>$buyer])->andWhere(['not',['platform_order_number'=>null]])->asArray()->all();

                foreach ($order as $ok => $ov) {
                    $order_ship_info = PurchaseOrderShip::find()->where(['pur_number'=>$ov['pur_number']])->one();
                    if (!empty($order_ship_info)) {
                        unset($order[$ok]);
                        continue;
                    }
                }
                $res = $this->_pullLogistics($order);
                $buyer_info[] = $buyer;
            }*/
Vhelper::dump($buyer_info);
//            $buyer_json = json_encode($buyer_info);
//            echo $buyer_json;
//            exit($buyer_json);
        }


    }

    /**
     * 物流信息处理
     */
    protected function _pullLogistics($order)
    {
        $parm  ='param2/1/com.alibaba.logistics/alibaba.trade.getLogisticsInfos.buyerView/';
        $url   = $this->_posturl.$parm;
        if($order) {
            foreach ($order as $v) {       //如果拍单号长度不够直接跳出
                $arr = ['pur_number'=>$v['pur_number'],'platform_order_number'=>!empty($v['platform_order_number']) ? $v['platform_order_number']:''];
//                echo Json::encode($arr);

                if (strlen(trim($v['platform_order_number'])) >= 17 && is_numeric(trim($v['platform_order_number']))) {
                    $id = User::find()->select('id')->where(['username'=>$v['orders']['buyer']])->asArray()->one()['id'];
                    $acc = AlibabaAccount::find()->where(['bind_account' => $id])->one();

                    //如果找不到与之对应的帐号也跳出
                    if ($acc) {
                        $curl   = new curl\Curl();
                        $ars    = [
                            'orderId' => trim($v['platform_order_number']),
                            'webSite' => '1688',
                        ];

                        $reuslt = $curl->setPostParams([
                            '_aop_signature' => Vhelper::Signatures($acc->app_key, $acc->secret_key, $parm, $ars),
                            'orderId'        => trim($v['platform_order_number']),
                            'webSite'        => '1688',
                        ])->post($url . $acc->app_key);

                        $reuslt = Json::decode($reuslt);

                        if (empty($reuslt['result'])) {
                            continue;
                        }


                        $status = $this->_getLogistics($reuslt, $v['pur_number']);

//                        echo Json::encode($v['pur_number']) . "--{$status}\r";

                        //物流信息没有修改的话也跳出
                        if ($status) {

                            //修改这个状态表示已请求过了
//                            $mod             = PurchaseOrderOrders::find()->where(['pur_number' => $v['pur_number']])->one();
                            $mod = PurchaseOrderPayType::find()->where(['pur_number' => $v['pur_number']])->one();

                            $mod->is_request = 1;
                            $mod->save();
                        } else {
                            continue;
                        }
                    } else {
                        continue;
                    }
                } else {
                    continue;
                    //exit('无法获取么token,失去必要的签证');
                }

            }
            return true;
        } else{
            return -1;
        }
    }


    /**
     * 保存物流信息
     * @param $data
     * @param $pur_number
     * @return bool
     */
    protected  function  _getLogistics($data,$pur_number)
    {

        if(!empty($data['result']))
        {
            foreach($data as $v)
            {
                foreach($v as $d)
                {
                    $model = PurchaseOrderShip::find()->where(['pur_number'=>$pur_number])->one();
                    if($model)
                    {

                        $model->express_no       = isset($d['logisticsBillNo'])?$d['logisticsBillNo']:$d['logisticsId'];
                        $model->cargo_company_id = isset($d['logisticsCompanyName'])?$d['logisticsCompanyName']:"自动送货";
                        $model->purchase_type    = Vhelper::getNumber($pur_number);
                        $model->create_user_id   = !empty($model->create_user_id)?$model->create_user_id:1;
                        $model->create_time      = !empty($model->create_time)?$model->create_time:date('Y-m-d H:i:s');
                        return $model->save(false);
                    } else {
                        $ship                   = new  PurchaseOrderShip();
                        $ship->pur_number       = $pur_number;
                        $ship->express_no       = isset($d['logisticsBillNo'])?$d['logisticsBillNo']:$d['logisticsId'];
                        $ship->cargo_company_id = isset($d['logisticsCompanyName'])?$d['logisticsCompanyName']:"自动送货";
                        $ship->purchase_type    = Vhelper::getNumber($pur_number);
                        $ship->create_user_id   = 1;
                        $ship->create_time      = date('Y-m-d H:i:s');
                        return $ship->save(false);
                    }

                }
            }
        } else{

            return false;
        }
    }

    //筛选条件并抓取订单信息
    public function actionGetOrderInfo(){
//        if(time()<strtotime(date('Y-m-d 21:00:00',time()))){
//            exit();
//        }
        set_time_limit(240);
        $orders = PurchaseOrderPayType::find()
            ->alias('pt')
            ->select('pt.id,pt.platform_order_number,pt.pur_number,pt.purchase_acccount')
            ->leftJoin(PurchaseOrder::tableName().' o','pt.pur_number=o.pur_number')
            ->where(['pt.is_success'=>0])
            ->andWhere('pt.check_date<:time or isnull(pt.check_date)',[':time'=>date('Y-m-d H:i:s',time()-7200)])
            ->andWhere(['in','o.purchas_status',[3,5,6,7,8,9,10]])
            ->andWhere("pt.platform_order_number IS NOT  NULL AND pt.platform_order_number !=''")
            ->andWhere("pt.purchase_acccount IS NOT NULL AND pt.purchase_acccount !='' AND pt.purchase_acccount !='0'")
            ->limit(200)
            ->orderBy('pt.check_date ASC')
            ->asArray()
            ->all();
        $this->_pullOrderInfo($orders);
    }

    //抓取订单具体方法
    public function _pullOrderInfo($order){
        $parm  ='param2/1/com.alibaba.trade/alibaba.trade.get.buyerView/';
        $url   = $this->_posturl.$parm;
        if($order) {
            foreach ($order as $v) {//如果拍单号长度不够直接跳出
                if (strlen(trim($v['platform_order_number'])) >= 17 && is_numeric(trim($v['platform_order_number']))&&!empty($v['purchase_acccount'])) {
                    $acc = AlibabaAccount::find()->where(['account' => $v['purchase_acccount']])->one();
                    //如果找不到与之对应的帐号也跳出
                    if ($acc) {
                        $curl   = new curl\Curl();
                        $ars    = [
                            'orderId' => trim($v['platform_order_number']),
                            'webSite' => '1688',
                            'access_token'=> $acc->access_token,
                        ];
                        $reuslt = $curl->setPostParams([
                            '_aop_signature' => Vhelper::Signatures($acc->app_key, $acc->secret_key, $parm, $ars),
                            'orderId'        => trim($v['platform_order_number']),
                            'access_token'        => $acc->access_token,
                            'webSite'        => '1688',
                        ])->post($url . $acc->app_key);
                        $reuslt = Json::decode($reuslt);
                        if(!empty($reuslt)&&isset($reuslt['result'])){
                            $tran = Yii::$app->db->beginTransaction();
                            try{
                                if(isset($reuslt['result']['nativeLogistics'])){
                                   $saveResponse = AliOrderLogisticsInfo::saveData($v['pur_number'],$v['platform_order_number'],$reuslt['result']['nativeLogistics']);
                                   if($saveResponse==false){
                                       throw new yii\db\Exception('物流信息插入失败');
                                   }
                                }
                                if(isset($reuslt['result']['baseInfo'])){
                                    $baseSaveResponse = AliOrderBaseinfo::saveData($v['pur_number'],$v['platform_order_number'],$reuslt['result']['baseInfo']);
                                    if($baseSaveResponse==false){
                                        throw new yii\db\Exception('订单基本信息保存失败');
                                    }
                                }
                                if(isset($reuslt['result']['productItems'])){
                                    $productSaveResponse=AliOrderProductItems::saveData($v['pur_number'],$v['platform_order_number'],$reuslt['result']['productItems']);
                                    if($productSaveResponse==false){
                                        throw new yii\db\Exception('订单产品详情信息保存失败');
                                    }
                                }
                                $tran->commit();
                            }catch (yii\db\Exception $e){
                                $tran->rollBack();
                                Yii::$app->db->createCommand()
                                    ->insert(AliOrderLog::tableName(),['pur_number'=>$v['pur_number'],
                                        'order_number'=>$v['platform_order_number'],
                                        'error_code'=>'saveError',
                                        'error_message'=>$e->getMessage().'--'.$v['purchase_acccount'].'--'.$v['id'],
                                        'create_date'=>date('Y-m-d H:i:s',time()),
                                        'status'=>'error'])->execute();
                                Yii::$app->db->createCommand()->update(PurchaseOrderPayType::tableName(),
                                    ['is_success'=>1,'check_date'=>date('Y-m-d H:i:s',time())],
                                    ['id'=>$v['id'],'is_success'=>0])->execute();
                            }
                        }else{
                            $code = !isset($reuslt['error_code']) ? isset($reuslt['errorCode']) ? $reuslt['errorCode'] :json_encode($reuslt) : $reuslt['error_code'];
                            $message = !isset($reuslt['error_message']) ? isset($reuslt['errorMessage']) ? $reuslt['errorMessage'] :json_encode($reuslt) : $reuslt['error_message'];
                            Yii::$app->db->createCommand()
                                ->insert(AliOrderLog::tableName(),['pur_number'=>$v['pur_number'],
                                    'order_number'=>$v['platform_order_number'],
                                    'error_code'=>$code,
                                    'error_message'=>$message.'--'.$v['purchase_acccount'].'--'.$v['platform_order_number'],
                                    'create_date'=>date('Y-m-d H:i:s',time()),
                                    'status'=>'error'])->execute();
                            Yii::$app->db->createCommand()->update(PurchaseOrderPayType::tableName(),
                                ['is_success'=>1,'check_date'=>date('Y-m-d H:i:s',time())],
                                ['id'=>$v['id'],'is_success'=>0])->execute();
                        }

                    } else {
                        Yii::$app->db->createCommand()
                            ->insert(AliOrderLog::tableName(),['pur_number'=>$v['pur_number'],
                                'order_number'=>$v['platform_order_number'],
                                'error_code'=>'accountError',
                                'error_message'=>'采购用户不存在'.'--'.$v['purchase_acccount'],
                                'create_date'=>date('Y-m-d H:i:s',time()),
                                'status'=>'error'])->execute();
                        Yii::$app->db->createCommand()->update(PurchaseOrderPayType::tableName(),
                            ['is_success'=>1,'check_date'=>date('Y-m-d H:i:s',time())],
                            ['id'=>$v['id'],'is_success'=>0])->execute();
                        continue;
                    }
                } else {
                    Yii::$app->db->createCommand()
                        ->insert(AliOrderLog::tableName(),['pur_number'=>$v['pur_number'],
                            'order_number'=>$v['platform_order_number'].'--'.$v['purchase_acccount'],
                            'error_code'=>'orderNumberError',
                            'error_message'=>'平台单号符合要求（长度不够)或采购用户为空',
                            'create_date'=>date('Y-m-d H:i:s',time()),
                            'status'=>'error'])->execute();
                    Yii::$app->db->createCommand()->update(PurchaseOrderPayType::tableName(),
                        ['is_success'=>1,'check_date'=>date('Y-m-d H:i:s',time())],
                        ['id'=>$v['id'],'is_success'=>0])->execute();
                    continue;
                }
            }
            return true;
        } else{
            return -1;
        }
    }
}
