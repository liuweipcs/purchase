<?php
namespace app\controllers;
use app\models\PlatformSummary;
use app\models\ProductProvider;
use app\models\PurchaseCompact;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderPayWater;
use app\models\PurchaseOrderReceipt;
use app\models\PurchaseOrderReceiptWater;
use app\models\PurchaseReceive;
use app\models\SupplierCheck;
use app\models\SupplierCheckNote;
use app\models\SupplierDeliverAdress;
use app\models\SupplierQuotes;
use yii\db\Exception;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/4
 * Time: 17:01
 */
class TestController extends BaseController{
    public function actionUpdateSupplier(){
        $file_name = \Yii::$app->request->getQueryParam('fileName','');
        set_time_limit(0);
        if(!empty($file_name)){
            $count=1;
        }else{
            $count=3;
        }
        for ($i=1;$i<=$count;$i++){
            if(!empty($file_name)){
                $file        = fopen($file_name, 'r');
            }else{
                $file        = fopen('supplier_update_info_0'.$i.'.csv', 'r');
            }
        $line_number = 0;
        $Name=[];
        while ($datas = fgetcsv($file)) {
            $num = count($datas);
            for ($c = 0; $c < $num; $c++) {
                $Name[$line_number][] = mb_convert_encoding(trim($datas[$c]),'utf-8','gbk');
            }
            $line_number++;
        }
        $successIndex=0;
        if(!empty($Name)){
            foreach ($Name as $k=>$value){
                if(isset($value[2])&&$value[2]=='不保留'){
                    continue;
                }
                if(isset($value[2])&&$value[2]=='保留'){
                    if(isset($value[0])&&!empty($value[0])){
                        $supplier_code = $value[0];//获取打算保留的供应商编码,
                        foreach ($Name as $key=>$v){
                            if(!isset($v[0])||!isset($v[1])||!isset($v[2])){
                                continue;
                            }
                            if($v[1]===$value[1]&&$v[2]==='不保留'){
                                $supplier_code_update[] = $v[0];
                                unset($Name[$key]);
                            }
                        }
                        if(empty($supplier_code_update)){
                            continue;
                        }
                        $supplier_name = $value[1];
                       $updateStatus= $this ->updateSupplier($supplier_code,$supplier_code_update,$supplier_name);
                       if($updateStatus){
                           exit($updateStatus);
                       }
                    }else{
                        continue;
                    }

                }
            }
        }
        echo '更新成功';
        }
    }

    public function updateSupplier($supplier_code,$supplier_code_update,$supplier_name){
/*
 * pur_platform_summary  supplier_code
    pur_product_supplier  supplier_code
    pur_supplier_quotes   suppliercode
    pur_purchase_compact   supplier_name    supplier_code
    pur_purchase_order     supplier_code   supplier_name
    pur_purchase_order_pay  supplier_code
    pur_purchase_order_pay_water  supplier_code
    pur_purchase_order_receipt    supplier_code
    pur_purchase_order_receipt_water    supplier_code
    pur_purchase_receive  supplier_code   supplier_name
    pur_supplier_check  supplier_code
    pur_supplier_check_note   supplier_code
    pur_supplier_deliver_adress supplier_code
 */
        //更新需求里面的供应商编码
        $tran = \Yii::$app->db->beginTransaction();
        try{
            PlatformSummary::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新sku的绑定关系
            $quoteDatas = ProductProvider::find()->select('sku,quotes_id,supplier_code')
                ->where(['in','supplier_code',$supplier_code_update])
                ->andWhere(['is_supplier'=>1])
                ->asArray()->all();
            if(!empty($quoteDatas)){
                foreach ($quoteDatas as $value){
                    $exist = ProductProvider::find()->where(['sku'=>$value['sku'],'supplier_code'=>$supplier_code])->exists();
                    ProductProvider::updateAll(['is_supplier'=>0],['sku'=>$value['sku']]);
                    if($exist){
                        ProductProvider::updateAll(['is_supplier'=>1,'quotes_id'=>$value['quotes_id'],'is_push_to_erp'=>0],['supplier_code'=>$supplier_code,'is_supplier'=>0,'sku'=>$value['sku']]);
                    }else{
                        ProductProvider::updateAll(['supplier_code'=>$supplier_code,'is_supplier'=>1,'is_push_to_erp'=>0],['supplier_code'=>$value['supplier_code'],'sku'=>$value['sku']]);
                    }
                    SupplierQuotes::updateAll(['suppliercode'=>$supplier_code],['id'=>$value['quotes_id']]);
                }
            }
            //更新合同数据
            PurchaseCompact::updateAll(['supplier_code'=>$supplier_code,'supplier_name'=>$supplier_name],['in','supplier_code',$supplier_code_update]);
            //更新采购单数据
            PurchaseOrder::updateAll(['supplier_code'=>$supplier_code,'supplier_name'=>$supplier_name],['in','supplier_code',$supplier_code_update]);
            //更新支付数据
            PurchaseOrderPay::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新支付流水数据
            PurchaseOrderPayWater::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新退款数据
            PurchaseOrderReceipt::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新退款流水数据
            PurchaseOrderReceiptWater::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新收货异常数据
            PurchaseReceive::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新供应商验厂验货数据
            SupplierCheck::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新供应商验厂验货备注数据
            SupplierCheckNote::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            //更新供应商收货地址数据
            SupplierDeliverAdress::updateAll(['supplier_code'=>$supplier_code],['in','supplier_code',$supplier_code_update]);
            $tran->commit();
            return false;
        } catch (Exception $e){
            $tran->rollBack();
            return $e->getMessage();
        }
    }
}