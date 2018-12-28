<?php

namespace app\api\v1\controllers;
use app\api\v1\models\PlatformSummary;
use app\api\v1\models\Product;
use app\api\v1\models\PurchaseDemand;
use app\api\v1\models\ProductProvider;
use app\api\v1\models\SupplierSkuDetail;
use app\api\v1\models\PurchaseOrderItems;
use app\models\PurchaseOrderTaxes;
use app\models\ProductDescription;
use app\models\PurchaseSuggest;
use app\models\SkuSalesStatistics;
use app\services\CommonServices;
use app\models\PurchaseCompactItems;
use app\models\PurchaseCompact;
use yii;
use linslin\yii2\curl;
use yii\helpers\Json;
use yii\web\HttpException;
use app\services\BaseServices;
use app\config\Vhelper;
use app\models\SupplierNum;
/**
 * 产品
 * Created by PhpStorm.
 * User: wr
 * Date: 2018/02/06
 * Time: 10:55
 */
class PlatformSummaryController extends BaseController
{
    public $modelClass = 'app\api\v1\models\PlatformSummary';
    public function actionErpDemand(){
        header('Content-Type: text/html; charset=utf-8');
        if(Yii::$app->request->isPost){
            $token = Yii::$app->request->getBodyParam('token');
            $data = Yii::$app->request->getBodyParam('data');
            if($token=='fbapurchasesuggestion'){
                $tran = Yii::$app->db->beginTransaction();
                try{
                    $datas = json_decode($data);
                    $matchs = [];
                    preg_match_all('/\d+/',$datas->saler_group,$matchs);
                    if(empty($matchs)||!isset($matchs[0][0])||!is_numeric($matchs[0][0])){
                        throw new HttpException(500,'分组信息错误');
                    }
                    if(empty($datas->skus)||!is_array($datas->skus)){
                        throw new HttpException(500,'sku信息错误');
                    }
                    $exits = PlatformSummary::find()->andFilterWhere(['sales_note'=>$datas->batch_number])->all();
                    if(empty($exits)){
                        foreach($datas->skus  as $sku){
                            $productdesc = ProductDescription::find()->andFilterWhere(['sku'=>$sku->sku])->one();
                            $product     = Product::find()->andFilterWhere(['sku'=>$sku->sku])->one();
                            if(empty($product)||!is_numeric($sku->qty)){
                                throw new HttpException(500,'产品信息错误');
                            }
                            $model = new PlatformSummary();
                            $model->sku             = $sku->sku;
                            $model->source          = '2';
                            $model->platform_number = 'AMAZON';
                            $model->product_name    = $productdesc->title;
                            $model->purchase_warehouse = property_exists($datas,'warehouse') ? $datas->warehouse : 'SZ_AA';
                            $model->purchase_quantity  = $sku->qty;
                            $model->create_id          = 'erp';
                            $model->create_time        = date('Y-m-d H:i:s',time());
                            $model->is_transit         = 1;
                            $model->demand_number      = CommonServices::getNumber('RD');
                            $model->purchase_type      = 3;
                            $model->product_category   = $product->product_category_id;
                            $model->sales              = $datas->saler;
                            $model->sales_note         = $datas->batch_number;
                            $model->group_id           = $matchs[0][0];
                            if($model->save() == false){
                                throw new HttpException(500,'需求添加失败');
                            }
                        }
                    }
                    $tran->commit();
                    Yii::$app->response->statusCode =200;
                    Yii::$app->response->statusText ='需求生成成功';
                }catch(HttpException $e){
                    $tran->rollBack();
                    Yii::$app->response->statusCode =400;
                    Yii::$app->response->statusText =$e->getMessage();
                }
            }else{
                Yii::$app->response->statusCode =400;
                Yii::$app->response->statusText ='token错误';
            }
        }else{
            Yii::$app->response->statusCode =400;
            Yii::$app->response->statusText ='请求方式错误';
        }
    }

    public function actionGetlinebuyer(){
    	set_time_limit(0);
    	$data=PlatformSummary::find()->alias('a')->select('a.sku,a.id,g.product_linelist_id')
    	          ->leftJoin('pur_product as g','{{g}}.sku={{a}}.sku')
    	          ->where (['a.purchase_type'=>3])
    	          ->andWhere('a.line_buyer is null')
    	          ->asArray()
    	          ->limit(500)
    	          ->all();
    	if(!empty($data)){
	    	foreach ($data as $val){
	    		$firstLine =!empty($val['product_linelist_id']) ? BaseServices::getProductLineFirst($val['product_linelist_id']) : '';
	    		$buyer=\app\models\PurchaseCategoryBind::getBuyer($firstLine);
	    		//$model=new PlatformSummary();
	    		PlatformSummary::updateAll(['line_buyer'=>$buyer],'id=:id',array(':id'=>$val['id']));
	    	}
	    	exit('done all');
    	}else{
    		exit('none');
    	}
    }

    //local.cg.com/v1/platform-summary/skusalesdetail
   //sku销量信息计划任务
   public function actionSkusalesdetail(){
       set_time_limit(0);
   	   $is   = SupplierNum::find()->select('num')->where(['type'=>19])->orderBy('id desc')->scalar();
   	   if($is){
   	   	$begin=$is;
   	   }else {
   	   	$begin=0;
   	   }
//   	   $sql="SELECT MAX(a.id) AS ids, `a`.`sku`, `a`.`pur_number`, `a`.`name`, `a`.`price`, `a`.`items_totalprice`, `b`.`supplier_code`, `b`.`supplier_name`
// FROM (SELECT * FROM pur_purchase_order_items ORDER BY sku,id DESC) `a` LEFT JOIN `pur_purchase_order` `b` ON `b`.pur_number=`a`.pur_number GROUP BY `sku` limit {$begin},10000";
   	   $sql="SELECT 
        `a`.`id`,
      `a`.`sku`,
      `a`.`pur_number`,
      `a`.`name`,
      `a`.`price`,
      `a`.`items_totalprice`,
      `b`.`supplier_code`,
      `b`.`supplier_name`
FROM
pur_purchase_order_items `a`
LEFT JOIN `pur_purchase_order` `b` ON `b`.pur_number = `a`.pur_number where a.id in (select max(id) from pur_purchase_order_items GROUP BY sku order by null)
 limit {$begin},5000";
   	 
   	   $list =  Yii::$app->db->createCommand($sql)->queryAll();
//    	   $query = PurchaseOrderItems::find()->alias('a')->select('max(a.id) as ids,a.sku,a.pur_number,a.name,a.price,a.items_totalprice,b.supplier_code,b.supplier_name')
//    	            ->leftjoin('pur_purchase_order as b','{{b}}.pur_number={{a}}.pur_number ')
//    	            ->groupBy('sku')
//    	            ->offset($begin)
//    	            ->limit(10000)
//    	            ->orderBy('ids asc')
// //  	        Vhelper::dump($query->createCommand()->getRawSql());
//    	            ->asArray()
//    	            ->all();

   	   $skus=array_column($list,'sku');

//    	   Vhelper::dump($query->createCommand()->getRawSql());
   	   $skus=array_unique($skus);
   	   $linelist=Product::find()->select('sku,product_linelist_id')->andFilterWhere(['in','sku',$skus])->asArray()->all();
   	   $line=array_column($linelist,'product_linelist_id','sku');
   	   $qtys=PurchaseSuggest::find()->select('sku,qty_13')->andFilterWhere(['in','sku',$skus])->asArray()->all();
   	   $qty13=array_column($qtys,'qty_13','sku');
   	   $sales=SkuSalesStatistics::find()->select('sum(days_sales_3) as days_sales_3,sum(days_sales_15) as days_sales_15,sum(days_sales_30) as days_sales_30,sum(days_sales_60) as days_sales_60,sum(days_sales_90) as days_sales_90,sku')->andFilterWhere(['in','sku',$skus])->groupBy('sku')->asArray()->all();
   	   $sales=array_column($sales, null,'sku');
   	   if(!empty($list)){
   	   foreach ($list as $val){
   	   	$begin++;
   	   	$model=new SupplierSkuDetail();
   	   	$res=SupplierSkuDetail::find()->where(array('sku'=>$val['sku']))->one();
   	   	if(empty($res)){
            if(!isset($line[$val['sku']])){
                $productLine = Product::find()->select('product_linelist_id')->where(['sku'=>$val['sku']])->scalar();
            }
   	   		$model->ids=$val['id'];
   	   		$model->sku=$val['sku'];
   	   		$model->pur_number=$val['pur_number'];
   	   		$model->name=$val['name'];
   	   		$model->price=$val['price'];
   	   		$model->items_totalprice=$val['items_totalprice'];
   	   		$model->supplier_code=$val['supplier_code'];
   	   		$model->supplier_name=$val['supplier_name'];
   	   		$model->product_linelist_id=isset($line[$val['sku']])?$line[$val['sku']]:$productLine;
   	   		$model->qty_13=isset($qty13[$val['sku']])?$qty13[$val['sku']]:0;
   	   		$model->days_sales_3=isset($sales[$val['sku']]['days_sales_3'])?$sales[$val['sku']]['days_sales_3']:0;
   	   		$model->days_sales_15=isset($sales[$val['sku']]['days_sales_15'])?$sales[$val['sku']]['days_sales_15']:0;
   	   		$model->days_sales_30=isset($sales[$val['sku']]['days_sales_30'])?$sales[$val['sku']]['days_sales_30']:0;
   	   		$model->days_sales_60=isset($sales[$val['sku']]['days_sales_60'])?$sales[$val['sku']]['days_sales_60']:0;
   	   		$model->days_sales_90=isset($sales[$val['sku']]['days_sales_90'])?$sales[$val['sku']]['days_sales_90']:0;
   	   		$model->save();	
   	   	}else{
//    	   		continue;
            if(!isset($line[$val['sku']])){
                $productLine = Product::find()->select('product_linelist_id')->where(['sku'=>$val['sku']])->scalar();
            }
   	   		$data=array(
   	   				'ids'=>$val['id'],
   	   				'sku'=>$val['sku'],
   	   				'pur_number'=>$val['pur_number'],
   	   				'name'=>$val['name'],
   	   				'price'=>$val['price'],
   	   				'items_totalprice'=>$val['items_totalprice'],
   	   				'supplier_code'=>$val['supplier_code'],
   	   				'supplier_name'=>$val['supplier_name'],
   	   				'product_linelist_id'=>isset($line[$val['sku']])?$line[$val['sku']] :$productLine,
   	   				'qty_13'=>isset($qty13[$val['sku']])?$qty13[$val['sku']]:0,
   	   				'days_sales_3'=>isset($sales[$val['sku']]['days_sales_3'])?$sales[$val['sku']]['days_sales_3']:0,
   	   				'days_sales_15'=>isset($sales[$val['sku']]['days_sales_15'])?$sales[$val['sku']]['days_sales_15']:0,
   	   				'days_sales_30'=>isset($sales[$val['sku']]['days_sales_30'])?$sales[$val['sku']]['days_sales_30']:0,
   	   				'days_sales_60'=>isset($sales[$val['sku']]['days_sales_60'])?$sales[$val['sku']]['days_sales_60']:0,
   	   				'days_sales_90'=>isset($sales[$val['sku']]['days_sales_90'])?$sales[$val['sku']]['days_sales_90']:0,
   	   				
   	   		);
   	   		SupplierSkuDetail::updateAll($data,'id=:id',array(':id'=>$res->id));
   	   	}
   	   }
	   	   if(count($list)==5000){
		   	   $num=new SupplierNum();
		   	   $num->num=$begin;
		   	   $num->type=19;
		   	   $num->time=strtotime(date('Y-m-d H:i:s'));
		   	   $num->save(false);
	   	   }else{
	   	   	   SupplierNum::deleteAll('type=19');
	   	   }
   	  }else{
   	  	   SupplierNum::deleteAll('type=19');
   	  }
   	   exit('done all');
   }

   //获取erp海外仓采购建议,存入需求表
   public function actionHwcPlatformDemand()
   {
       $datas= Yii::$app->request->getBodyParam('data');
       if(Vhelper::is_json($datas)){
           $data = json_decode($datas);

           $successList=PlatformSummary::saveSuggestSummary($data);
           echo json_encode($successList);
           Yii::$app->end();
       }else{
          exit('推送的数据不是json字符串');
       }
   }


   public function actionPushVerifResult(){

        $datas = PlatformSummary::find()->select('suggest_id,demand_number,level_audit_status,audit_note,purchase_note')->where(['source'=>2,'purchase_type'=>2,'push_to_erp'=>0])
            ->limit(500)->asArray()->all();
       if(empty($datas)){
           exit('没有要推送的数据');
       }
        $result = [];
        foreach ($datas as $key=>$value){
            $result[$key]['id'] = $value['suggest_id'];
            $result[$key]['status'] = self::actionGetSummaryStatus($value['level_audit_status']);
            $result[$key]['msg'] = self::getSummaryReason($value);
        }

        $postData = ['token'=>'$a2#!d','data'=>json_encode($result)];
        $ch = curl_init();
        $url = Yii::$app->params['SKU_ERP_URL'].'/services/amazon/amazonskuowinventory/SyncPoSatatus';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        $output = curl_exec($ch);
        curl_close($ch);
        if(Vhelper::is_json($output)){
            $updateids = json_decode($output);
            PlatformSummary::updateAll(['push_to_erp'=>1],['suggest_id'=>$updateids]);
        };
   }

   public static function getSummaryReason($value){
        $reason = '';
        if(!isset($value['level_audit_status'])||!isset($value['demand_number'])){
            return $reason;
        }
        if(in_array($value['level_audit_status'],[1])){
            $reason = '成功';
        }
        if(in_array($value['level_audit_status'],[6,7])){
            $reason = '规则拦截:'.$value['audit_note'].'需求单号：'.$value['demand_number'];
        }
        if(in_array($value['level_audit_status'],[2])){
            $reason = '销售经理驳回'.'需求单号：'.$value['demand_number'];
        }
        if(in_array($value['level_audit_status'],[3])){
            $reason = '需求撤销'.'需求单号：'.$value['demand_number'];
        }
        if(in_array($value['level_audit_status'],[5])){
           $reason = '需求删除'.'需求单号：'.$value['demand_number'];
        }
        if(in_array($value['level_audit_status'],[4])){
            $reason = '采购驳回:'.$value['purchase_note'].'需求单号：'.$value['demand_number'];
        }
        return $reason;
   }
   public static function actionGetSummaryStatus($level_audit_status){
        $status =0;
        switch ($level_audit_status){
            case 0 :
                $status = 1;
                break;
            case 1 :
                $status =2;
                break;
            default :
                $status = 3;
        }
        return $status;
   }

    /**
     * 补财务没有合同号的数据
     * @return string|\yii\web\Response
     */
    public function actionImportcaiwu(){
        set_time_limit(0);
        ini_set('memory_limit','512M');
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        //$filePath = 'D:\phpStudy\WWW\caigou\controllers\abc.xlsx';
        $filePath       = Yii::$app->basePath.'/web/files/abc.xlsx';
        $PHPReader      = $PHPReader->load($filePath);
        $currentSheet   = $PHPReader->getSheet(0);
        $totalRows      = $currentSheet->getHighestRow();

        //设置的上传文件存放路径
        $sheetData  = $currentSheet->toArray(null,true,true,true);
        $skus = array();
        if($sheetData){
            $now_date = date('Y-m-d H:i:s');
            $i = 0;

            foreach($sheetData as $data_value){

                if($i > 1){
                    if($data_value['D'] == '') continue;
                    $pos = explode(' ',$data_value['D']);

                    foreach ($pos as $keys=>$posval){
                        if(empty($posval) || $posval == ''){
                            unset( $pos[$keys] );
                        }
                    }
                    $skus[] = $pos;
                }
                $i++;
            }
        }else{
            echo '读取不到文件';
        }

        print_r($skus);
        foreach($skus as $sku_val) {
            $compact_number = CommonServices::getNumber('ABD-XL');
            foreach ($sku_val as $pur_number) {
                $bindObj = PurchaseCompactItems::find()->where(['pur_number' => $pur_number])->all();
                if (!empty($bindObj)) {
                    break;
                }

                $model = new PurchaseCompactItems;
                $model -> compact_number = $compact_number;
                $model -> pur_number = $pur_number;
                $model ->save(false);
echo $model ->id;

            }
        }
    }
	
	/**
     *  
	 * 修改税点和合同，采购单金额。
     * @return string|\yii\web\Response
     */
    public function actionImporttaxes(){
        set_time_limit(0);
        ini_set('memory_limit','512M');
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        //$filePath = 'D:\phpStudy\WWW\caigou\controllers\abc.xlsx';
        $filePath       = Yii::$app->basePath.'/web/files/suidian.xlsx';
        $PHPReader      = $PHPReader->load($filePath);
        $currentSheet   = $PHPReader->getSheet(0);
        $totalRows      = $currentSheet->getHighestRow();

        //设置的上传文件存放路径
        $sheetData  = $currentSheet->toArray(null,true,true,true);
        $skus = array();
        if($sheetData){
            $now_date = date('Y-m-d H:i:s');
            $i = 0;

            foreach($sheetData as $data_value){

                if($i > 1){
					$taxes	=	$data_value['K'];
					$compact_number = $data_value['E'];
                    if($data_value['D'] == '') continue;
                    $pos = explode(' ',$data_value['D']);

                    foreach ($pos as $keys=>$posval){
                        if(empty($posval) || $posval == ''){
                            unset( $pos[$keys] );
                        }
                    }
                    $skus[] = array($pos,$taxes);
                    if(!empty($compact_number) || $compact_number != '') {
                        $compact_numbers[] = $compact_number;
                    }
                }
                $i++;
            }
        }else{
            echo '读取不到文件';
        }

       // print_r($skus);

        foreach($skus as $sku_val) {
			foreach( $sku_val[0] as $pur_val){
				$purchaseDemand = PurchaseDemand::find()->where(['pur_number' => $pur_val])->all();
				if(empty($purchaseDemand))continue;
				foreach( $purchaseDemand as $demand ){
					$platformSummary = PlatformSummary::find()->where(['demand_number' => $demand->demand_number])->one();
					if(!empty($platformSummary)){
						$platformSummary->pur_ticketed_point = $sku_val[1];
						$platformSummary->save(false);
						$PurchaseOrderItems = PurchaseOrderItems::find()->where(['pur_number' => $pur_val,'sku'=>$platformSummary->sku])->one();
						if(empty($PurchaseOrderItems))continue;
							$PurchaseOrderItems->price =  $PurchaseOrderItems->price * (1 + $sku_val[1]);
                            $PurchaseOrderItems->pur_ticketed_point =$sku_val[1];
                            $PurchaseOrderItems->items_totalprice = $PurchaseOrderItems->ctq * $PurchaseOrderItems->price;
                            $PurchaseOrderItems->save(false);
							
							$PurchaseOrderTaxes = PurchaseOrderTaxes::find()->where(['pur_number' => $pur_val,'sku'=>$platformSummary->sku])->one();
							if(empty($PurchaseOrderTaxes)){
								$PurchaseOrderTaxes->taxes =  $sku_val[1];
								$PurchaseOrderTaxes->pur_number =  $pur_val;
								$PurchaseOrderTaxes->sku =  $platformSummary->sku;
								$PurchaseOrderTaxes->create_time =  $now_date;
								$PurchaseOrderTaxes->is_taxes = 1;
								$PurchaseOrderTaxes->save(false);
							}else{
								$PurchaseOrderTaxes->taxes =  $sku_val[1];
								$PurchaseOrderTaxes->is_taxes = 1;
								$PurchaseOrderTaxes->save(false);
							}
							echo $PurchaseOrderTaxes->items_totalprice;
					}
				}
			}
        }

        foreach ($compact_numbers as $compact){
            $this->Compactcalculation($compact);
        }
    }

    /**
     *
     *  修改税点后重新计算合同价格，如果存在合同。
     *
     */
    public function Compactcalculation($compact){
        $compact_number =  PurchaseCompact::find()->where(['compact_number' => $compact])->one();
        if(empty($compact_number)) return false;

        $PurchaseOrderItems = PurchaseCompactItems::find()->where(['compact_number' => $compact])->all();
        $total_money = 0;
        if(!empty($PurchaseOrderItems)) {
            foreach ($PurchaseOrderItems as $OrderItems) {
                $total_money += $OrderItems->ctq * $OrderItems->price;
            }
        }
        $compact_number->product_money = $total_money;
        $compact_number->save(false);
        return true;
    }

}
