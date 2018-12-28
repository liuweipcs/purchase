<?php

namespace app\controllers;


use app\api\v1\models\ProductLine;
use app\config\Vhelper;
use app\models\SupplierBuyer;
use app\models\SupplierGoodsSearch;
use app\models\SupplierContactInformation;
use app\models\SupplierImages;
use app\models\SupplierLog;
use app\models\PurchaseAmountSearch;
use app\models\SupplierPaymentAccount;
use app\models\SupplierProductLine;
use app\models\SupplierUpdateApply;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrder;
use app\services\BaseServices;
use app\services\CommonServices;
use app\services\SupplierServices;
use Yii;
use app\models\Supplier;
use app\models\Product;
use app\models\ProductDescription;
use app\models\SupplierSearch;
use yii\helpers\ArrayHelper;
use yii\web\ConflictHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\UploadedFile;
/**
 * Created by PhpStorm.
 * User: ztt
 * Date: 2017/3/23 0023
 * Time: 18:41
 */
class SupplierPurchaseAmountController extends BaseController
{
    /**
     * @inheritdoc
     */
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
     * Lists all Stockin models.
     * @return mixed
     */
    public function actionIndex()
    {

    	$searchModel = new PurchaseAmountSearch();
    	$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    	return $this->render('index', [
    			'searchModel' => $searchModel,
    			'dataProvider' => $dataProvider,
    	]);
    }

    public function actionExportamount(){        
         set_time_limit(0);
         $form = Yii::$app->request->getQueryParam('purNumber');
//          Vhelper::dump($form);
         $purNumber =   explode(',',$form);
         foreach($purNumber as $v){
         	$info=explode('_',$v);
         	$skus[]=$info[0];
         	$pur_nums[]=$info[1];
         }
         $datas = PurchaseOrderItems::find()->andFilterWhere(['in','pur_number',$pur_nums]);
         $datas = $datas->andFilterWhere(['in','sku',$skus])->asArray()->all();
         $pur= PurchaseOrder::find()->andFilterWhere(['in','pur_number',$pur_nums])->asArray()->all();        
         $suppliers=array_column($pur, 'supplier_name','pur_number');
         $supplier_codes=array_column($pur, 'supplier_code','pur_number');
         $submit_time=array_column($pur,'submit_time','pur_number');
         $supplier_info= Supplier::find()->andFilterWhere(['in','supplier_code',$supplier_codes])->asArray()->all();
         $createtime=array_column($supplier_info,'create_time','supplier_code');
         $supplier_settlement=array_column($supplier_info,'supplier_settlement','supplier_code');
         $product=Product::find()->andFilterWhere(['in','sku',$skus])->asArray()->all();
         $category=array_column($product,'product_category_id','sku');
         $desc=ProductDescription::find()->andFilterWhere(['in','sku',$skus])->asArray()->all();
         $title=array_column($desc,'title','sku');
         $objectPHPExcel = new \PHPExcel();
         $objectPHPExcel->setActiveSheetIndex(0);
         $n = 0;
         //报表头的输出
         $objectPHPExcel->getActiveSheet()->mergeCells('A1:N1'); //合并单元格
         $objectPHPExcel->getActiveSheet()->mergeCells('A1:A2');
         $objectPHPExcel->getActiveSheet()->setCellValue('A1','供应商采购信息表');  //设置表标题
         $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(24); //设置字体大小
         $objectPHPExcel->setActiveSheetIndex(0)->getStyle('A1')
         ->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         //表格头的输出
         $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('A3','首次合作时间');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(6.5);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B3','供应商');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('C3','结算方式');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('D3','图片');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('E3','PO号');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('F3','产品线');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('G3','SKU');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('H3','产品名称');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('I3','下单时间');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('J3','单价');
         $objectPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
         $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('K3','金额');

         	/*if($pur->purchas_status ==1){
         	 continue;
         	 }*/
         	if(!empty($datas)){
         
         		$totalprice = 0;
         		$b=$n;
         		foreach($datas as $val){
         			$objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,$n+1);
         			$objectPHPExcel->getActiveSheet()->getRowDimension($n+4)->setRowHeight(75);
         			$url = Vhelper::downloadImg($val['sku'],'');
         			if($url ){
         				$img=new \PHPExcel_Worksheet_Drawing();
         				$img->setPath($url);//写入图片路径
         				$img->setHeight(50);//写入图片高度
         				$img->setWidth(100);//写入图片宽度
         				$img->setOffsetX(2);//写入图片在指定格中的X坐标值
         				$img->setOffsetY(2);//写入图片在指定格中的Y坐标值
         				$img->setRotation(1);//设置旋转角度
         				//$img->getShadow()->setVisible(true);//
         				$img->getShadow()->setDirection(50);//
         				$img->setCoordinates('D'.($n+4));//设置图片所在表格位置
         				$img->setWorksheet($objectPHPExcel->getActiveSheet());//把图片写到当前的表格中
         			}
         			$objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+4) ,date('Y-m-d H:i:s',$createtime[$supplier_codes[$val['pur_number']]]));
         			$objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4) ,$suppliers[$val['pur_number']]);
         			$objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4) ,SupplierServices::getSettlementMethod($supplier_settlement[$supplier_codes[$val['pur_number']]]));
         			$objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4) ,$val['pur_number']);
         			$objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4) ,!empty($category[$val['sku']]) ? BaseServices::getCategory($category[$val['sku']]) : '');
         			$objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4) ,$val['sku']);
         			$objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4) ,$title[$val['sku']]);
         			$objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4) ,$submit_time[$val['pur_number']]);
         			$objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4) ,$val['price']);//
         			$objectPHPExcel->getActiveSheet()->setCellValue('K'.($n+4) ,$val['items_totalprice']);//
                    $n++;
         
         	}
//          }
         //设置样式
       
         $objectPHPExcel->getActiveSheet()->getStyle('A2:N'.($n+4))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
         $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
         ob_end_clean();
         ob_start();
         header("Content-type:application/vnd.ms-excel;charset=UTF-8");
         header('Content-Type : application/vnd.ms-excel');
         header('Content-Disposition:attachment;filename="'.'供应商采购信息表-'.date("Y年m月j日").'.xls"');
         $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
         $objWriter->save('php://output');
         
         
    }
    
   }   
   
   public function actionList(){
   	$searchModel = new PurchaseAmountSearch();
   	$dataProvider = $searchModel->search1(Yii::$app->request->queryParams);
   	return $this->render('list', [
   			'searchModel' => $searchModel,
   			'dataProvider' => $dataProvider,
   	]);
   }
   
   public  function actionExportalldata(){
   	set_time_limit(0);
   	ini_set('memory_limit','500M');
   	//$category = Yii::$app->request->getQueryParam('product_category_id');
   	$supplier_code = Yii::$app->request->getQueryParam('supplier_code');
   	//$purchase_type = Yii::$app->request->getQueryParam('purchase_type');
   	//$sku = Yii::$app->request->getQueryParam('sku');
   	$agree_time = Yii::$app->request->getQueryParam('agree_time');
   	$agree_time1=substr($agree_time, 0,10);
   	$agree_time2=substr($agree_time, 22);
//    	$query = PurchaseOrderItems::find()->andFilterWhere(['not in', 'purchas_status', [1,2,4,10]]);
//    	$query->joinWith('purNumber');
//    	$query->joinWith('suppliers');
//    	$query->joinWith('product');
//    	$query->select('pur_purchase_order_items.id,pur_purchase_order_items.sku,pur_purchase_order_items.pur_number,pur_purchase_order_items.name,
//    			pur_purchase_order_items.price,pur_purchase_order_items.ctq,pur_purchase_order_items.items_totalprice,
//    			pur_supplier.create_time,pur_supplier.supplier_name,pur_supplier.supplier_settlement,
//    			pur_product.product_category_id,
//    			pur_purchase_order.created_at,pur_purchase_order.buyer,pur_purchase_order.purchase_type');
   	$query=PurchaseOrderItems::find()->alias('t')->andFilterWhere(['not in', 'purchas_status', [1,2,4,10]]);
   	$query->leftJoin('pur_purchase_order a','a.pur_number=t.pur_number');
   	$query->leftJoin('pur_supplier b','a.supplier_code=b.supplier_code');
   	$query->leftJoin('pur_product c','c.sku=t.sku');
   	$query->select('t.id,t.sku,t.pur_number,t.name,t.price,t.ctq,t.items_totalprice,
   			b.create_time,b.supplier_name,b.supplier_settlement,
   			c.product_category_id,
    		a.created_at,a.buyer,a.purchase_type
   			');
   	if(!empty($supplier_code)){
   		$query = $query->andFilterWhere(['=','a.supplier_code',$supplier_code]);
   	}
//    	if(!empty($purchase_type)){
//    		$query = $query->andFilterWhere(['=','pur_purchase_order.purchase_type',$purchase_type]);
//    	}
   	if(!empty($agree_time)){
   		$query = $query->andFilterWhere(['between','a.created_at',$agree_time1,$agree_time2]);
   	}
    $datas=$query->asArray();
    $filename = '采购信息导出'.date('YmdHis') . '-';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
    header('Cache-Control: max-age=0');
    $fp = fopen('php://output', 'a');
    
    $headerCell = [
    		'首次合作时间','供应商','结算方式','PO号','产品线','SKU','产品名称','下单时间','单价','数量','金额','采购员','采购类型'
    ];
   
    $headerTitle = [];
    foreach ($headerCell as $title)
    {
    	$headerTitle[] = iconv('utf-8', 'gbk', $title);
    }
    
    fputcsv($fp, $headerTitle);
    	$countLimit = 10000;    //导出10000条刷新缓存
    	$count = 0;             //计算器
    	foreach ($datas->each() as $val)
    	{
    		if ($count > $countLimit)
    		{
    			ob_flush();
    			flush();
    			$count = 0;
    		}
    	
    		$data = [];
    		$data[] =  date('Y-m-d H:i:s',$val['create_time']);
    		$data[] =  $val['supplier_name'];
    		$data[] =  !empty($val['supplier_settlement'])?SupplierServices::getSettlementMethod($val['supplier_settlement']):'';
    		$data[] =  isset($val['pur_number'])?$val['pur_number']:'';
    		$data[] =  !empty($val['product_category_id']) ? BaseServices::getCategory($val['product_category_id']): '';   	
    		$data[] =  $val['sku'];
    		$data[] =  $val['name'];
    		$data[] =  $val['created_at'];
    		$data[] =  $val['price'];
    		$data[] =  $val['ctq'];
    		$data[] =  $val['items_totalprice'];
    		$data[] =  $val['buyer'];
    		$data[] =  ($val['purchase_type']==1)?'国内':(($val['purchase_type']==2)?'海外':'FBA');

    		foreach ($data as $k => $value)
    		{
    			$data[$k] = iconv('utf-8', 'GBK//IGNORE', $value);
    		}
    		fputcsv($fp, $data);
    		unset($data);
    		$count++;
    	} 
    	exit;
   }
   
   public function actionExportsumdata(){
   	set_time_limit(0);
   	ini_set('memory_limit','500M');
   	$supplier_code = Yii::$app->request->getQueryParam('supplier_code');
   	$agree_time = Yii::$app->request->getQueryParam('agree_time');
   	$agree_time1=substr($agree_time, 0,10);
   	$agree_time2=substr($agree_time, 22);
   	
   	$query=PurchaseOrder::find()->alias('t')->andFilterWhere(['not in', 'purchas_status', [1,2,4,10]]);
//    	$query->leftJoin('pur_purchase_order a','a.pur_number=t.pur_number');
//    	$query->leftJoin('pur_supplier b','a.supplier_code=b.supplier_code');
//    	$query->leftJoin('pur_product c','c.sku=t.sku');
   	$query->select('t.id,t.pur_number,t.supplier_name,t.purchase_type,t.supplier_code');  
   	if(!empty($supplier_code)){
   		$query = $query->andFilterWhere(['=','t.supplier_code',$supplier_code]);
   	}
   	if(!empty($agree_time)){
   		$query = $query->andFilterWhere(['between','t.created_at',$agree_time1,$agree_time2]);
   	}
   	$datas=$query->groupBy('supplier_code,pur_number')->asArray()->all();
    $pur_numbers=array_column($datas,'pur_number');
    $items=PurchaseOrderItems::find()->select('pur_number,items_totalprice')->andFilterWhere(['in', 'pur_number',$pur_numbers])->groupBy('pur_number')->asArray()->all();
    $items=array_column($items,'items_totalprice','pur_number');
    $data=array();
   
    foreach($datas as $val){
    	$data[$val['supplier_code']]['total']=isset($data[$val['supplier_code']]['total']) ? $data[$val['supplier_code']]['total']+$items[$val['pur_number']]: $items[$val['pur_number']];
//     	$data['types'][$val['purchase_type']]=$val['purchase_type'];
    	$data[$val['supplier_code']]['name']=$val['supplier_name'];
    	$data[$val['supplier_code']]['PO'][$val['purchase_type']][$val['pur_number']]=$val['pur_number'];
    }
   	$filename = '汇总导出'.date('YmdHis') . '-';
   	header('Content-Type: application/vnd.ms-excel');
   	header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
//    	header('Cache-Control: max-age=0');
   	$fp = fopen('php://output', 'a');
   	
   	$headerCell = [
   			'供应商','国内','海外','FBA','采购建议','金额'
   	];
   	 
   	$headerTitle = [];
   	foreach ($headerCell as $title)
   	{
   		$headerTitle[] = iconv('utf-8', 'gbk', $title);
   	}
   	
   	fputcsv($fp, $headerTitle);
   	$countLimit = 10000;    //导出10000条刷新缓存
   	$count = 0;             //计算器
//    	Vhelper::dump(count($data));

   	
   	foreach ($data as $val)
   	{
   		if ($count > $countLimit)
   		{
   			ob_flush();
   			flush();
   			$count = 0;
   		}
   		 
   		$row = [];
   		$row[] =  $val['name'];
   		$row[] =  isset($val['PO'][1])?count($val['PO'][1]):'';
   		$row[] =  isset($val['PO'][2])?count($val['PO'][2]):'';
   		$row[] =  isset($val['PO'][3])?count($val['PO'][3]):'';
   		$row[] =  isset($val['PO'][4])?count($val['PO'][4]):'';
   		$row[] =  $val['total'];

   		foreach ($row as $k => $value)
   		{
   			$row[$k] = iconv('utf-8', 'GBK//IGNORE', $value);
   		}
   		fputcsv($fp, $row);
   		unset($row);
   		$count++;
   	}
   	exit;
   }
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
}