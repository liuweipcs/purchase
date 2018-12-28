<?php

namespace app\controllers;

use app\api\v1\models\ArrivalRecord;
use app\config\Vhelper;
use app\models\PurchaseFreightHistory;
use app\models\PurchaseOrder;
use Yii;
use app\models\User;
use app\models\PurchaseSuggest;
use app\models\PurchaseOrderSearch;
use app\models\PurchaseOrderPay;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;

use app\services\BaseServices;


/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class OverseasPurchaseOrderStatisticsController extends BaseController
{

    /**
     * 采购下单完成率
     * @return mixed
     */
    public function actionIndex()
    {
        set_time_limit(60);
        $searchModel = new PurchaseOrderSearch();
        $dataProvider = $searchModel->searchOrderRate(Yii::$app->request->queryParams);
        //开始时间、结束时间
        if(isset(Yii::$app->request->queryParams['start_time'])){
            $searchModel->start_time = Yii::$app->request->queryParams['start_time'];
        }
        if(isset(Yii::$app->request->queryParams['end_time'])){
            $searchModel->end_time = Yii::$app->request->queryParams['end_time'];
        }
        if (!empty($dataProvider)) {
            $query = $dataProvider;
        } else {
            $query = User::find();
        }

        //获取海外仓采购员名单
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 20, 'pageParam' => 'ym']);
        $buyer = ArrayHelper::map($query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all(),'id','username');

        //获取开始时间、结束时间
        $start_time = '';
        $end_time = '';
        if(isset(Yii::$app->request->queryParams['start_time'])){
            $start_time = Yii::$app->request->queryParams['start_time'];
        }
        if(isset(Yii::$app->request->queryParams['end_time'])){
            $end_time = Yii::$app->request->queryParams['end_time'];
        }

        $arrList = [];
        $connection = Yii::$app->db;

        //默认查询昨天的数据
        $now = date('Y-m-d 23:59',strtotime('-1day'));//一天结束
        $now_7 = date('Y-m-d 00:00',strtotime('-1day'));//一天开始
        //付款完成率-----开始
        if(isset($_GET['source']) && $_GET['source']==2){
            foreach ($buyer as $key=>$val){
                //获取总下单数  查询对应采购员的海外仓订单
                $totalOrder = PurchaseOrder::find()
                    ->where(['buyer' => "{$val}", 'purchase_type' => 2])
                    ->andWhere(['>=', 'created_at', $now_7])
                    ->andWhere(['<=', 'created_at', $now])
                    ->count();
                if ($start_time && $end_time) {
                    $totalOrder = PurchaseOrder::find()
                        ->where(['buyer' => "{$val}", 'purchase_type' => 2])
                        ->andWhere(['>=', 'created_at', $start_time])
                        ->andWhere(['<=', 'created_at', $end_time])
                        ->count();
                }

                //总下单数>0 计算完成率 PO单数 SKU数
                $list = [];
                if(!empty($totalOrder) && $totalOrder > 0){
                    $list['total'] = $totalOrder;
                    //18小时付款订单数
                    $number18 = "
                        select count(DISTINCT(pay.pur_number)) as num from pur_purchase_order_pay as pay, pur_purchase_order as pur_order
                        where pay.applicant='{$key}'
                        and pay.pur_number = pur_order.pur_number
                        and pay.pay_status=5
                        and timestampdiff(minute,pay.application_time,pay.payer_time) >= 0
                        and timestampdiff(minute,pay.application_time,pay.payer_time) <= 1080
                    ";
                    if ($start_time && $end_time) {
                        $number18 .= "
                            and pur_order.created_at >= '{$start_time}'
                            and pur_order.created_at <= '{$end_time}'
                        ";
                    }else{
                        $number18 .= "
                            and pur_order.created_at >= '{$now_7}'
                            and pur_order.created_at <= '{$now}'
                        ";
                    }
                    $list['number_18']=$connection->createCommand($number18)->queryScalar();

                    //24小时付款订单数
                    $number24 = "
                        select count(DISTINCT(pay.pur_number)) as num from pur_purchase_order_pay as pay, pur_purchase_order as pur_order
                        where pay.applicant='{$key}'
                        and pay.pur_number = pur_order.pur_number
                        and pay.pay_status=5
                        and timestampdiff(minute,pay.application_time,pay.payer_time) >= 0
                        and timestampdiff(minute,pay.application_time,pay.payer_time) <= 1440
                    ";
                    if ($start_time && $end_time) {
                        $number24 .= "
                            and pur_order.created_at >= '{$start_time}'
                            and pur_order.created_at <= '{$end_time}'
                        ";
                    }else{
                        $number24 .= "
                            and pur_order.created_at >= '{$now_7}'
                            and pur_order.created_at <= '{$now}'
                        ";
                    }
                    $list['number_24']=$connection->createCommand($number24)->queryScalar();
                }else{
                    $list['total'] = 0;
                    $list['number_18'] = 0;
                    $list['number_24'] = 0;
                    $list['po_num'] = 0;
                    $list['sku_num'] = 0;
                }

                $arrList[$val]  = $list;
            }

            return $this->render('payment', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'pager' => $pagination,
                'list' => $arrList,
                'total' => count($arrList)
            ]);
        }
        //付款完成率-----结束

        //下单率-----开始
        if(isset($_GET['source']) && $_GET['source']==3){
            //获取下单数开始时间、结束时间
            $audit_time_start = '';
            $audit_time_end = '';
            if(isset(Yii::$app->request->queryParams['audit_time_start'])){
                $audit_time_start = Yii::$app->request->queryParams['audit_time_start'];
                $searchModel->audit_time_start = $audit_time_start;
            }
            if(isset(Yii::$app->request->queryParams['audit_time_end'])){
                $audit_time_end = Yii::$app->request->queryParams['audit_time_end'];
                $searchModel->audit_time_end = $audit_time_end;
            }
            foreach ($buyer as $key => $val) {
                //获取po数量
                $po_number = "
                    SELECT count(DISTINCT pur_purchase_order.pur_number) as po_num
                    FROM `pur_purchase_order`
                    WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$val}') 
                    and (`pur_purchase_order`.`purchase_type`='2') 
                ";
                if ($audit_time_start && $audit_time_end) {
                    $po_number .= "AND (`audit_time` BETWEEN '{$audit_time_start}' AND '{$audit_time_end}');";
                }else{
                    $po_number .= "AND (`audit_time` BETWEEN '{$now_7}' AND '{$now}');";
                }

                $po_num = $connection->createCommand($po_number)->queryOne();
                if (!empty($po_num['po_num'])) {
                    $list['po_num'] = $po_num['po_num'];
                } else {
                    $list['po_num'] = 0;
                }
                //获取sku数量
                $sku_number = "
                    SELECT count(*) as sku_num 
                    FROM pur_purchase_order_items 
                    WHERE pur_number in(
                    SELECT pur_number 
                    FROM `pur_purchase_order` 
                    WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$val}') and (`pur_purchase_order`.`purchase_type`='2')
                ";
                if ($audit_time_start && $audit_time_end) {
                    $sku_number .= "AND (`audit_time` BETWEEN '{$audit_time_start}' AND '{$audit_time_end}'));";
                }else{
                    $sku_number .= "AND (`audit_time` BETWEEN '{$now_7}' AND '{$now}'));";
                }
                $sku_num = $connection->createCommand($sku_number)->queryOne();
                if (!empty($sku_num['sku_num'])) {
                    $list['sku_num'] = $sku_num['sku_num'];
                } else {
                    $list['sku_num'] = 0;
                }

                $arrList[$val] = $list;
            }

            return $this->render('order', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'pager' => $pagination,
                'list' => $arrList
            ]);
        }
        //下单率-----结束

        //采购下单完成率-----开始
        foreach ($buyer as $key => $val) {
            //获取总下单数  查询对应采购员的海外仓订单
            $totalOrder = PurchaseOrder::find()
                ->where(['buyer' => "{$val}", 'purchase_type' => 2])
                ->andWhere(['>=', 'created_at', $now_7])
                ->andWhere(['<=', 'created_at', $now])
                ->count();
            if ($start_time && $end_time) {
                $totalOrder = PurchaseOrder::find()
                    ->where(['buyer' => "{$val}", 'purchase_type' => 2])
                    ->andWhere(['>=', 'created_at', $start_time])
                    ->andWhere(['<=', 'created_at', $end_time])
                    ->count();
            }
            //总下单数>0 计算完成率 PO单数 SKU数
            $list = [];
            if (!empty($totalOrder) && $totalOrder > 0) {
                $list['total'] = $totalOrder;
                ////18小时完成订单数 0H-18H
                $number18 = "
                    select count(DISTINCT pur_purchase_order.pur_number) as num from pur_purchase_suggest,pur_purchase_order,pur_purchase_demand
                    where pur_purchase_demand.pur_number = pur_purchase_order.pur_number
                    and pur_purchase_demand.demand_number = pur_purchase_suggest.demand_number
                    and pur_purchase_order.purchase_type = 2
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) >= 0
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) <= 1080
                    and pur_purchase_order.buyer='{$val}'
                ";
                if ($start_time && $end_time) {
                    $number18 .= "
                        and pur_purchase_order.created_at >= '{$start_time}'
                        and pur_purchase_order.created_at <= '{$end_time}'
                    ";
                }else{
                    $number18 .= "
                        and pur_purchase_order.created_at >= '{$now_7}'
                        and pur_purchase_order.created_at <= '{$now}'
                    ";
                }
                $list['number_18'] = $connection->createCommand($number18)->queryScalar();

                //24小时完成订单数 19H~24H
                $number24 = "
                    select count(DISTINCT pur_purchase_order.pur_number) as num from pur_purchase_suggest,pur_purchase_order,pur_purchase_demand
                    where pur_purchase_demand.pur_number = pur_purchase_order.pur_number
                    and pur_purchase_demand.demand_number = pur_purchase_suggest.demand_number
                    and pur_purchase_order.purchase_type = 2
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) >= 0
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) <= 1440
                    and pur_purchase_order.buyer='{$val}'
                ";
                if ($start_time && $end_time) {
                    $number24 .= "
                        and pur_purchase_order.created_at >= '{$start_time}'
                        and pur_purchase_order.created_at <= '{$end_time}'
                    ";
                }else{
                    $number24 .= "
                        and pur_purchase_order.created_at >= '{$now_7}'
                        and pur_purchase_order.created_at <= '{$now}'
                    ";
                }
                $list['number_24'] = $connection->createCommand($number24)->queryScalar();

                //36小时完成订单数 25H~36H
                $number36 = "
                    select count(DISTINCT pur_purchase_order.pur_number) as num from pur_purchase_suggest,pur_purchase_order,pur_purchase_demand
                    where pur_purchase_demand.pur_number = pur_purchase_order.pur_number
                    and pur_purchase_demand.demand_number = pur_purchase_suggest.demand_number
                    and pur_purchase_order.purchase_type = 2
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) >= 0
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) <= 2160
                    and pur_purchase_order.buyer='{$val}'
                ";
                if ($start_time && $end_time) {
                    $number36 .= "
                        and pur_purchase_order.created_at >= '{$start_time}'
                        and pur_purchase_order.created_at <= '{$end_time}'
                    ";
                }else{
                    $number36 .= "
                        and pur_purchase_order.created_at >= '{$now_7}'
                        and pur_purchase_order.created_at <= '{$now}'
                    ";
                }
                $list['number_36'] = $connection->createCommand($number36)->queryScalar();

                //48小时完成订单数 37H~48H
                $number48 = "
                    select count(DISTINCT pur_purchase_order.pur_number) as num from pur_purchase_suggest,pur_purchase_order,pur_purchase_demand
                    where pur_purchase_demand.pur_number = pur_purchase_order.pur_number
                    and pur_purchase_demand.demand_number = pur_purchase_suggest.demand_number
                    and pur_purchase_order.purchase_type = 2
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) >= 0
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) <= 2880
                    and pur_purchase_order.buyer='{$val}'
                ";
                if ($start_time && $end_time) {
                    $number48 .= "
                        and pur_purchase_order.created_at >= '{$start_time}'
                        and pur_purchase_order.created_at <= '{$end_time}'
                    ";
                }else{
                    $number48 .= "
                        and pur_purchase_order.created_at >= '{$now_7}'
                        and pur_purchase_order.created_at <= '{$now}'
                    ";
                }
                $list['number_48'] = $connection->createCommand($number48)->queryScalar();

                //72小时完成订单数 49H~72H
                $number72 = "
                    select count(DISTINCT pur_purchase_order.pur_number) as num from pur_purchase_suggest,pur_purchase_order,pur_purchase_demand
                    where pur_purchase_demand.pur_number = pur_purchase_order.pur_number
                    and pur_purchase_demand.demand_number = pur_purchase_suggest.demand_number
                    and pur_purchase_order.purchase_type = 2
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) >= 0
                    and timestampdiff(minute,pur_purchase_suggest.created_at,pur_purchase_order.created_at) <= 4320
                    and pur_purchase_order.buyer='{$val}'
                ";
                if ($start_time && $end_time) {
                    $number72 .= "
                        and pur_purchase_order.created_at >= '{$start_time}'
                        and pur_purchase_order.created_at <= '{$end_time}'
                    ";
                }else{
                    $number72 .= "
                        and pur_purchase_order.created_at >= '{$now_7}'
                        and pur_purchase_order.created_at <= '{$now}'
                    ";
                }
                $list['number_72'] = $connection->createCommand($number72)->queryScalar();

            } else {
                $list['total'] = 0;
                $list['number_18'] = 0;
                $list['number_24'] = 0;
                $list['number_36'] = 0;
                $list['number_48'] = 0;
                $list['number_72'] = 0;
            }

            $arrList[$val] = $list;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'pager' => $pagination,
            'list' => $arrList,
            'total' => count($arrList)
        ]);
        //采购下单完成率-----结束
    }

}
