<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/10
 * Time: 17:34
 */

namespace app\controllers;
use app\config\Vhelper;
use app\models\ArrivalRecord;
use app\models\LowerRateStatistics;
use app\models\LowerRateStatisticsSearch;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderPay;
use app\models\PurchaseSuggest;
use app\models\PurchaseSuggestHistory;
use app\models\PurchaseWarehouseAbnormal;
use app\models\PurchaseWarningStatus;
use app\models\StockOwes;
use app\models\TablesChangeLog;
use app\services\BaseServices;
use app\services\PurchaseSuggestQuantityServices;
use yii\data\Pagination;
use yii\filters\VerbFilter;
use Yii;

class LowerRateStatisticsController extends BaseController
{
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
     * @desc优化下单率统计
     * @author SunnyLin 2018-10-31
     * */
    public function actionIndex()
    {
        set_time_limit(0);
        $searchModel = new LowerRateStatisticsSearch();
        $map=Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($map);


        if (!empty($dataProvider)) {
            $query = $dataProvider->orderBy('create_time DESC');
        } else {
            $query = LowerRateStatistics::find()
                ->orderBy('create_time DESC');
        }

        $buyer      = BaseServices::getBuyer();
        $page       = count($buyer);
        $count      = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $page, 'pageParam' => 'ym']);

        $orderList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $list      = [];
        foreach ($orderList as $m) {
            $time          = date('Y-m-d', strtotime($m['create_time']));
            $list[$time][] = $m;
        }

        $start_time=isset($map['LowerRateStatisticsSearch']['start_time'])?$map['LowerRateStatisticsSearch']['start_time'].' 00:00:00':'';
        $end_time=isset($map['LowerRateStatisticsSearch']['end_time'])?$map['LowerRateStatisticsSearch']['end_time'].' 23:59:59':'';
        //指定时间到货数统计
        $arrival = PurchaseOrder::find()->alias('A')->select('A.buyer,date(A.created_at) created_at,SUM(B.delivery_qty)  as arrival_qty')
            ->innerJoin('pur_arrival_record B','A.pur_number=B.purchase_order_no')
            ->where(['between', 'A.created_at', $start_time, $end_time])
            ->andWhere(['A.purchase_type' => 1])
            ->andWhere(['A.create_type' => 1])
            ->andWhere(['in', 'A.purchas_status', ['3', '5', '6', '7', '8', '9']])
            ->groupBy('A.buyer,date(A.created_at)')
            ->asArray()->all();
        $arrivalCount=[];
        foreach($arrival as $val){
            $arrivalCount[$val['created_at']][$val['buyer']]=$val['arrival_qty'];
        }
        unset($arrival);
        //已完成采购订单数
        $success_qty= PurchaseOrderItems::find()->alias('A')
            ->select('B.buyer,date(B.created_at) created_at,sum(A.ctq) AS success_qty,count(DISTINCT A.sku) AS success_sku')
            ->innerJoin('pur_purchase_order B','A.pur_number=B.pur_number')
            ->where(['between', 'B.created_at', $start_time, $end_time])
            ->andWhere(['B.purchase_type' => 1])
            ->andWhere(['in', 'B.purchas_status', ['3', '5', '6', '7', '8', '9']])
            ->groupBy('B.buyer,date(B.created_at)')
            ->asArray()->all();
        $successCount=[];
        foreach($success_qty as $val){
            $successCount[$val['created_at']][$val['buyer']]['success_qty']=$val['success_qty'];
            $successCount[$val['created_at']][$val['buyer']]['success_sku']=$val['success_sku'];
        }
        unset($success_qty);
        //po数量
        $po_number=PurchaseOrder::find()
                ->select('buyer,date(audit_time) created_at,count(DISTINCT pur_purchase_order.pur_number) as po_num')
                ->where(['between', 'audit_time', $start_time, $end_time])
                ->andWhere(['in', 'purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
                ->andWhere(['purchase_type' => 1])
                ->groupBy('buyer,date(audit_time)')
                ->asArray()->all();
        $poCount=[];
        foreach($po_number as $val){
            $poCount[$val['created_at']][$val['buyer']]=$val['po_num'];
        }
        unset($po_number);
        //sku
        $sku_number=PurchaseOrderItems::find()->alias('A')
            ->select('B.buyer,date(B.audit_time) created_at,count(A.pur_number) as sku_num')
            ->innerJoin('pur_purchase_order B','A.pur_number=B.pur_number')
            ->where(['between', 'B.created_at', $start_time, $end_time])
            ->andWhere(['B.purchase_type' => 1])
            ->andWhere(['in', 'B.purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
            ->groupBy('B.buyer,date(B.audit_time)')
            ->asArray()->all();
        $skuCount=[];
        foreach($sku_number as $val){
            $skuCount[$val['created_at']][$val['buyer']]=$val['sku_num'];
        }
        unset($sku_number);
        //异常单
        $exp_number  = PurchaseWarehouseAbnormal::find()->alias('A')
            ->select('B.buyer,date(B.audit_time) created_at,COUNT(B.pur_number) as pur_number')
            ->leftJoin('pur_purchase_order B','A.purchase_order_no=B.pur_number')
            ->where(['in', 'B.purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
            ->andWhere(['B.purchase_type' => 1])
            ->andWhere(['between', 'B.audit_time', $start_time, $end_time])
            ->groupBy('B.buyer,date(B.audit_time)')
            ->asArray()->all();
        $expCount=[];
        foreach($exp_number as $val){
            $expCount[$val['created_at']][$val['buyer']]=$val['pur_number'];
        }
        unset($exp_number);
        //处理的异常订单
        $handler_exp_number  = PurchaseWarehouseAbnormal::find()->alias('A')
            ->select('B.buyer,date(B.audit_time) created_at,COUNT(B.pur_number) as pur_number')
            ->leftJoin('pur_purchase_order B','A.purchase_order_no=B.pur_number')
            ->where(['in', 'B.purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
            ->andWhere(['B.purchase_type' => 1])
            ->andWhere(['between', 'B.audit_time', $start_time, $end_time])
            ->andWhere(['A.is_handler' => 1])
            ->groupBy('B.buyer,date(B.audit_time)')
            ->asArray()->all();
        $handlerExpCount=[];
        foreach($handler_exp_number as $val){
            $handlerExpCount[$val['created_at']][$val['buyer']]=$val['pur_number'];
        }
        unset($handler_exp_number);
        //缺货数量
        $left_stock_sql=" select buyer,m.created_at,sum(n.left_stock) as left_stock from   
                        (SELECT distinct buyer,date(audit_time) created_at,sku FROM pur_purchase_order_items A  
                    INNER JOIN `pur_purchase_order` B ON A.pur_number=B.pur_number 
                    WHERE B.`purchas_status` IN ('3','5','6','7','8','9','10')
                    AND  (B.`purchase_type`='1')
                    AND (`audit_time`  BETWEEN '".$start_time."' and '".$end_time."'))m
                   inner join pur_stock_owes n on n.sku=m.sku where `warehouse_code`='SZ_AA'   group by buyer,m.created_at";

        $left_stock = Yii::$app->db->createCommand($left_stock_sql)->queryAll();
        /*$left_stock=PurchaseOrderItems::find()->alias('A')
            ->select('B.buyer,date(B.audit_time) created_at, sum(C.left_stock) as left_stock')
            ->rightJoin('pur_purchase_order B','A.pur_number=B.pur_number')
            ->rightJoin('pur_stock_owes C'," C.sku=A.sku AND C.warehouse_code='SZ_AA'")
            ->where(['between', 'B.audit_time', $start_time, $end_time])
            ->andWhere(['B.purchase_type' => 1])
            ->andWhere(['in', 'B.purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
            ->groupBy('B.buyer,date(B.audit_time)')
            ->asArray()->all();*/
        $leftStockCount=[];
        foreach($left_stock as $val){
            $leftStockCount[$val['created_at']][$val['buyer']]=$val['left_stock'];
        }
        unset($left_stock);
        //在途数量
        /*$on_way_stock=PurchaseOrderItems::find()->alias('A')
            ->select('B.buyer,date(B.audit_time) created_at, sum(on_way_stock) AS on_way_stock')
            ->innerJoin('pur_purchase_order B','A.pur_number=B.pur_number')
            ->rightJoin('pur_stock C'," C.sku=A.sku AND C.warehouse_code='SZ_AA'")
            ->where(['between', 'B.audit_time', $start_time, $end_time])
            ->andWhere(['B.purchase_type' => 1])
            ->andWhere(['in', 'B.purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
            //->andWhere(['C.warehouse_code'=>'SZ_AA'])
            ->groupBy('B.buyer,date(B.audit_time)')
            ->asArray()->all();
        ;*/
        $onway_stock_sql=" select buyer,m.created_at,sum(n.on_way_stock) AS on_way_stock from   
                        (SELECT distinct buyer,date(audit_time) created_at,sku FROM pur_purchase_order_items A  
                    INNER JOIN `pur_purchase_order` B ON A.pur_number=B.pur_number 
                    WHERE B.`purchas_status` IN ('3','5','6','7','8','9','10')
                    AND  (B.`purchase_type`='1')
                    AND (`audit_time`  BETWEEN '".$start_time."' and '".$end_time."'))m
                   inner join pur_stock n on n.sku=m.sku where `warehouse_code`='SZ_AA'   group by buyer,m.created_at";

        $on_way_stock = Yii::$app->db->createCommand($onway_stock_sql)->queryAll();
        $onWayStockCount=[];
        foreach($on_way_stock as $val){
            $onWayStockCount[$val['created_at']][$val['buyer']]=$val['on_way_stock'];
        }
        unset($on_way_stock);
        foreach ($list as $lk => $lv) {
            foreach ($lv as $k => $v) {

                //已到货采购数量（系统）
                $createAt=substr($v['create_time'],0,10);
                $list[$lk][$k]['arrival_qty']=isset($arrivalCount[$createAt][$v['buyer']])
                    ?$arrivalCount[$createAt][$v['buyer']]:0;
                //已完成状态采购数量（ok）
                $list[$lk][$k]['success_qty'] = isset($successCount[$createAt][$v['buyer']]['success_qty'])? $successCount[$createAt][$v['buyer']]['success_qty']: 0;
                $list[$lk][$k]['success_sku'] = isset($successCount[$createAt][$v['buyer']]['success_sku'])? $successCount[$createAt][$v['buyer']]['success_sku']: 0;
                //po数量
                $list[$lk][$k]['po_number']=isset($poCount[$createAt][$v['buyer']]) ?$poCount[$createAt][$v['buyer']]:0;

                $list[$lk][$k]['sku_number'] = isset($skuCount[$createAt][$v['buyer']]) ?$skuCount[$createAt][$v['buyer']]:0;

                //异常单数量
                $list[$lk][$k]['exp_number'] = isset($expCount[$createAt][$v['buyer']]) ?$expCount[$createAt][$v['buyer']]:0;
                $list[$lk][$k]['handler_exp_number'] = isset($handlerExpCount[$createAt][$v['buyer']]) ?$handlerExpCount[$createAt][$v['buyer']]:0;
                //缺货数量
                $list[$lk][$k]['left_stock'] = isset($leftStockCount[$createAt][$v['buyer']]) ?$leftStockCount[$createAt][$v['buyer']]:0;
                $list[$lk][$k]['on_way_stock'] = isset($onWayStockCount[$createAt][$v['buyer']]) ?$onWayStockCount[$createAt][$v['buyer']]:0;
            }
        }
        unset($arrivalCount,$successCount,$poCount,$skuCount,$expCount,$handlerExpCount,$leftStockCount,$onWayStockCount);
        //付款超时
        $total = PurchaseOrderItems::find()
            ->leftJoin(PurchaseOrder::tableName(),'pur_purchase_order_items.pur_number=pur_purchase_order.pur_number')
            ->leftJoin(PurchaseOrderPay::tableName(),'pur_purchase_order_pay.pur_number=pur_purchase_order.pur_number')
            ->leftJoin(PurchaseWarningStatus::tableName(),'pur_purchase_order_items.pur_number=pur_purchase_warning_status.pur_number and pur_purchase_order_items.sku=pur_purchase_warning_status.sku')
            ->where(['pur_purchase_order.account_type'=>2])
            ->andFilterWhere(['in','pur_purchase_order.purchas_status',['5','7','8']])
            ->andFilterWhere(['in','pur_purchase_order.purchase_type',['1']])
            ->andFilterWhere(['!=',"ifnull(ctq, 0) - ifnull(cty, 0)",0])
            ->andFilterWhere(['<>','pur_purchase_order_pay.pay_status',0])
            ->andFilterWhere(['pur_purchase_order_pay.payer_time'=>''])
            ->andFilterWhere(['pur_purchase_warning_status.warn_status'=>2])
            ->groupBy('pur_purchase_order_pay.pur_number')
            ->count();

        return $this->render('index', [
            'list' => $list,
            'pager' => $pagination,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'total' => $total
        ]);
    }
    public function actionIndex2()
    {
        set_time_limit(0);
        $searchModel = new LowerRateStatisticsSearch();
        $map=Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($map);


        if (!empty($dataProvider)) {
            $query = $dataProvider->orderBy('create_time DESC');
        } else {
            $query = LowerRateStatistics::find()
                ->orderBy('create_time DESC');
        }

        $buyer      = BaseServices::getBuyer();
        $page       = count($buyer);
        $count      = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $page, 'pageParam' => 'ym']);

        $orderList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $list      = [];
        foreach ($orderList as $m) {
            $time          = date('Y-m-d', strtotime($m['create_time']));
            $list[$time][] = $m;
        }

        foreach ($list as $lk => $lv) {
            foreach ($lv as $k => $v) {
                //已到货采购数量（系统）
                $start_time  = date('Y-m-d 00:00:00', strtotime($lk));
                $end_time    = date('Y-m-d 23:59:59', strtotime($lk));
                $order_query = PurchaseOrder::find()
                    ->where(['between', 'created_at', $start_time, $end_time])
                    ->andWhere(['in', 'buyer', $v['buyer']])
                    ->andWhere(['purchase_type' => 1])
                    ->andWhere(['create_type' => 1])
                    ->andWhere(['in', 'purchas_status', ['3', '5', '6', '7', '8', '9']])
                    ->asArray()->all();
                //                        ->createCommand()->getRawSql(); Vhelper::dump($order_query);
                $order_query = array_column($order_query, 'pur_number');

                //到货数量（ok）
                $query = ArrivalRecord::find()->alias('ar');
                $query->select(['arrival_qty' => 'SUM(delivery_qty)']); //采购员 已到货采购数量
                $query->where(['in', 'purchase_order_no', $order_query]);
                $arrival_sql = $query->createCommand()->getRawSql();
                //                    Vhelper::dump($arrival_sql);
                $arrival                      = $query->asArray()->all();
                $list[$lk][$k]['arrival_qty'] = !empty($arrival[0]['arrival_qty']) ? $arrival[0]['arrival_qty'] : 0;


                //已完成状态采购数量（ok）
                $pur_numbers = "SELECT pur_number FROM pur_purchase_order WHERE buyer='{$v['buyer']}' ||
 '' AND created_at between '{$start_time}' AND '{$end_time}' AND purchase_type=1 AND  purchas_status IN ('3','5','6','7','8','9')";
                $success_qty_sql = "SELECT sum(ctq) AS success_qty FROM pur_purchase_order_items
                        WHERE pur_number IN ({$pur_numbers})";
                //Vhelper::dump($success_qty_sql);
                $success_qty                  = Yii::$app->db->createCommand($success_qty_sql)->queryAll();
                $list[$lk][$k]['success_qty'] = !empty($success_qty[0]['success_qty']) ? $success_qty[0]['success_qty'] : 0;


                //已完成状态SKU数（ok）
                $pur_numbers                  = "SELECT pur_number FROM pur_purchase_order WHERE buyer='{$v['buyer']}' 
AND created_at between '{$start_time}' AND '{$end_time}' AND purchase_type=1 AND  purchas_status IN ('3','5','6','7','8','9')";
                $success_sku_sql              = "SELECT count(DISTINCT sku) AS success_sku FROM pur_purchase_order_items WHERE pur_number in ({$pur_numbers})";
                $success_sku                  = Yii::$app->db->createCommand($success_sku_sql)->queryAll();
                $list[$lk][$k]['success_sku'] = !empty($success_sku[0]['success_sku']) ? $success_sku[0]['success_sku'] : 0;

                //po数量
                $po_number = "
                    SELECT count(DISTINCT pur_purchase_order.pur_number) as po_num
                    FROM `pur_purchase_order` WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$v['buyer']}') AND (`pur_purchase_order`.`purchase_type`='1') AND (`audit_time` BETWEEN '{$start_time}' AND '{$end_time}');
                ";

                $po_number = Yii::$app->db->createCommand($po_number)->queryOne();
                $list[$lk][$k]['po_number'] = !empty($po_number['po_num']) ? $po_number['po_num'] : 0;

                //sku数量
                $sku_number = "
                    SELECT count(*) as sku_num FROM pur_purchase_order_items WHERE pur_number in(
                    SELECT pur_number FROM `pur_purchase_order` WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$v['buyer']}') and (`pur_purchase_order`.`purchase_type`='1')
                    AND (`audit_time` BETWEEN '{$start_time}' AND '{$end_time}'));
                ";
                $sku_number                  = Yii::$app->db->createCommand($sku_number)->queryOne();
                $list[$lk][$k]['sku_number'] = !empty($sku_number['sku_num']) ? $sku_number['sku_num'] : 0;

                //异常单数量
                $exp_number  = PurchaseWarehouseAbnormal::find()
                    ->leftJoin(PurchaseOrder::tableName(), 'pur_purchase_warehouse_abnormal.purchase_order_no=pur_purchase_order.pur_number')
                    ->select('pur_purchase_order.pur_number')
                    ->where(['in', 'pur_purchase_order.purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
                    ->andWhere(['pur_purchase_order.buyer' => $v['buyer'], 'pur_purchase_order.purchase_type' => 1])
                    ->andWhere(['between', 'pur_purchase_order.audit_time', $start_time, $end_time])
                    ->count();
                $list[$lk][$k]['exp_number'] = $exp_number;


                //异常单数量
                $handler_exp_number = PurchaseWarehouseAbnormal::find()
                    ->leftJoin(PurchaseOrder::tableName(), 'pur_purchase_warehouse_abnormal.purchase_order_no=pur_purchase_order.pur_number')
                    ->select('pur_purchase_order.pur_number')
                    ->where(['in', 'pur_purchase_order.purchas_status', ['3', '5', '6', '7', '8', '9', '10']])
                    ->andWhere(['pur_purchase_order.buyer' => $v['buyer'], 'pur_purchase_order.purchase_type' => 1])
                    ->andWhere(['between', 'pur_purchase_order.audit_time', $start_time, $end_time])
                    ->andWhere(['pur_purchase_warehouse_abnormal.is_handler' => 1])
                    ->count();
                $list[$lk][$k]['handler_exp_number'] = $handler_exp_number;

                //缺货数量
                $sku_sql = "
                    SELECT sku FROM pur_purchase_order_items WHERE pur_number in(
                    SELECT pur_number FROM `pur_purchase_order` WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$v['buyer']}') and (`pur_purchase_order`.`purchase_type`='1')
                    AND (`audit_time` BETWEEN '{$start_time}' AND '{$end_time}'));
                ";
                $arr_sku = Yii::$app->db->createCommand($sku_sql)->queryAll();
                $arr_sku = array_map('array_shift', $arr_sku);

                if (count($arr_sku) > 0) {
                    $left_stock  = StockOwes::find()
                        ->alias('sl')
                        ->select(['left_stock' => 'sum(sl.left_stock)'])
                        ->where(['sl.warehouse_code' => 'SZ_AA'])
                        ->andWhere(['in', 'sl.sku', $arr_sku])
                        //->andWhere(['between', 'sl.statistics_date', $start_time, $end_time])
                        ->one();
                    $list[$lk][$k]['left_stock'] = !empty($left_stock->left_stock) ? $left_stock->left_stock : 0;
                } else {
                    $list[$lk][$k]['left_stock'] = 0;
                }

                //在途数
                if (count($arr_sku) > 0) {
                    $sku  = join("','", $arr_sku);
                    $on_way_stock = "
                        SELECT sum(on_way_stock) AS `on_way_stock` FROM `pur_stock` WHERE (`pur_stock`.`warehouse_code`='SZ_AA') AND (`sku` in ('{$sku}'));
                    ";
                    $on_way_stock  = Yii::$app->db->createCommand($on_way_stock)->queryOne();
                    $list[$lk][$k]['on_way_stock'] = !empty($on_way_stock['on_way_stock']) ? $on_way_stock['on_way_stock'] : 0;
                } else {
                    $list[$lk][$k]['on_way_stock'] = 0;
                }
            }
        }

        //付款超时
        $total = PurchaseOrderItems::find()
            ->leftJoin(PurchaseOrder::tableName(),'pur_purchase_order_items.pur_number=pur_purchase_order.pur_number')
            ->leftJoin(PurchaseOrderPay::tableName(),'pur_purchase_order_pay.pur_number=pur_purchase_order.pur_number')
            ->leftJoin(PurchaseWarningStatus::tableName(),'pur_purchase_order_items.pur_number=pur_purchase_warning_status.pur_number and pur_purchase_order_items.sku=pur_purchase_warning_status.sku')
            ->where(['pur_purchase_order.account_type'=>2])
            ->andFilterWhere(['in','pur_purchase_order.purchas_status',['5','7','8']])
            ->andFilterWhere(['in','pur_purchase_order.purchase_type',['1']])
            ->andFilterWhere(['!=',"ifnull(ctq, 0) - ifnull(cty, 0)",0])
            ->andFilterWhere(['<>','pur_purchase_order_pay.pay_status',0])
            ->andFilterWhere(['pur_purchase_order_pay.payer_time'=>''])
            ->andFilterWhere(['pur_purchase_warning_status.warn_status'=>2])
            ->groupBy('pur_purchase_order_pay.pur_number')
            ->count();

        return $this->render('index', [
            'list' => $list,
            'pager' => $pagination,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'total' => $total
        ]);
    }

    /**
     * 下单率统计--当天的记录
     * caigou.yibainetwork.com/lower-rate-statistics/total-lower-rate-statistics
     */
    public function actionTotalLowerRateStatistics()
    {
        $start_time = date('Y-m-d 00:00:00');
        $end_time   = date('Y-m-d 23:59:59');
        $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode();
        $warehouse_code_string = PurchaseSuggestQuantityServices::getSuggestWarehouseCode(1);

        //已完成
        $success_sql = $this->success($warehouse_code,$start_time,$end_time);
        //到货数量
        $arrival_sql = $this->arrival($start_time,$end_time);

        $total_sql = "SELECT c.buyer_id AS buyer_id,c.buyer AS buyer,COUNT(sku) AS total_sku,SUM(c.qty) AS total_qty FROM (SELECT buyer_id,buyer,sku,qty FROM `pur_purchase_suggest` WHERE 
(`warehouse_code` IN ({$warehouse_code_string})) AND (`qty` > 0) AND (`sku` != 'XJFH0000') AND (`purchase_type`=1) AND (`created_at` BETWEEN '{$start_time}' AND '{$end_time}') AND (`product_status` NOT IN ('0', '5', '6', '7', '100'))  
GROUP BY `sku`, `warehouse_code`) as c GROUP BY c.buyer";
//        Vhelper::dump($total_sql);
        $info = Yii::$app->db->createCommand($total_sql)->queryAll();
        $res = $this->saveLowerRateStatistics($info,$start=time(),$start_time,$end_time);
        return json_encode($res);
    }
    /**
     * 下单率统计--历史的记录
     * caigou.yibainetwork.com/lower-rate-statistics/total-lower-rate-statistics-history?begin=2018-06-25&end=2018-06-25
     */
    public function actionTotalLowerRateStatisticsHistory($begin=null,$end=null)
    {
        if (!empty($begin) && !empty($end)) {
            $begintime = strtotime($begin);
            $endtime = strtotime($end);
        } else {
            $begintime = time();
            $endtime   = time();
        }

        for ($start = $begintime; $start <= $endtime; $start += 24 * 3600) {
            $start_time = date('Y-m-d 00:00:00', $start);
            $end_time   = date('Y-m-d 23:59:59',$start);
            $warehouse_code = PurchaseSuggestQuantityServices::getSuggestWarehouseCode();
            $warehouse_code_string = PurchaseSuggestQuantityServices::getSuggestWarehouseCode(1);

            //已完成
            $success_sql = $this->success($warehouse_code,$start_time,$end_time);
            //到货数量
            $arrival_sql = $this->arrival($start_time,$end_time);

            $total_sql = "SELECT c.buyer_id AS buyer_id,c.buyer AS buyer,COUNT(sku) AS total_sku,SUM(c.qty) AS total_qty FROM (SELECT buyer_id,buyer,sku,qty FROM `pur_purchase_suggest_history` WHERE 
(`warehouse_code` IN ({$warehouse_code_string})) AND (`qty` > 0) AND (`sku` != 'XJFH0000') AND (`purchase_type`=1) AND (`created_at` BETWEEN '{$start_time}' AND '{$end_time}') AND (`product_status` NOT IN ('0', '5', '6', '7', '100'))  
GROUP BY `sku`, `warehouse_code`) as c GROUP BY c.buyer";
            $info = Yii::$app->db->createCommand($total_sql)->queryAll();
            $res = $this->saveLowerRateStatistics($info,$start=time(),$start_time,$end_time);
            return json_encode($res);
        }
    }
    /**
     * 已完成 -- 历史
     */
    public function successHistory($warehouse_code=null,$start_time=null,$end_time=null)
    {
        //已完成
        $query = PurchaseSuggestHistory::find();
        $query->select(['buyer','success_sku'=>'count(sku)','success_qty'=>'SUM(qty)']); //采购员 已完成状态SKU数 已完成状态采购数量
        $query->groupBy(['buyer']);
        $query->andWhere(['in','warehouse_code',$warehouse_code]);
        $query->andWhere(['>','qty',0]);
        $query->andWhere(['!=','sku','XJFH0000']);
        $query->andWhere(['in','purchase_type',1]);
        $query->andWhere(['between', 'created_at', $start_time, $end_time]);
        $query->andWhere(['=','state','2']);
        $query->orderBy('left_stock asc');
        $success_sql = $query->createCommand()->getRawSql();
        $success = $query->asArray()->all();
        return $success_sql;
    }
    /**
     * 已完成 -- 今天
     */
    public function success($warehouse_code=null,$start_time=null,$end_time=null)
    {
        //已完成
        $query = PurchaseSuggest::find();
        $query->select(['buyer','success_sku'=>'count(sku)','success_qty'=>'SUM(qty)']); //采购员 已完成状态SKU数 已完成状态采购数量
        $query->groupBy(['buyer']);
        $query->andWhere(['in','warehouse_code',$warehouse_code]);
        $query->andWhere(['>','qty',0]);
        $query->andWhere(['!=','sku','XJFH0000']);
        $query->andWhere(['in','purchase_type',1]);
        $query->andWhere(['between', 'created_at', $start_time, $end_time]);
        $query->andWhere(['=','state','2']);
        $query->andWhere(['=','purchase_type','1']);
        $query->orderBy('left_stock asc');
        $success_sql = $query->createCommand()->getRawSql();
        $success = $query->asArray()->all();
        return $success_sql;
    }
    /**
     * 已到货采购数量
     */
    public function arrival($start_time=null,$end_time=null)
    {
        //到货数量
        $query = ArrivalRecord::find()->alias('ar');
        $query->select(['buyer','arrival_qty'=>'SUM(delivery_qty)']); //采购员 已到货采购数量
        $query->leftJoin('pur_purchase_order po', '{{po}}.pur_number={{ar}}.purchase_order_no');
        $query->groupBy(['buyer']);
        $order_query = PurchaseOrder::find()->where(['between', 'created_at', $start_time, $end_time])->andWhere(['purchase_type'=>1])->asArray()->all();
        $order_query = array_column($order_query,'pur_number');
        $query->where(['in','purchase_order_no',$order_query]);
        $query->andWhere(['between', 'delivery_time', $start_time, $end_time]);
        $arrival_sql = $query->createCommand()->getRawSql();
        $arrival = $query->asArray()->all();
        return $arrival_sql;
    }
    /**
     * 保存
     */
    public function saveLowerRateStatistics($info,$start=null,$start_time,$end_time)
    {
        $buyer = [];
        if (empty($info)) {
            return ['meg'=>'没数据'];
        }
        foreach ($info as $k=>$v) {
            $lower_model = LowerRateStatistics::find()->where(['=','buyer',$v['buyer']])->andWhere(['between','create_time',$start_time,$end_time])->one();
            if (!empty($lower_model)) {
//                $lower_model->buyer        = $v['buyer'];
                $lower_model->buyer_id     = empty($v['buyer_id'])? 0 : $v['buyer_id'];
                $lower_model->total_sku    = empty($v['total_sku'])? 0 : $v['total_sku'];
                $lower_model->total_qty    = empty($v['total_qty'])? 0 : $v['total_qty'];
                $lower_model->success_sku  = empty($v['success_sku'])? 0 : $v['success_sku'];
                $lower_model->success_qty  = empty($v['success_qty'])? 0 : $v['success_qty'];
//                $lower_model->create_time  = $lower_model->create_time;
//                $lower_model->update_time  = date('Y-m-d H:i:s',time());
                $lower_model->arrival_qty  = empty($v['arrival_qty']) ? 0 : $v['arrival_qty'];
                $status = $lower_model->save(false);
            } else {
                $model = new LowerRateStatistics();
//            $model_order->load($v); //规则验证
                $model->buyer        = empty($v['buyer']) ? 0 : $v['buyer'];
                $model->buyer_id     = empty($v['buyer_id']) ? 0 : $v['buyer_id'];
                $model->total_sku    = empty($v['total_sku']) ? 0 : $v['total_sku'];
                $model->total_qty    = empty($v['total_qty']) ? 0 : $v['total_qty'];
                $model->success_sku  = empty($v['success_sku']) ? 0 : $v['success_sku'];
                $model->success_qty  = empty($v['success_qty']) ? 0 : $v['success_qty'];
                $model->create_time  = date('Y-m-d',strtotime($start_time));
                $model->update_time  = date('Y-m-d H:i:s',strtotime($start_time));
                $model->arrival_qty  = empty($v['arrival_qty']) ? 0 : $v['arrival_qty'];
                $status = $model->save(false);
            }

            if ($status == true) {
                $buyer[] = $v['buyer'];
            }
        }
        return $buyer;
    }
    /**
     * 删除
     */
    public  function actionDeleteOne($id)
    {
        //表修改日志-删除
        $change_content = "delete:删除id值为{$id}的记录";
        $change_data = [
            'table_name' => 'pur_lower_rate_statistics', //变动的表名称
            'change_type' => '3', //变动类型(1insert，2update，3delete)
            'change_content' => $change_content, //变更内容
        ];
        TablesChangeLog::addLog($change_data);
        $status = LowerRateStatistics::deleteAll(['id'=>$id]);
        if ($status) {
            Yii::$app->getSession()->setFlash('success', '恭喜你，删除成功');
        } else {
            Yii::$app->getSession()->setFlash('error','删除失败');
        }
        $this->redirect(Yii::$app->request->referrer);
    }


    /**
     * PHPExcel导出
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function actionExportCsv()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $create_time = Yii::$app->request->get('create_time');
        $buyer_id = Yii::$app->request->get('buyer_id');

        $map = [];
        $map['LowerRateStatisticsSearch']['buyer_id'] = $buyer_id;
        if($create_time){
            $map['LowerRateStatisticsSearch']['create_time'] = $create_time;
            if(strpos($create_time,' - ')){
                $arr = explode(' - ',$create_time);
                if(count($arr)>0){
                    if(isset($arr[0])){
                        $map['LowerRateStatisticsSearch']['start_time'] = $arr[0];
                    }
                    if(isset($arr[1])){
                        $map['LowerRateStatisticsSearch']['end_time'] = $arr[1];
                    }
                }
            }

        }

        $searchModel = new LowerRateStatisticsSearch();
        $dataProvider = $searchModel->search($map);

        if (!empty($dataProvider)) {
            $query = $dataProvider->orderBy('create_time DESC');
        } else {
            $query = LowerRateStatistics::find()
                ->orderBy('create_time DESC');
        }

        $buyer = BaseServices::getBuyer();
        $page = count($buyer);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $page, 'pageParam' => 'ym']);

        $orderList = $query->offset($pagination->offset)->limit($pagination->limit)->asArray()->all();
        $list = [];
        foreach($orderList as $m) {
            $time = date('Y-m-d',strtotime($m['create_time']));
            $list[$time][] = $m;
        }

        foreach ($list as $lk=>$lv) {
            foreach ($lv as $k=>$v) {
                //已到货采购数量（系统）
                $start_time = date('Y-m-d 00:00:00',strtotime($lk));
                $end_time = date('Y-m-d 23:59:59',strtotime($lk));
                $order_query = PurchaseOrder::find()
                    ->where(['between', 'created_at', $start_time, $end_time])
                    ->andWhere(['in','buyer',$v['buyer']])
                    ->andWhere(['purchase_type'=>1])
                    ->andWhere(['create_type'=>1])
                    ->andWhere(['in','purchas_status',['3','5','6','7','8','9']])
                    ->asArray()->all();
//                        ->createCommand()->getRawSql(); Vhelper::dump($order_query);
                $order_query = array_column($order_query,'pur_number');

                //到货数量（ok）
                $query = ArrivalRecord::find()->alias('ar');
                $query->select(['arrival_qty'=>'SUM(delivery_qty)']); //采购员 已到货采购数量
                $query->where(['in','purchase_order_no',$order_query]);
                $arrival_sql = $query->createCommand()->getRawSql();
                $arrival = $query->asArray()->all();
                $list[$lk][$k]['arrival_qty'] = !empty($arrival[0]['arrival_qty']) ? $arrival[0]['arrival_qty'] : 0;



                //已完成状态采购数量（ok）
                $pur_numbers = "SELECT pur_number FROM pur_purchase_order WHERE buyer='{$v['buyer']}' AND created_at between '{$start_time}' AND '{$end_time}' AND purchase_type=1 AND  purchas_status IN ('3','5','6','7','8','9')";
                $success_qty_sql = "SELECT sum(ctq) AS success_qty FROM pur_purchase_order_items
                        WHERE pur_number IN ({$pur_numbers})";
                $success_qty = Yii::$app->db->createCommand($success_qty_sql)->queryAll();
                $list[$lk][$k]['success_qty'] = !empty($success_qty[0]['success_qty']) ? $success_qty[0]['success_qty'] : 0;


                //已完成状态SKU数（ok）
                $pur_numbers = "SELECT pur_number FROM pur_purchase_order WHERE buyer='{$v['buyer']}' AND created_at between '{$start_time}' AND '{$end_time}' AND purchase_type=1 AND  purchas_status IN ('3','5','6','7','8','9')";
                $success_sku_sql = "SELECT count(DISTINCT sku) AS success_sku FROM pur_purchase_order_items WHERE pur_number in ({$pur_numbers})";
                $success_sku = Yii::$app->db->createCommand($success_sku_sql)->queryAll();
                $list[$lk][$k]['success_sku'] = !empty($success_sku[0]['success_sku']) ? $success_sku[0]['success_sku'] : 0;

                //po数量
                $po_number = "
                    SELECT count(DISTINCT pur_purchase_order.pur_number) as po_num
                    FROM `pur_purchase_order` WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$v['buyer']}') AND (`pur_purchase_order`.`purchase_type`='1') AND (`audit_time` BETWEEN '{$start_time}' AND '{$end_time}');
                ";

                $po_number = Yii::$app->db->createCommand($po_number)->queryOne();
                $list[$lk][$k]['po_number'] = !empty($po_number['po_num']) ? $po_number['po_num'] : 0;

                //sku数量
                $sku_number = "
                    SELECT count(*) as sku_num FROM pur_purchase_order_items WHERE pur_number in(
                    SELECT pur_number FROM `pur_purchase_order` WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$v['buyer']}') and (`pur_purchase_order`.`purchase_type`='1')
                    AND (`audit_time` BETWEEN '{$start_time}' AND '{$end_time}'));
                ";
                $sku_number = Yii::$app->db->createCommand($sku_number)->queryOne();
                $list[$lk][$k]['sku_number'] = !empty($sku_number['sku_num']) ? $sku_number['sku_num'] : 0;

                //异常单数量
                $exp_number = PurchaseWarehouseAbnormal::find()
                    ->leftJoin(PurchaseOrder::tableName(), 'pur_purchase_warehouse_abnormal.purchase_order_no=pur_purchase_order.pur_number')
                    ->select('pur_purchase_order.pur_number')
                    ->where(['in','pur_purchase_order.purchas_status',['3', '5', '6', '7', '8', '9', '10']])
                    ->andWhere(['pur_purchase_order.buyer'=>$v['buyer'],'pur_purchase_order.purchase_type'=>1])
                    ->andWhere(['between','pur_purchase_order.audit_time',$start_time, $end_time])
                    ->count();
                $list[$lk][$k]['exp_number'] = $exp_number;


                //异常单数量
                $handler_exp_number = PurchaseWarehouseAbnormal::find()
                    ->leftJoin(PurchaseOrder::tableName(), 'pur_purchase_warehouse_abnormal.purchase_order_no=pur_purchase_order.pur_number')
                    ->select('pur_purchase_order.pur_number')
                    ->where(['in','pur_purchase_order.purchas_status',['3', '5', '6', '7', '8', '9', '10']])
                    ->andWhere(['pur_purchase_order.buyer'=>$v['buyer'],'pur_purchase_order.purchase_type'=>1])
                    ->andWhere(['between','pur_purchase_order.audit_time',$start_time, $end_time])
                    ->andWhere(['pur_purchase_warehouse_abnormal.is_handler'=>1])
                    ->count();
                $list[$lk][$k]['handler_exp_number'] = $handler_exp_number;

                //缺货数量
                $sku_sql = "
                    SELECT sku FROM pur_purchase_order_items WHERE pur_number in(
                    SELECT pur_number FROM `pur_purchase_order` WHERE (`purchas_status` IN ('3', '5', '6', '7', '8', '9', '10')) 
                    AND (`pur_purchase_order`.`buyer`='{$v['buyer']}') and (`pur_purchase_order`.`purchase_type`='1')
                    AND (`audit_time` BETWEEN '{$start_time}' AND '{$end_time}'));
                ";
                $arr_sku = Yii::$app->db->createCommand($sku_sql)->queryAll();
                $arr_sku = array_map('array_shift',$arr_sku);

                if(count($arr_sku)>0) {
                    $left_stock                  = StockOwes::find()
                        ->alias('sl')
                        ->select(['left_stock' => 'sum(sl.left_stock)'])
                        ->where(['sl.warehouse_code' => 'SZ_AA'])
                        ->andWhere(['in', 'sl.sku', $arr_sku])
                        //->andWhere(['between', 'sl.statistics_date', $start_time, $end_time])
                        ->one();
                    $list[$lk][$k]['left_stock'] = !empty($left_stock->left_stock) ? $left_stock->left_stock : 0;
                }else{
                    $list[$lk][$k]['left_stock'] = 0;
                }

                //在途数
                if(count($arr_sku)>0) {
                    $sku  = join("','", $arr_sku);
                    $on_way_stock = "
                        SELECT sum(on_way_stock) AS `on_way_stock` FROM `pur_stock` WHERE (`pur_stock`.`warehouse_code`='SZ_AA') AND (`sku` in ('{$sku}'));
                    ";
                    $on_way_stock  = Yii::$app->db->createCommand($on_way_stock)->queryOne();
                    $list[$lk][$k]['on_way_stock'] = !empty($on_way_stock['on_way_stock']) ? $on_way_stock['on_way_stock'] : 0;
                }else{
                    $list[$lk][$k]['on_way_stock'] = 0;
                }
            }
        }

        $objectPHPExcel = new \PHPExcel();
        $objectPHPExcel->setActiveSheetIndex(0);
        $n = 0;

        //表格头的输出
        $cell_value = ['创建日期','采购员','SKU下单率','采购数量下单率','到货率','PO数','SKU数','异常单数','处理的异常单数','缺货数','在途数'];
        foreach ($cell_value as $k => $v) {
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($k+65) . '1',$v);
        }
        //设置数据水平靠左和垂直居中
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $objectPHPExcel->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $all_count = count($list);
        $sku_total_res = 0;
        $ctq_total_res = 0;
        $arrival_total_res = 0;

        $total_po_number = 0;
        $total_sku_number = 0;
        $total_exp_number = 0;
        $total_handler_exp_number = 0;
        $total_left_stock = 0;
        $total_on_way_stock = 0;
        $po_number_res = 0;
        $sku_number_res = 0;
        $exp_number_res = 0;
        $handler_exp_number_res = 0;
        $left_stock_res = 0;
        $on_way_stock_res = 0;
        foreach($list as $lk => $lv) {
            $r = count($lv);
            $sku_lower_rate_total = 0;
            $ctq_lower_rate_total = 0;
            $arrival_rate_total   = 0;

            $sku_total = 0;
            $ctq_total = 0;
            $arrival_total = 0;
            $total_po_number = 0;
            $total_sku_number = 0;
            $total_exp_number = 0;
            $total_handler_exp_number = 0;
            $total_left_stock = 0;
            $total_on_way_stock = 0;
            foreach($lv as $k => $v){
                $sku_lower_rate_total += ($v['total_sku']==0 ? 0 : $v['success_sku']/$v['total_sku']);
                $ctq_lower_rate_total += ($v['total_qty']==0 ? 0 : $v['success_qty']/$v['total_qty']);
                $arrival_rate_total += ($v['success_qty']==0 ? 0 : $v['arrival_qty']/$v['success_qty']);
                $total_po_number += $v['po_number'];
                $total_sku_number += $v['sku_number'];
                $total_exp_number += $v['exp_number'];
                $total_handler_exp_number += $v['handler_exp_number'];
                $total_left_stock += $v['left_stock'];
                $total_on_way_stock += $v['on_way_stock'];
            }
            //数据处理
            $sku_total = round(($sku_lower_rate_total/$r) * 100,2);
            $ctq_total = round(($ctq_lower_rate_total/$r) * 100,2);
            $arrival_total = round(($arrival_rate_total/$r) * 100,2);

            $sku_total_res += $sku_total;
            $ctq_total_res += $ctq_total;
            $arrival_total_res += $arrival_total;
            $po_number_res += $total_po_number;
            $sku_number_res += $total_sku_number;
            $exp_number_res += $total_exp_number;
            $handler_exp_number_res += $total_handler_exp_number;
            $left_stock_res += $total_left_stock;
            $on_way_stock_res += $total_on_way_stock;

            //组装总计
            $count = [];
            $count['sku_total'] = $sku_total;
            $count['ctq_total'] = $ctq_total;
            $count['arrival_total'] = $arrival_total;
            $count['total_po_number'] = $total_po_number;
            $count['total_sku_number'] = $total_sku_number;
            $count['total_exp_number'] = $total_exp_number;
            $count['total_handler_exp_number'] = $total_handler_exp_number;
            $count['total_left_stock'] = $total_left_stock;
            $count['total_on_way_stock'] = $total_on_way_stock;
            $lv['count'] = $count;


            foreach($lv as $k => $v){
                if($k === 'count'){
                    $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,'');
                    $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,'总计');
                    $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,$v['sku_total'] . "%");
                    $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+2) ,$v['ctq_total'] . "%");
                    $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+2) ,$v['arrival_total'] . "%");
                    $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+2) ,$v['total_po_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+2) ,$v['total_sku_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+2) ,$v['total_exp_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+2) ,$v['total_handler_exp_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+2) ,$v['total_left_stock']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+2) ,$v['total_on_way_stock']);
                    $n = $n +1;
                }else{
                    $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,$lk);
                    $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,$v['buyer']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,round(($v['total_sku'] ==0 ? 0 : $v['success_sku']/$v['total_sku'])* 100,2) . "%");
                    $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+2) ,round(( $v['total_qty']==0 ? 0 : $v['success_qty']/$v['total_qty']) * 100,2) . "%");
                    $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+2) ,round(($v['success_qty']==0 ? 0 : $v['arrival_qty']/$v['success_qty']) * 100,2) . "%");
                    $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+2) ,$v['po_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+2) ,$v['sku_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+2) ,$v['exp_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+2) ,$v['handler_exp_number']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+2) ,$v['left_stock']);
                    $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+2) ,$v['on_way_stock']);
                    $n = $n +1;
                }
            }
        }

        $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,'');
        $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,'总计');
        $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,round(($sku_total_res/$all_count),2) . "%");
        $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+2) ,round(($ctq_total_res/$all_count),2) . "%");
        $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+2) ,round(($arrival_total_res/$all_count), 2) . "%");
        $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+2) ,$po_number_res);
        $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+2) ,$sku_number_res);
        $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+2) ,$exp_number_res);
        $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+2) ,$handler_exp_number_res);
        $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+2) ,$left_stock_res);
        $objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+2) ,$on_way_stock_res);

        for ($i = 65; $i<77; $i++) {
            $objectPHPExcel->getActiveSheet()->getColumnDimension(chr($i))->setWidth(15);
            $objectPHPExcel->getActiveSheet()->getStyle( chr($i) . "1")->getFont()->setBold(true);
        }
        $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

        //设置样式
        $objectPHPExcel->getActiveSheet()->getStyle('A1:E'.($n+2))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objectPHPExcel->getActiveSheet()->getStyle('C2:E'.($n+2))->getNumberFormat()->setFormatCode(\PHPExcel_Cell_DataType::TYPE_NUMERIC);
        ob_end_clean();
        ob_start();
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Type : application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.'下单率统计-'.date("Y年m月j日").'.xls"');
        $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
        $objWriter->save('php://output');
        die;
    }
}