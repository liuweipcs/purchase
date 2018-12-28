<?php 
namespace app\controllers;

use app\api\v1\models\HwcAvgDeliveryTime;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\PurchaseOrderItems;
use app\api\v1\models\ArrivalRecord;
use app\api\v1\models\Product;
use m35\thecsv\theCsv;
use Yii;
use yii\web\Controller;

class ImportAvgArrivalController extends Controller{

	public function actionIndex() {
		$skuArr = HwcAvgDeliveryTime::find()
		  		  ->alias('h')
				  ->select('h.sku')
				  ->leftJoin(Product::tableName().' p','skus')
				  ->andWhere(['p.product_status' => 4])
				  ->orderBy('h.avg_delivery_time ASC')
				  ->limit(10000)
				  ->asArray()
				  ->all();

		$pur_data = [];

		foreach ($skuArr as $value) {
			$sku = $value['sku'];

			$pur_res = PurchaseOrder::find()
					   ->alias('o')
					   ->select('i.sku, o.pur_number, o.audit_time, r.delivery_time, r.purchase_order_no,(UNIX_TIMESTAMP(r.delivery_time) - UNIX_TIMESTAMP(o.audit_time))/(24*3600) as td')
					   ->leftJoin(PurchaseOrderItems::tableName().' i','i.pur_number = o.pur_number')
					   ->innerJoin(ArrivalRecord::tableName().' r','r.purchase_order_no = o.pur_number')
					   ->andWhere(['o.pay_status' => 5,'i.sku' =>$sku])
					   ->andWhere(['in','o.purchas_status', [5,6,8,9]])
					   ->orderBy('o.submit_time DESC')
					   ->limit(5)
					   ->asArray()
					   ->all();
	   //echo "<pre>";var_dump($pur_res);exit;
			$pur_data[$sku] = $pur_res;			   
		}

		$table = ['SKU','1订单','2订单','3订单','4订单','5订单'];
		$table_head = [];
		foreach ($pur_data as $c=>$v)
		{
		    $table_head[$c][]= $c;
		    $table_head[$c][]= $v[0]['td'] ? $v[0]['td'] : '';
		    $table_head[$c][]= $v[1]['td'] ? $v[1]['td'] : '';
		    $table_head[$c][]= $v[2]['td'] ? $v[2]['td'] : '';
		    $table_head[$c][]= $v[3]['td'] ? $v[3]['td'] : '';
		    $table_head[$c][]= $v[4]['td'] ? $v[4]['td'] : '';
		}
		theCsv::export([
		    'header' =>$table,
		    'data' => $table_head,
		]);
	}
}