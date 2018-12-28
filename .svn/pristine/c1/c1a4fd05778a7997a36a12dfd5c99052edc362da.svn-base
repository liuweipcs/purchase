<?php

namespace app\controllers;

use app\models\Supplier;
use app\models\SupplierKpiCaculte;
use app\models\SupplierSettlement;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrder;
use app\api\v1\models\ApiPageCircle;
use app\models\SkuTotaPage;
use m35\thecsv\theCsv;
use Yii;
use yii\filters\VerbFilter;
use app\config\MyExcel;
use app\config\Vhelper;
use yii\helpers\Json;


/**
 * Created by PhpStorm.
 * User: wr
 * Date: 2017/12/27
 * Time: 16:28
 */
class SupplierKpiCheckController extends BaseController
{

    /**
     * 供应商结算方式列表
     */
    public function  actionIndex()
    {
        $searchModel = new SupplierKpiCaculte();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionExport(){
        set_time_limit(0);
        $supplier_code = Yii::$app->request->getQueryParam('supplier_code');
        $month         = Yii::$app->request->getQueryParam('month');
        $model = SupplierKpiCaculte::find()
            ->andFilterWhere(['supplier_code'=>$supplier_code])
            ->andFilterWhere(['month'=>$month?date('Y-m-01',strtotime($month)):date('Y-m-01',time())])
            ->all();

        $table = [
            '供应商名称',
            '采购金额',
            '统计月份',
            '账期',
            'PO数量',
            'SKU总下单批次',
            'SKU准交批次',
            'SKU准交率',
            'SKU异常批次',
            'SKU异常率',
            'SKU海外异常批次',
            'SKU海外异常率',
            'SKU降价金额',
            'SKU降价率',
            'SKU涨价金额',
            'SKU涨价率',
        ];

        $table_head = [];
        foreach($model as $k=>$v)
        {
            $table_head[$k][] = !empty($v->supplier) ? $v->supplier->supplier_name : '';
            $table_head[$k][] = $v->purchase_total;
            $table_head[$k][] = date('Y-m',strtotime($v->month));
            $table_head[$k][] = $v->settlement;
            $table_head[$k][] = $v->purchase_times;
            $table_head[$k][] = $v->purchase_total;
            $table_head[$k][] = $v->sku_purchase_times;
            $table_head[$k][] = $v->punctual_rate ==0 ? 0 : $v->punctual_rate.'%';
            $table_head[$k][] = $v->sku_exception_times;
            $table_head[$k][] = $v->excep_rate ==0 ? 0 : $v->excep_rate.'%';
            $table_head[$k][] = 0;
            $table_head[$k][] = 0;
            $table_head[$k][] = $v->sku_down_total;
            $table_head[$k][] = ($v->sku_down_rate*100).'%';
            $table_head[$k][] = $v->sku_up_total;
            $table_head[$k][] = ($v->sku_up_rate*100).'%';
        }

        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
    }
    /**
     * 将供应商的采购金额推送给erp
     */
    public function actionPushPurchasePrice()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $return_json_data = [];
        $lastDays = $this->getlastMonthDays();
        $lastday = array_shift($lastDays);

        $supplier_codes = \Yii::$app->request->getQueryParam('supplier_codes',false);
        if (!empty($supplier_codes)) {
            $supplier_codes = explode(',',$supplier_codes);
            $supplierKpiCaculteInfo = SupplierKpiCaculte::find()
                ->alias('skc')
                ->select('skc.supplier_code, month, purchase_total,s.supplier_name,s.store_link')
                ->leftJoin('pur_supplier s', 's.supplier_code=skc.supplier_code and status=1')
                ->where(['month'=>$lastday])
                ->andWhere(['in', 'skc.supplier_code', $supplier_codes])
                ->asArray()
                ->all();
        } else {
            $supplierKpiCaculteInfo = SupplierKpiCaculte::find()
                ->alias('skc')
                ->select('skc.supplier_code, month, purchase_total,s.supplier_name,s.store_link')
                ->leftJoin('pur_supplier s', 's.supplier_code=skc.supplier_code and status=1')
                ->where(['month'=>$lastday])
                ->asArray()
                ->all();
            $supplier_codes = array_unique(array_column($supplierKpiCaculteInfo, 'supplier_code'));
        }

        $lastDays = $this->getlastMonthDays(false,true);
        $startDay = array_shift($lastDays);
        $endDay = end($lastDays);
        $orderInfo = PurchaseOrder::find()
            ->alias('po')
            ->select('po.supplier_code,count(poi.id) as sku_count')
            ->leftJoin(PurchaseOrderItems::tableName() ." poi","poi.pur_number = po.pur_number")
            ->where(['in', 'po.purchas_status', [5, 6, 7, 8, 9]])
            ->andWhere(['between', 'po.created_at', $startDay, $endDay])
            ->andWhere(['in', 'po.supplier_code', $supplier_codes])
            ->groupBy('po.supplier_code')
            ->asArray()->all();
        unset($supplier_codes);

        //采购单处理
        $order_key = array_column($supplierKpiCaculteInfo,'supplier_code');  //键值
        $newSupplierKpiCaculteInfo = array_combine($order_key,$supplierKpiCaculteInfo);
        unset($supplierKpiCaculteInfo);

        foreach ($orderInfo as $k => $v) $newSupplierKpiCaculteInfo[$v['supplier_code']]['sku_count'] = $v['sku_count'];

        if (!empty($newSupplierKpiCaculteInfo)) $return_json_data = json_encode($newSupplierKpiCaculteInfo);
        unset($newSupplierKpiCaculteInfo);

        $curl   = new \linslin\yii2\curl\Curl();
        $url    = Yii::$app->params['ERP_URL'] . '/services/products/product/Getproviderdeal';
        // 执行推送数据
        $s = $curl->setPostParams([
            'purchase_price' => $return_json_data, //Json::encode($push_list_data),
        ])->post($url);
        unset($return_json_data);

        //验证json
        $sb = Vhelper::is_json($s);
        try {
            //验证json
            if(!$sb){
                //保存到日志
                $log = ['type'=>45, 'pid'=> null, 'pur_number'=>'', 'module'=>'应商的采购金额推送erp-请检查json', 'content'=>''];
                Vhelper::setOperatLog($log);
                unset($log);
                Vhelper::dump($s);
            } else {
                $_result = Json::decode($s);
                if (!empty($_result)) {
                    //保存到日志
                    $log = ['type'=>45, 'pid'=> null, 'pur_number'=>'', 'module'=>'应商的采购金额推送到erp-推送失败', 'content'=>$s];
                    Vhelper::setOperatLog($log);
                    unset($log);
                    echo '应商的采购金额推送到erp-推送失败';
                }
            }
        } catch (\Exception $e) {
            $log = ['type'=>45, 'pid'=> null, 'pur_number'=>'', 'module'=>'应商的采购金额推送到erp-发生错误', 'content'=>''];
            Vhelper::setOperatLog($log);
            unset($log);
            echo '应商的采购金额推送到erp-发生错误';
        }
        unset($s);
        unset($sb);
    }
    /**
     * 推“SKU交易额”数据到SKU优选，其中“SKU交易额”指上月该SKU的交易额。
     */
    public function actionPushSkuPrice()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $skus_arr = [];
        $limit = \Yii::$app->request->getQueryParam('limit',2000);
        $skus = \Yii::$app->request->getQueryParam('skus',false);

        $lastDays = $this->getlastMonthDays(false,true);
        $startDay = array_shift($lastDays);
        $endDay = end($lastDays);
        if (!empty($skus)) {
            $skus = explode(',',$skus);
            $return_res = $this->pushErpSkuPrice($skus,$startDay,$endDay);
            unset($skus);
        } else {
            $sockSize = \Yii::$app->request->getQueryParam('sockSize',5);

            $pageBeign = ApiPageCircle::find()
                ->select('page')
                ->where(['type'=>'PUSH_SKU_TRANSACTION_AMOUNT_TO_ERP_TOTAL_COUNT_PAGE'])
                ->andWhere(['>','create_time',date('Y-m-d 00:00:00')])
                ->orderBy('id DESC')
                ->scalar();
            if(!$pageBeign) $pageBeign=0;
            ApiPageCircle::insertNewPage($pageBeign+$sockSize,'PUSH_SKU_TRANSACTION_AMOUNT_TO_ERP_TOTAL_COUNT_PAGE');
            for ($i=$pageBeign;$i<$pageBeign+$sockSize;$i++){
                $orderItemsInfo = PurchaseOrderItems::find()
                    ->alias('poi')
                    ->select('poi.pur_number, poi.sku, poi.ctq')
                    ->leftJoin(PurchaseOrder::tableName() ." po","po.pur_number = poi.pur_number")
                    ->where(['in', 'purchas_status', [5, 6, 7, 8, 9]])
                    ->andWhere(['between', 'created_at', $startDay, $endDay])
                    ->offset($i*$limit)
                    ->limit($limit)
                    ->groupBy('poi.sku')
                    ->asArray()->all();

                if(empty($orderItemsInfo)) return exit('查不到sku');
                $skus = array_unique(array_column($orderItemsInfo, 'sku'));
                unset($orderItemsInfo);
                $return_res = $this->pushErpSkuPrice($skus,$startDay,$endDay, $i);
                unset($skus);
            }
        }
    }
    public function pushErpSkuPrice($skus, $startDay, $endDay, $page=false)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        if (empty($skus)) return 'sku参数不能为空';

        $orderItemsInfo = PurchaseOrderItems::find()
            ->alias('poi')
            ->select('poi.pur_number, poi.sku, poi.ctq')
            ->leftJoin(PurchaseOrder::tableName() ." po","po.pur_number = poi.pur_number")
            ->where(['in', 'purchas_status', [5, 6, 7, 8, 9]])
            ->andWhere(['between', 'created_at', $startDay, $endDay])
            ->andWhere(['in', 'sku', $skus])
            ->asArray()->all();
        unset($skus);

        if(empty($orderItemsInfo)) return 'sku交易额推送完成';
        $pur_numbers = array_unique(array_column($orderItemsInfo, 'pur_number'));
        $itemsPrice = PurchaseOrderItems::getItemsPrice($pur_numbers);
        unset($pur_numbers);
        foreach ($orderItemsInfo as $k => $v) {

            if (isset($return_res[$v['sku']])) {
                # 存在
                $return_res[$v['sku']]['ctq'] += $v['ctq'];
                $return_res[$v['sku']]['total_price'] += $v['ctq']*$itemsPrice[$v['pur_number']][$v['sku']]['price'];
            } else {
                #不存在
                $return_res[$v['sku']]['ctq'] = $v['ctq'];
                $return_res[$v['sku']]['total_price'] = $v['ctq']*$itemsPrice[$v['pur_number']][$v['sku']]['price'];
            }
            unset($itemsPrice[$v['pur_number']][$v['sku']]);
            unset($orderItemsInfo[$k]);
        }
        unset($orderItemsInfo);
        unset($itemsPrice);
        $return_json_data = json_encode($return_res);
        unset($return_res);

        $curl   = new \linslin\yii2\curl\Curl();
        $url    = Yii::$app->params['ERP_URL'] . '/services/products/product/Getskudeal';
        // 执行推送数据
        $s = $curl->setPostParams([
            'sku_price' => $return_json_data, //Json::encode($push_list_data),
        ])->post($url);
        //验证json
        $sb = Vhelper::is_json($s);
        try {
            //验证json
            if(!$sb){
                //保存到日志
                $log = ['type'=>46, 'pid'=> null, 'pur_number'=>'', 'module'=>'推“sku交易额”数据到erp-请检查json', 'content'=>$return_json_data];
                Vhelper::setOperatLog($log);
                unset($log);
                Vhelper::dump($s);
            } else {
                $_result = Json::decode($s);
                if (!empty($_result)) {
                    //保存到日志
                    $log = ['type'=>46, 'pid'=> null, 'pur_number'=>'', 'module'=>'推“sku交易额”数据到erp-推送失败', 'content'=>$s];
                    Vhelper::setOperatLog($log);
                    unset($log);
                    echo '推“sku交易额”数据到erp-推送失败';
                } elseif($page != false) {
                    ApiPageCircle::insertNewPage($page+1,'PUSH_SKU_TRANSACTION_AMOUNT_TO_ERP_SUCCESS_PAGE');
                }
            }
        } catch (\Exception $e) {
            $log = ['type'=>46, 'pid'=> null, 'pur_number'=>'', 'module'=>'推“sku交易额”数据到erp-发生错误', 'content'=>$return_json_data];
            Vhelper::setOperatLog($log);
            unset($log);
            echo '推“sku交易额”数据到erp-发生错误';
        }
        unset($s);
        unset($sb);
        unset($return_json_data);
    }
    /**
     * 获取上个月的日期
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getlastMonthDays($date=false, $is_show_his=false){
        if (empty($date)) $date = date('Y-m-01',time());
        $timestamp=strtotime($date);
        if ($is_show_his) {
            $firstday=date('Y-m-01 00:00:00',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
            $lastday=date('Y-m-d 23:59:59',strtotime("$firstday +1 month -1 day"));
        } else {
            $firstday=date('Y-m-01',strtotime(date('Y',$timestamp).'-'.(date('m',$timestamp)-1).'-01'));
            $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
        }
        return array($firstday,$lastday);
    }
}
