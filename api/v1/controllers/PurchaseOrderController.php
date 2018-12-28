<?php

namespace app\api\v1\controllers;
use app\api\v1\models\ArrivalRecord;
use app\api\v1\models\PlatformSummary;
use app\api\v1\models\PurchaseDemand;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\PurchaseOrderPay;
use app\api\v1\models\PurchaseOrderShip;
use app\api\v1\models\PurchaseWarningStatus;
use app\api\v1\models\SupplierQuotes;
use app\api\v1\models\WarehouseResults;
use app\models\Product;
use app\models\PurchaseAvg;
use app\models\PurchaseOrderCancel;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderPayType;
use app\models\SupplierUpdateApply;
use app\models\TongtoolPurchase;
use app\models\User;
use app\services\BaseServices;
use yii;
use app\config\Vhelper;
use linslin\yii2\curl;
use yii\helpers\Json;
use app\models\SupplierNum;
use yii\data\Pagination;
use app\models\StockLog;
use app\models\PurchaseOrderItemsStock;
use app\services\PurchaseOrderServices;
/**
 * 采购单
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class PurchaseOrderController extends BaseController
{
    public $modelClass = 'app\api\v1\models\PurchaseOrder';

    /**
     * 推送采购员信息
     * @return string
     */
    public function actionBuyerNum()
    {
        $curl = new curl\Curl();
        $query = User::find()->select('user_number,username,status')->asarray()->all();
        try {
            $url = Yii::$app->params['server_ip'] . '/index.php/Test/addBuyerId';

            $s = $curl->setPostParams([
                'addBuyerId' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } 
        } catch (Exception $e) {

            exit('发生了错误');
        }
    }

    /**
     * 推送所有国内采购单
     * @return string
     */
    public function actionPurchaseAll()
    {
        $curl = new curl\Curl();
        $modelClass = $this->modelClass;
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 100;

        $query = $modelClass::find()->joinWith(['purchaseOrderItems','orderPay'])->andwhere(['pur_purchase_order.is_push' => 0])->andwhere(['in','pur_purchase_order.purchas_status',[3,5,6,7,10]])->asarray()->orderby('created_at desc')->limit($limit)->all();


        if($query)
        {
            foreach($query as $k=>&$v)
            {
                if(in_array($v['warehouse_code'],['FBA_SZ_AA','SZ_AA','ZDXNC','HW_XNC']) && ($v['purchas_status']==4 ||$v['purchas_status']==10))
                {
                    unset($query[$k]);
                    //PurchaseOrder::updateAll(['is_push'=>1],['id'=>$v['id']]);
                }
                //根据username获取user_number
                $username = $v['buyer'];
                if ($username) {
                    $buyerInfo = User::findByUsername($username);
                    $buyer_id = isset($buyerInfo->user_number) ? $buyerInfo->user_number : "";
                }
                
                $v['buyer_id'] = $buyer_id ? : " ";
            }
        }
        try {
            $url = Yii::$app->params['server_ip'] . '/index.php/purchases/insertPurchaseToMysql';
            $s = $curl->setPostParams([
                'purchase' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {
                $_result = Json::decode($s);
                if ($_result['success_list'] && !empty($_result['success_list'])) {
                    foreach ($_result['success_list'] as $v) {
                        $modelClass::updateAll(['is_push' => 1], 'pur_number = :pur_number', [':pur_number' =>$v]);
                    }

                } else {
                    Vhelper::dump($s);
                }
            }
        } catch (Exception $e) {

            exit('发生了错误');
        }


    }
    /**
     * 取消国内仓采购单状态为10的
     * @return string
     */
    public function actionPurchaseCancel()
    {
        $curl = new curl\Curl();
        $modelClass = $this->modelClass;
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 50;
        //获取作废的
        $query        = $modelClass::find()->select('pur_number,purchase_type,created_at,warehouse_code,transit_warehouse')->where(['is_push'=>0])
            ->andwhere(['in','warehouse_code',['FBA_SZ_AA','SZ_AA','ZDXNC','HW_XNC']])
            ->andwhere(['in','pur_purchase_order.purchas_status',['10']])->asarray()->limit($limit)->all();
        //获取部分到货不等待剩余的
        $query_s      = $modelClass::find()->select('pur_number,purchase_type,created_at,warehouse_code,transit_warehouse')->where(['is_push'=>0])
            ->andwhere(['in','warehouse_code',['FBA_SZ_AA','SZ_AA','ZDXNC','HW_XNC']])
            ->andwhere(['in','pur_purchase_order.purchas_status',['9']])->asarray()->limit($limit)->all();
        $querys = $this->getPurchase($query);
        $queryb = $this->getPurchase($query_s);
        $queryd = yii\helpers\ArrayHelper::merge($querys,$queryb);
        $queryd = Vhelper::ThereArrayTwo($queryd);
        if(!empty($queryd)) {
            try {
                $url = Yii::$app->params['server_ip'] . '/index.php/purchases/cancelPurchase';
                $curl->setOption(CURLOPT_TIMEOUT,100);
                $s = $curl->setPostParams([
                    'purchase' => Json::encode($queryd),
                    'token' => Json::encode(Vhelper::stockAuth()),
                ])->post($url);
                //验证json
                $sb = Vhelper::is_json($s);
                if (!$sb) {
                    echo '请检查json' . "\r\n";
                    exit($s);
                } else {
                    $_result = Json::decode($s);
                    if (isset($_result['failure_list']) && !empty($_result['failure_list'])) {
                        foreach ($_result['failure_list'] as $failValue) {
                            $modelClass::updateAll(['is_push' => 1], 'pur_number = :pur_number', [':pur_number' => $failValue['pur_number']]);
                        }
                    }

                    if (isset($_result['success_list']) && !empty($_result['success_list'])) {
                        foreach ($_result['success_list'] as $v) {
                            $modelClass::updateAll(['is_push' => 1], 'pur_number = :pur_number', [':pur_number' => $v['pur_number']]);
                        }

                    } else {
                        Vhelper::dump($s);
                    }
                }
            } catch (Exception $e) {

                exit('发生了错误');
            }
        }else{
            exit('没有在途数据推送');
        }


    }
    protected function  getPurchase($data, $type=4)
    {

        $queryb= [];
        foreach ($data as $k=>$v)
        {
            $rs2     = PurchaseOrderItems::find()->select(['id','pur_number','sku','ctq','cty'=>'ifnull(cty,0)'])
                        ->where(['in','pur_number',$v['pur_number']])
                        ->asArray()->all();
            foreach($rs2 as $d=>$c)
            {
                $queryb[$k][$d]['id'] = $c['id'];
                $queryb[$k][$d]['pur_number'] = $c['pur_number'];
                $queryb[$k][$d]['warehouse_code'] = $v['warehouse_code'];
                $queryb[$k][$d]['transit_warehouse'] = !empty($v['transit_warehouse']) ? $v['transit_warehouse'] : '';
                $queryb[$k][$d]['sku'] = $c['sku'];
                $queryb[$k][$d]['created_at'] = !empty($v['created_at']) ? $v['created_at'] : '';
                $queryb[$k][$d]['ctq'] = !empty($c['cty']) ? (($c['ctq'] - $c['cty'])>=0 ? $c['ctq'] - $c['cty'] :0) : $c['ctq'];
                $queryb[$k][$d]['cancel_operator'] = !empty($v['buyer']) ? $v['buyer'] : '';
                $queryb[$k][$d]['type'] = $type; //取消的类型
                $queryb[$k][$d]['check_operator'] = '';
                $queryb[$k][$d]['status'] = !empty($v['purchas_status']) ? $v['purchas_status'] : '';
                $queryb[$k][$d]['purchase_type'] = !empty($v['purchase_type']) ? $v['purchase_type'] : '';
            }
        }
        return $queryb;
    }

    /**
     * 推送所有采购单物流信息
     * @return string
     */
    public function actionPurchaseShipAll()
    {
        $curl = new curl\Curl();
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 100)
            $limit = 100;
        $query = PurchaseOrderShip::find()->where(['is_push' =>0])->andWhere(['NOT', ['express_no' => null]])->andWhere(['not',['express_no'=>'']])->asarray()->limit($limit)->all();


        foreach ($query as $k=>$v)
        {
            if (!empty($v['cargo_company_id']))
                {
                    if(preg_match ("/^[a-z]/i",$v['cargo_company_id']))
                    {
                        $se  = BaseServices::getLogisticsCarrier($v['cargo_company_id']);
                        $query[$k]['express_name'] = $se?$se->name:'';
                    } else {
                        $query[$k]['express_name'] = $v['cargo_company_id'];
                    }
                }

        }
        try {
            $url = Yii::$app->params['server_ip'] . '/index.php/purchases/insertPurchaseExpressToMysql';
            $s = $curl->setPostParams([
                'purchase_ship' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {

                    $_result = Json::decode($s);
                    if ($_result['success_list'] && !empty($_result['success_list'])) {
                        foreach ($_result['success_list'] as $v) {
                            if(!empty($v))
                            {
                                PurchaseOrderShip::updateAll(['is_push' => 1], 'express_no = :express_no', [':express_no'=>$v]);
                            }



                        }

                    } else {
                        Vhelper::dump($s);
                    }
            }
        } catch (Exception $e) {

            exit('发生了错误');
        }


    }

    /**
     * 接收仓库返回的结果
     */
    public function actionWarehouseResults()
    {
        error_reporting(E_ALL);
        Yii::$app->response->format ='raw';
        $results = Yii::$app->request->post()['results'];
        if (isset($results) && !empty($results))
        {
            $results = Json::decode($results);
            $find    = WarehouseResults::findWarehouse($results);
            echo json_encode($find);
            Yii::$app->end();
           // return $find;
        } else {

            return '没有任何的数据传输过来！';
        }

    }

    /**
     * 获取通途采购单
     */
    public function actionTongToolPurchase()
    {

        set_time_limit(50000);
       $is= SupplierNum::find()->select('num')->where(['type'=>6])->orderBy('id desc')->scalar();

        if(!empty($is))
        {
            $id = $is;
        } else{
            $id = 1;
        }
            $curl  = new curl\Curl();
            $datas = [
                'token' => 'b24fe215-7a7b-4e83-85be-e917d59eef18',

                'data'  => [
                    'merchantId' => '003498',
                    //'updatedDateFrom'=>'2017-12-01 00:00:00',
                    //'updatedDateTo'=>'2017-12-26 00:00:00',
                     'pageNo'     => $id,
                    //'productType' =>'0',
                ],
            ];
            try {
                $url     = Yii::$app->params['tongtool'] . '/process/resume/openapi/tongtool/purchaseOrdersQuery';
                $s       = $curl->setPostParams([
                    'q'  => Json::encode($datas),
                ])->post($url);
                $_result = Json::decode($s);
                if(!is_array($_result['data']['array']))
                {
                    $mod      = new SupplierNum();
                    $mod->num = $id;
                    $mod->type = 6;
                    $mod->time = time();
                    $mod->save(false);
                    exit();
                } else {
                    TongtoolPurchase::SaveTongTool($_result['data']['array']);
                    $mod      = new SupplierNum();
                    $mod->num = $id+1;
                    $mod->type = 6;
                    $mod->time = time();
                    $mod->save(false);
                }

                // Vhelper::dump($i);
                //$s = is_array($_result['data']['array']) ? Supplier::SaveTongTool($_result['data']['array']) : '不是数组';


            } catch (Exception $e) {

                exit('发生了错误');
            }

    }

    /**
     * 推送所有采购单需求信息
     * @return string
     * @link http://www.purchase.net/v1/purchase-order/purchase-demand-all
     */
    public function actionPurchaseDemandAll()
    {
        //Yii::$app->response->format = 'raw';
        $curl = new curl\Curl();
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 300;
        //推送已经同意或者驳回的需求
        $query = PlatformSummary::find()
            ->select('t.*,pd.pur_number')
            ->alias('t')
            ->leftJoin(PurchaseDemand::tableName().' pd','pd.demand_number=t.demand_number')
            ->where(['t.is_push' =>0])
            ->asarray()->limit($limit)->all();
        try {
            if(empty($query)) exit('没有需要推送的数据！');

            $curl->setOption(CURLOPT_TIMEOUT,115);
            $url = Yii::$app->params['server_ip'] . '/index.php/purchases/platformSummaryToMysql';
            $s = $curl->setPostParams([
                'purchase_ship' => Json::encode($query),
                'token'    => Json::encode(Vhelper::stockAuth()),
            ])->post($url);
//            Vhelper::dump($s);
            //验证json
            $sb = Vhelper::is_json($s);
            if(!$sb)
            {
                echo '请检查json'."\r\n";
                exit($s);
            } else {

                $_result = Json::decode($s);
                if (isset($_result['data']) && !empty($_result['data'])) {
                    foreach ($_result['data']['success'] as $v) {
                        PlatformSummary::updateAll(['is_push' => 1], 'id = :id', [':id' => $v]);
                    }
                } else {
                    Vhelper::dump($s);
                }
                echo '推送成功';
                Yii::$app->end();
            }
        } catch (Exception $e) {

            exit('发生了错误');
        }


    }

    /**
     * 通过采购单号找到采购单与中间表的需求单号，然后拿到需求单号对需求表进行重新更新推送状态
     */
    public function  actionUpdatePush()
    {
        $purs = Yii::$app->request->post()['pro'];
        if($purs)
        {

            $purs = Json::decode($purs);
            $s    = PurchaseDemand::getDemand($purs);
            echo json_encode($s);
            Yii::$app->end();

        } else{
            exit('没有采购单号传递过来');
        }

    }

    /**
 * 获取到采购平均价格
 */
    public function actionGetSkuAvg()
    {
        $page  = (int)Yii::$app->request->get('page', 0);
        $limit = (int)Yii::$app->request->get('limit', 1000);
        $date  = Yii::$app->request->getQueryParam('date',date('Y-m-d H:i:s',time()));
        $time  = strtotime($date);
        $limit = $limit>=1000?1000:$limit;
        $start_time = date('Y-m-d 00:00:00',$time-24*60*60);
        $end_time   = date('Y-m-d 23:59:59',$time-24*60*60);
        $queryMax = PurchaseAvg::find()->select(['id'=>'max(id)'])->groupBy('sku')->column();
        $searchIdArray = array_chunk($queryMax,$limit);
        $searchIds = $searchIdArray[$page];
        $data['data'] = PurchaseAvg::find()->where(['id'=>$searchIds])->asArray()->all();
//        $queryMax = PurchaseAvg::find()->offset($page*$limit)
//            ->andWhere(['between', 'create_time', $start_time, $end_time])
//            ->limit($limit)
//            ->orderBy('id ASC');
//        $date['data']=$queryMax->asArray()->all();
        foreach ($data['data'] as $k=>$v)
        {
            $data['data'][$k]['product_status'] =Product::find()->select('product_status')->where(['sku'=>$v['sku']])->scalar();
            $latest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.base_price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(0)->limit(1)->scalar();
            if(empty($latest_purchase_price)||$latest_purchase_price==0){
                $latest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(0)->limit(1)->scalar();
            }
            $nearest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.base_price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(1)->limit(1)->scalar();
            if(empty($nearest_purchase_price)||$nearest_purchase_price==0){
                $nearest_purchase_price = PurchaseOrderItems::find()->alias('t')->select('t.price')->leftJoin(PurchaseOrder::tableName().' a','t.pur_number=a.pur_number')->where(['t.sku'=>$v['sku']])->andWhere(['not in','a.purchas_status',[1,2,4,10]])->orderBy('a.audit_time DESC')->offset(1)->limit(1)->scalar();
            }
            $data['data'][$k]['latest_purchase_price'] = $latest_purchase_price;
            $data['data'][$k]['nearest_purchase_price']= $nearest_purchase_price;
        }
        $data['page']  = $page;
        $data['limit'] = $limit;
        $data['total'] = PurchaseAvg::find()->groupBy('sku')->count();
        return $data;
    }

    //国内仓更新采购计划单状态
    public function actionUpdatePurchase(){
        PurchaseOrder::updateAll(['purchas_status'=>4],['purchas_status'=>1,'purchase_type'=>1]);
    }

    public function actionGetEarlyWarningStatus(){
        set_time_limit(0);
        $data =PurchaseOrderItems::find()
                ->select('t.sku,t.pur_number')
                ->alias('t')
                ->leftJoin(PurchaseOrder::tableName().' o','o.pur_number=t.pur_number')
                ->where(['in','o.purchas_status',[5,7,8]])
                ->andWhere(['t.is_check'=>0])
                ->orderBy('t.id ASC')
                ->limit(1000)
                ->asArray()
                ->all();

        foreach ($data as $v){
            $datas = self::getEarlyWarningStatus($v['sku'],$v['pur_number']);
            PurchaseWarningStatus::deleteAll(['sku'=>$v['sku'],'pur_number'=>$v['pur_number']]);
            $insertData =[];
            foreach ($datas as $key=>$s){
                $insertData[$key][]=$v['sku'];
                $insertData[$key][]=$v['pur_number'];
                $insertData[$key][]=$s;
            }
            Yii::$app->db->createCommand()->batchInsert(PurchaseWarningStatus::tableName(),['sku','pur_number','warn_status'],$insertData)->execute();
            PurchaseOrderItems::updateAll(['is_check'=>1],['sku'=>$v['sku'],'pur_number'=>$v['pur_number'],'is_check'=>0]);
        }
        if(count($data)<1000){
            PurchaseOrderItems::updateAll(['is_check'=>0]);
        }
    }


    public static function getEarlyWarningStatus($sku,$pur_number){
        $data = [];
        $andit_time = PurchaseOrder::find()->select('audit_time')->where(['pur_number'=>$pur_number])->scalar();
        $appTime = PurchaseOrderPay::find()->where(['pur_number'=>$pur_number])->one();
        if(!empty($andit_time)&&empty($appTime)&&(time()-strtotime($andit_time))/(60*60)>12){
            $data[]= 1;
        }
        $payTime = PurchaseOrderPay::find()->where(['pur_number'=>$pur_number])->andWhere('isnull(payer_time)')->one();
        if(!empty($payTime)&&(time()-strtotime($payTime->application_time))/3600>12){
            $data[]= 2;
        }
        $ship = PurchaseOrderShip::find()->where(['pur_number'=>$pur_number])->andWhere('not isnull(express_no)')->one();
        if(empty($ship)&&!empty($appTime->payer_time)&&(time()-strtotime($appTime->payer_time))/3600>24){
            $data[]= 3;
        }
        $arriveTime = ArrivalRecord::find()->where(['purchase_order_no'=>$pur_number,'sku'=>$sku])->one();
        if(!empty($appTime->payer_time)&&empty($arriveTime)&&(time()-strtotime($appTime->payer_time))/3600>144){
            $data[]=4;
        }
        $instock  = WarehouseResults::find()->where(['pur_number'=>$pur_number,'sku'=>$sku])->one();
        if(!empty($arriveTime)&&(time()-strtotime($arriveTime->delivery_time))/3600>24&&empty($instock)){
            $data[]=5;
        }
        return $data;
    }
    /**
     * 推送取消在途【海外仓-作废订单和FBA取消未到货】
     * http://caigou.yibainetwork.com/v1/purchase-order/pull-cancel-stock
     */
    public function actionPullCancelStock()
    {

        $curl = new curl\Curl();
        $limit = (int)Yii::$app->request->getQueryParam('limit',200);
        $query = PurchaseOrderCancelSub::find()
            ->alias('t')
            ->select('t.id,t.cancel_id,t.pur_number,po.warehouse_code,po.transit_warehouse,t.sku,t.cancel_ctq,poc.buyer,poc.audit,po.purchas_status,t.demand_number')
            ->leftJoin(PurchaseOrderCancel::tableName().' poc','poc.id=t.cancel_id')
            ->leftJoin(PurchaseOrder::tableName().' po','po.pur_number=t.pur_number')
            ->where(['t.is_push'=>0,'poc.audit_status'=>2])
            ->andWhere(['<>','t.cancel_ctq',0])
            ->limit($limit)
            ->asArray()
            ->all();
        $data = array();
        if (!empty($query)) {
            $i = 1;
            foreach ($query as $k=>$v) {
                $data[$i]['id'] = $v['id'];
                $data[$i]['cancel_id'] = $v['cancel_id'];
                $data[$i]['pur_number'] = $v['pur_number'];
                $data[$i]['warehouse_code'] = $v['warehouse_code'];
                $data[$i]['transit_warehouse'] = $v['transit_warehouse'];
                $data[$i]['sku'] = $v['sku'];
                $data[$i]['ctq'] = $v['cancel_ctq'];
                $data[$i]['cancel_operator'] = $v['buyer'];
                $data[$i]['type'] = 3; //FBA取消未到货
                $data[$i]['check_operator'] = $v['audit'];
                $data[$i]['status'] = $v['purchas_status'];
                $data[$i]['demand_number'] = $v['demand_number'];
                $data[$i]['purchase_type'] = Vhelper::getNumber($v['pur_number']);
                $i++;
            }
        }
        if(!empty($data)) {
            try {
                $url = Yii::$app->params['server_ip'] . '/index.php/purchases/cancelPurchaseByOrderSku';
                $s = $curl->setPostParams([
                    'purchase' => Json::encode($data),
                    //'token' => Json::encode(Vhelper::stockAuth()),
                ])->post($url);
                //验证json
                $sb = Vhelper::is_json($s);

                if (!$sb) {
                    echo '请检查json' . "\r\n";
                    exit($s);
                } else {
                    $_result = Json::decode($s);

                    if (isset($_result['failure_list']) && !empty($_result['failure_list'])) {
                        foreach ($_result['failure_list'] as $failValue) {
                            PurchaseOrderCancelSub::updateAll(['is_push' => 2], 'id = :id', [':id' => $failValue['id']]);
                        }
                    }
                    if (isset($_result['success_list']) && !empty($_result['success_list'])) {
                        foreach ($_result['success_list'] as $v) {
                            PurchaseOrderCancelSub::updateAll(['is_push' => 1], 'id = :id', [':id' => $v['id']]);
                        }
                    } else {
                        Vhelper::dump($s);
                    }
                }
            } catch (Exception $e) {

                exit('发生了错误');
            }
        }else{
            exit('没有在途数据推送');
        }
    }
    /**
     * 推送取消海外
     * http://caigou.yibainetwork.com/v1/purchase-order/pull-cancel-overseas
     */
    public function actionPullCancelOverseas()
    {
        $curl = new curl\Curl();
        $modelClass = $this->modelClass;
        $limit = (int)Yii::$app->request->get('limit');
        if ($limit <= 0 || $limit > 1000)
            $limit = 100;

        //获取作废的
        $query = $modelClass::find()
            ->select('pur_number,warehouse_code,created_at,buyer,purchas_status,transit_warehouse,purchase_type')
            ->where(['is_push'=>0,'purchase_type'=>2])
            ->andwhere(['in','purchas_status',['9','10']])
            ->asarray()->limit($limit)->all();
        $querys = $this->getOverseasPurchase($query);
        $queryd = Vhelper::ThereArrayTwo($querys);

        if (!empty($queryd)) {
            try {
                $url = Yii::$app->params['server_ip'] . '/index.php/purchases/cancelPurchaseByOrderSku';
                $s = $curl->setPostParams([
                    'purchase' => Json::encode($queryd),
                    //'token' => Json::encode(Vhelper::stockAuth()),
                ])->post($url);

                //验证json
                $sb = Vhelper::is_json($s);
                if (!$sb) {
                    echo '请检查json' . "\r\n";
                    exit($s);
                } else {
                    $_result = Json::decode($s);

                    if (isset($_result['failure_list']) && !empty($_result['failure_list'])) {
                        foreach ($_result['failure_list'] as $failValue) {
                            //$modelClass::updateAll(['is_push' => 1], 'pur_number = :pur_number', [':pur_number' => $failValue['pur_number']]);
                        }
                    }

                    if (isset($_result['success_list']) && !empty($_result['success_list'])) {
                        foreach ($_result['success_list'] as $v) {
                            $items_model = PurchaseOrderItems::find()->where(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']])->one();
                            $wcq = (int)$items_model->wcq;
                            $items_model->wcq = $wcq + $v['ctq'];
                            $tran = Yii::$app->db->beginTransaction();
                            try {
                                $items_model->save(false);
                                $modelClass::updateAll(['is_push' => 1], 'pur_number = :pur_number', [':pur_number' => $v['pur_number']]);
                                $tran->commit();
                            } catch (yii\db\Exception $e) {
                                $tran->rollBack();
                            }
                        }

                    } else {
                        Vhelper::dump($s);
                    }
                }
            } catch (Exception $e) {

                exit('发生了错误');
            }
        } else {
            exit('没有在途数据推送');
        }
    }

    protected function  getOverseasPurchase($data, $type=4)
    {

        $queryb= [];
        foreach ($data as $k=>$v)
        {
            $rs2     = PurchaseOrderItems::find()->select(['id','pur_number','sku','ctq','rqy'=>'ifnull(rqy,0)'])
                ->where(['in','pur_number',$v['pur_number']])
                ->asArray()->all();
            foreach($rs2 as $d=>$c)
            {
                $queryb[$k][$d]['id'] = $c['id'];
                $queryb[$k][$d]['pur_number'] = $c['pur_number'];
                $queryb[$k][$d]['warehouse_code'] = $v['warehouse_code'];
                $queryb[$k][$d]['transit_warehouse'] = !empty($v['transit_warehouse']) ? $v['transit_warehouse'] : '';
                $queryb[$k][$d]['sku'] = $c['sku'];
                $queryb[$k][$d]['created_at'] = !empty($v['created_at']) ? $v['created_at'] : '';
                $queryb[$k][$d]['ctq'] = !empty($c['rqy']) ? (($c['ctq'] - $c['rqy'])>=0 ? $c['ctq'] - $c['rqy'] :0) : $c['ctq'];
                $queryb[$k][$d]['cancel_operator'] = !empty($v['buyer']) ? $v['buyer'] : '';
                $queryb[$k][$d]['type'] = $type; //取消的类型
                $queryb[$k][$d]['check_operator'] = '';
                $queryb[$k][$d]['status'] = !empty($v['purchas_status']) ? $v['purchas_status'] : '';
                $queryb[$k][$d]['purchase_type'] = !empty($v['purchase_type']) ? $v['purchase_type'] : '';
            }
        }
        return $queryb;
    }
    
    public function actionPurchaseStocklog()
    {
        $data = Yii::$app->request->post()['data'];
        if (empty($data)) {
            exit('没有数据传递过来');
        }
        //$data = '[{"id":"4489084","warehouse_code":"TS","sku":"XD02309","platform_code":"","left_stock":"614","qty":"200","operate_type":"delivery","key_id":"FBA026756","message":"\u5165\u5e93","operate_time":"2018-07-30 16:03:07","operator":"\u674e\u5168\u690d"},{"id":"4506160","warehouse_code":"TS","sku":"XD00907","platform_code":"","left_stock":"400","qty":"100","operate_type":"delivery","key_id":"FBA022475","message":"\u5165\u5e93","operate_time":"2018-07-30 16:03:09","operator":"\u674e\u5168\u690d"},{"id":"4844052","warehouse_code":"TS","sku":"XD00907","platform_code":"","left_stock":"0","qty":"-100","operate_type":"pack","key_id":"FBA-SPH18072400045","message":"pack","operate_time":"2018-07-30 16:22:42","operator":"\u674e\u5168\u690d"}]';
        $data = Json::decode($data);
        $success_list = $fail_list = [];
        foreach ($data as $v) {
            if (StockLog::findOne(['w_log_id'=>$v['id']])) {
                $success_list[] = $v['id'];
                continue;
            }
            
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $log_data = [];//日志数组
                if ($v['operate_type'] == 'delivery') {
                    //入库
                    $pur_number = $v['key_id'];
                    $log_data[] = ['pur_number'=>$pur_number,'qty'=>$v['qty']];
                    
                } else {
                    if ($v['operate_type'] != 'pack' && $v['qty'] > 0){
                        $success_list[] = $v['id'];
                        continue;
                    }
                    //打包发货、损益, 把库存分配到PO上
                    $stocklog = StockLog::find()->where(['sku'=>$v['sku']])->andWhere("delivery_left_stock > 0")->orderBy("id asc")->all();
                    if ($stocklog) {
                        $num = abs($v['qty']);
                        foreach ($stocklog as $log) {
                            if ($log->delivery_left_stock <= $num) {
                                $log_data[] = ['pur_number'=>$log->pur_number,'qty'=>-$log->delivery_left_stock,'operate_type'=>$v['operate_type']];
                                $num -= $log->delivery_left_stock;
                                $log->delivery_left_stock = 0;
                                $log->stock_clear_time = $v['operate_time'];
                                $log->save(false);
                                if ($num == 0) break;
                            } else {
                                $log->delivery_left_stock = $log->delivery_left_stock - $num;
                                $log->save(false);
                                $log_data[] = ['pur_number'=>$log->pur_number,'qty'=>-$num,'operate_type'=>$v['operate_type']];
                                break;
                            }
                        }
                    }
                }
                
                foreach ($log_data as $log) {
                    
                    $log_model = new StockLog();
                    $log_model->sku = $v['sku'];
                    $log_model->warehouse_code = $v['warehouse_code'];
                    $log_model->pur_number = $log['pur_number'];
                    $log_model->change_qty = $log['qty'];
                    $log_model->after_qty = 0;
                    $log_model->key_id = $v['key_id'];
                    $log_model->message = $v['message'];
                    $log_model->operate_type = $v['operate_type'];
                    $log_model->w_log_id = $v['id'];
                    if ($v['operate_type'] == 'delivery') {
                        $log_model->delivery_left_stock = $log['qty'];
                    }
                    $log_model->operate_time = $v['operate_time'];
                    $log_model->operator = $v['operator'];
                    $log_model->create_time = date('Y-m-d H:i:s');
                    $log_model->save(false);
                    
                    $model = PurchaseOrderItemsStock::findOne(['pur_number'=>$log['pur_number'],'sku'=>$v['sku']]);
                    if (empty($model)) {
                        $model = new PurchaseOrderItemsStock();
                        $model->pur_number = $log['pur_number'];
                        $model->sku = $v['sku'];
                        $model->stock = $log['qty'];
                        $model->status = 1;
                        $model->stock_stay_days = 0;
                        $model->order_total = 0;
                        $model->profit_loss = 0;
                    } else {
                        $model->stock += $log['qty'];
                        $model->status = $model->stock == 0 ? 2 : 1;
                    }
                    if (isset($log['operate_type'])) {
                        if ($log['operate_type'] == 'pack') {
                            $model->order_total -= $log['qty'];
                        } else {
                            $model->profit_loss -= $log['qty'];
                        }
                    }
                    $model->update_time = date('Y-m-d H:i:s');
                    $model->save();
                }
                
                $transaction->commit();
                $success_list[] = $v['id'];
            } catch (Exception $e) {
                $transaction->rollBack();
                $fail_list[] = ['id'=>$v['id'],'message'=>'save data fail'];
            }
        }
        
        echo json_encode(['code'=>1,'success_list'=>$success_list,'fail_list'=>$fail_list]);
        Yii::$app->end();
    }


    /**
     * 根据SKU获取 仓库库存、仓位最新操作信息
     * @param $sku
     * @return array
     */
    public static function getSkuStorageStockFromWms($sku){
        $url = Yii::$app->params['wms_domain'] . '/Api/Warehouse/Stock/getPurchaseStock';

        $data = [$sku];

        $curl = new curl\Curl();
        $response = $curl->setPostParams([
                  'data'  => Json::encode($data),
                  'token' => Json::encode(Vhelper::stockAuth()),
              ])->post($url);

        $sb = Vhelper::is_json($response);// 验证json

        $sku_list_tmp = [];
        if ($sb){
            $_result = Json::decode($response,true);
            $sku_list = isset($_result['data'])?$_result['data']:'';

            if($sku_list){
                foreach($sku_list as $v_list){
                    $sku = $v_list['sku'];
                    $location_list = $v_list['shelfs'];
                    $stock_list = $v_list['stock'];

                    if($stock_list){
                        $stock_list = array_column($stock_list,'available_qty','warehouse_code');
                    }
                    $sku_list_tmp[$sku]['location_list'] = $location_list;
                    $sku_list_tmp[$sku]['stock_list'] = $stock_list;
                }
            }
        }

        return $sku_list_tmp;
    }
}
