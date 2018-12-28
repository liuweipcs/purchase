<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderSearch;
use m35\thecsv\theCsv;

class WarehouseEntryController extends Controller
{
	public function actionIndex()
	{
		$params = Yii::$app->request->queryParams;
        $data = PurchaseOrder::getList($params);
		return $this->render('index',$data);
	}
	public function actionExportCsv()
	{
		set_time_limit(0);
        ini_set('memory_limit', '1024M');

		$get = Yii::$app->request->get();
		if ($get['is_warehouse'] == 1) {
            $data = Yii::$app->cache->get('warehouse-details');
            if (empty($data)) return '无数据';
        	$this->ExportWarehouse($data);
		} else {
            $data = Yii::$app->cache->get('warehouse-details');
            if (empty($data)) return '无数据';
        	$this->ExportPayment($data);
		}
	}
	public function ExportWarehouse($data)
	{
        $table = [
            '合同号',
            '采购单号',
            'sku',
            '商品名称',
            '采购类型',
            '供应商名称',
            '供应商编号',
            '入库日期',
            '入库单号',
            '入库仓',
            '应入库数',
            '已入库数',
            '良品库',
            '次品库/不入库',
            '入库单价',
            '是否开票',
            '结算方式',
            '支付方式',
            '采购员备注',
        ];

        $table_head = [];
        foreach($data as $k=>$v)
        {
            $table_head[$k][] = $v['compact_number'];
            $table_head[$k][] = $v['pur_number']; 
            $table_head[$k][] = $v['sku'];
            $table_head[$k][] = $v['name'];                  
            $table_head[$k][] = $v['purchase_type'];       
            $table_head[$k][] = $v['supplier_name'];       
            $table_head[$k][] = $v['supplier_code'];       
            $table_head[$k][] = $v['instock_date'];        
            $table_head[$k][] = $v['receipt_number'];      
            $table_head[$k][] = $v['warehouse_code'];      
            $table_head[$k][] = $v['purchase_quantity'];   
            $table_head[$k][] = $v['instock_qty_count'];   
            $table_head[$k][] = $v['instock_qty_count'];   
            $table_head[$k][] = $v['nogoods'];             
            $table_head[$k][] = $v['price'];               
            $table_head[$k][] = $v['is_drawback'];         
            $table_head[$k][] = $v['supplier_settlement_name']; 
            $table_head[$k][] = $v['pay_type'];    
            $table_head[$k][] = $v['note'];
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
        die;
	}
	public function ExportPayment($data)
	{
		$table = [
            '合同号',
			'采购单号',
			'sku',
			'商品名称',
			'采购类型',
			'供应商名称',
			'采购员',
			'sku采购数量',
			'sku采购单价',
			'sku采购金额',
			'到货数量',
			'采购日期',
			'付款日期',
			'采购状态',
			'备注',
        ];

        $table_head = [];
        foreach($data as $k=>$v)
        {
            $table_head[$k][] = $v['compact_number'];
			$table_head[$k][] = $v['pur_number'];
			$table_head[$k][] = $v['sku']; 
			$table_head[$k][] = $v['name'];
			$table_head[$k][] = $v['purchase_type'];
			$table_head[$k][] = $v['supplier_name'];
			$table_head[$k][] = $v['buyer'];
			$table_head[$k][] = $v['ctq'];
			$table_head[$k][] = $v['price'];
			$table_head[$k][] = $v['total_price'];
			$table_head[$k][] = $v['arrival_quantity'];
			$table_head[$k][] = $v['created_at'];
			$table_head[$k][] = $v['payer_time'];
			$table_head[$k][] = $v['purchas_status'];
			$table_head[$k][] = $v['note']; 
        }
        theCsv::export([
            'header' =>$table,
            'data' => $table_head,
        ]);
        die;
	}
}