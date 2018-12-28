<?php

namespace app\models;

use Yii;
use app\services\PurchaseOrderServices;
use app\services\CommonServices;

/**
 * This is the model class for table "pur_overseas_demand_rule".
 *
 * @property integer $id
 * @property string $min_money
 * @property string $max_money
 * @property integer $status
 * @property integer $create_user_id
 * @property string $create_time
 * @property string $min_money_limit
 * @property integer $transport
 * @property integer $type
 * @property integer $supplier_invoice
 */
class OverseasDemandRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_overseas_demand_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['min_money', 'create_user_id', 'create_time'], 'required'],
            [['min_money', 'max_money', 'min_money_limit'], 'number'],
            [['status', 'create_user_id', 'transport', 'type', 'supplier_invoice'], 'integer'],
            [['create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'min_money' => 'Min Money',
            'max_money' => 'Max Money',
            'status' => 'Status',
            'create_user_id' => 'Create User ID',
            'create_time' => 'Create Time',
            'min_money_limit' => 'Min Money Limit',
            'transport' => 'Transport',
            'type' => 'Type',
            'supplier_invoice' => 'Supplier Invoice',
        ];
    }
    public static function saveRules($datas){
        $tran = Yii::$app->db->beginTransaction();
        try{
            if(!is_array($datas)){
                throw new Exception('提交数据无法添加！');
            }
            $insertData = [];
            $formData = [];
            foreach ($datas['transport'] as $k=>$data){
                $formData=array_column($datas,$k);
                $formData[]=Yii::$app->user->id;
                $formData[]=date('Y-m-d H:i:s',time());
                $formData[]=1;
                $insertData[]=$formData;
            }
            self::updateAll(['status'=>0],['status'=>1]);
            Yii::$app->db->createCommand()->batchInsert(self::tableName(),[
                'transport',
                'supplier_invoice',
                'min_money',
                'max_money',
                'min_money_limit',
                'create_user_id',
                'create_time',
                'status'
            ],$insertData)->execute();
            $tran->commit();
            $response = ['status'=>'success','message'=>'规则编辑成功'];
        }catch (Exception $e){
            $tran->rollBack();
            $response = ['status'=>'error','message'=>'规则编辑失败'];
        }
        return $response;
    }

    /*
     * 海外仓验证拦截规则
     * sku:需求sku
     * num:需求数量
     * warehouseCode:采购仓
    */
    public static function verifyInterceptRule($sku,$warehouseCode,$type=1,$demandNumber=null,$transport,$update=true,$updateData=[]){

        $skuInfo = ProductProvider::find()
            ->select('t.supplier_code,q.supplierprice,s.supplier_name')
            ->alias('t')
            ->where(['t.sku'=>$sku])
            ->andWhere(['t.is_supplier'=>1])
            ->leftJoin(SupplierQuotes::tableName().' q','t.quotes_id=q.id')
            ->leftJoin(Supplier::tableName().' s','t.supplier_code=s.supplier_code')
            ->asArray()
            ->one();
        if(empty($skuInfo['supplier_code'])||empty($skuInfo['supplierprice'])){
            return ['status'=>'error','message'=>"产品报价信息不全无法匹配拦截规则",'level'=>6];
        }

        $supplierSku = ProductProvider::find()->select('sku')->where(['supplier_code'=>$skuInfo['supplier_code'],'is_supplier'=>1])->asArray()->all();
        $supplierInvoice = Supplier::find()->select('invoice')->where(['supplier_code'=>$skuInfo['supplier_code']])->scalar();
        $supplierSkus = array_column($supplierSku,'sku');
        $dateVer = self::verifyInterceptDateRule($supplierSkus,$demandNumber);
        //7天3小时拦截错误提示
        $dateMsg = '';
        if($dateVer['status']=='error'){
            //return $dateVer;
            $dateMsg = $dateVer['message'];
        }
        $submitVer = self::verifyAgreeRule($demandNumber,$supplierSkus,$warehouseCode,$transport,$skuInfo['supplier_name']);
        if($submitVer['status']=='error'){
            return $submitVer;
        }
        //匹配不拦截规则
        $passRule = self::verifyPassRule($transport,$warehouseCode);
        $amountMsg = '';
        if(!$passRule){
            $purchaseData = self::getVerPurchasePrice($supplierSkus,$warehouseCode,$transport,$updateData);
            $purchasePrice = $purchaseData['price'];
            $matchRule = OverseasDemandRule::find()
                ->where(['in','transport',[0,$transport]])
                ->andWhere(['in','supplier_invoice',[0,$supplierInvoice]])
                ->andWhere(['status'=>1])
                ->andWhere('(min_money < :priceAmount and :priceAmount<= max_money) or (min_money< :priceAmount and isnull(max_money))',[':priceAmount'=>$purchasePrice])
                ->one();
            //->createCommand()->getRawSql();
            //金额拦截错误提示
            if(!empty($matchRule)){
                if($purchasePrice<$matchRule->min_money_limit){
                    $amountMsg = "不满足金额拦截规则,最低采购金额:$matchRule->min_money_limit";
                }
            }
        }
        //7天3小时、金额拦截拦截错误提示一起返回
        if(!empty($dateMsg) || !empty($amountMsg)){
            $merge_msg = $dateMsg.";".$amountMsg;
            return ['status'=>'error','message'=>ltrim($merge_msg,";"),'level'=>6,'supplier_name'=>$skuInfo['supplier_name']];
        }
        if($update){
            $updateSku = self::updateSummary($supplierSkus,$warehouseCode,$transport);
        }
        if($update){
            return ['status'=>'success','message'=>"没有匹配到任何拦截规则",'updateSku'=>$updateSku];
        }else{
            return ['status'=>'success'];
        }
    }


    public static function updateSummary($supplierSkus,$warehouseCode,$transport){
        $query = PlatformSummary::find()
            ->where(['purchase_warehouse'=>$warehouseCode])
            ->andWhere(['in','sku',$supplierSkus])
            ->andWhere(['in','level_audit_status',[6,7]])
            ->andWhere(['purchase_type'=>2])
            ->andWhere(['transport_style'=>$transport]);
        $datas = $query ->all();
        $updateArray = [];
        foreach ($datas as $data){
            $quoteid = ProductProvider::find()->where(['sku'=>$data->sku,'is_supplier'=>1])->select('quotes_id')->scalar();
            $product_quote = SupplierQuotes::find()->where(['id'=>$quoteid])->one();
            
            $updateArray[]=$data->sku;
            $data->updateAttributes(['level_audit_status'=>1,'supplier_code'=>$product_quote->suppliercode,'push_to_erp'=>0,'agree_time'=>date('Y-m-d H:i:s',time())]);
            
            self::agreeUpdateOrder($data, $product_quote);
            
            PurchaseOrderServices::writelog($data->demand_number, '规则拦截通过');
        }
        return $updateArray;
    }
    public static function verifyAgreeRule($demandNumber,$supplierSkus,$warehouseCode,$transport,$supplier_name){
        $query= PlatformSummary::find()
            ->where(['in','sku',$supplierSkus])
            ->andWhere(['level_audit_status'=>0])
            ->andWhere(['<>','demand_number',$demandNumber])
            ->andWhere(['purchase_warehouse'=>$warehouseCode])
            ->andWhere(['purchase_type'=>2]);
        $query->andWhere(['transport_style'=>$transport]);
        $onSubmitSummary=$query->exists();
        if($onSubmitSummary){
            return ['status'=>'error','message'=>'还有其他未同意需求','level'=>7,'supplier_name'=>$supplier_name];
        }
        return ['status'=>'success'];
    }

    //时间验证
    public static function verifyInterceptDateRule($supplierSkus,$demandNumber){
        //时间规则默认通过
        return ['status'=>'success'];
        $dataConfig = DataControlConfig::find()->select('values')->where(['type'=>'overseas_rule'])->scalar();
        $timeconfig = explode(',',$dataConfig);
        if(empty($timeconfig)){
            $timeconfig = [3,7];
        }
//        $timeData = PlatformSummary::find()->select('create_time,update_time')->where(['demand_number'=>$demandNumber])->asArray()->one();
//        if(empty($timeData)){
//            return ['status'=>'error','message'=>'数据异常','level'=>6];
//        }
//        $create_time = isset($timeData['update_time'])&&!empty($timeData['update_time']) ? $timeData['update_time'] : $timeData['create_time'];
        $limitTime = $timeconfig[0]*60*60;
        $limitDay  = $timeconfig[1]*24*60*60;
        $pExist = PlatformSummary::find()
            ->where(['between','create_time',date('Y-m-d H:i:s',time()-$limitDay),date('Y-m-d H:i:s',time()-$limitTime)])
            ->andWhere(['in','sku',$supplierSkus])
            ->andWhere(['purchase_type'=>2])
            ->andWhere('demand_number<>:demand_number',[':demand_number'=>$demandNumber])
            ->andWhere(['not in','level_audit_status',[3,4,5]])
            ->exists();
        if($pExist){
            return ['status'=>'error','message'=>"离该供应商上次创建需求大于".$timeconfig[0]."小时少于".$timeconfig[1].'天','level'=>6];
        }
        return ['status'=>'success'];
    }

    public static function getVerPurchasePrice($supplierSkus,$warehouseCode,$transport,$updateData=[]){
        $query= PlatformSummary::find()
            ->alias('t')
            ->select('t.id,t.demand_number,t.purchase_quantity,q.supplierprice')
            ->leftJoin(ProductProvider::tableName().' a','t.sku=a.sku')
            ->leftJoin(SupplierQuotes::tableName().' q','a.quotes_id=q.id')
            ->where(['in','t.sku',$supplierSkus])
            ->andWhere(['a.is_supplier'=>1])
            ->andWhere(['t.purchase_warehouse'=>$warehouseCode])
            ->andWhere(['in','t.level_audit_status',[0,6,7]]);
        $query->andWhere(['transport_style'=>$transport]);
        $datas=$query ->asArray()->all();
        $price = 0;
        $demand_number = [];
        foreach ($datas as $data){
            if(isset($updateData[$data['id']])){
                $data['purchase_quantity'] = $updateData[$data['id']];
            }
            $demand_number[] = $data['demand_number'];
            $price += $data['purchase_quantity']*$data['supplierprice'];
        }
        return ['price'=>$price,'demand_number'=>$demand_number];
    }

    public static function verifyPassRule($transport,$warehouse_code){
        $exist = OverseasDemandPassRule::find()
            ->where(['in','warehouse_code',['all',$warehouse_code]])
            ->andWhere(['in','transport',[0,$transport]])
            ->andWhere(['status'=>1])
            ->exists();
        return $exist;
    }
    
    public static function agreeUpdateOrder($model, $product_quote)
    {
        $buyer = SupplierBuyer::find()->where(['supplier_code'=>$model->supplier_code,'type'=>2,'status'=>1])->select('buyer')->scalar();
        
        $purchase_model = PurchaseOrder::find()->alias("a")
            ->leftJoin(PurchaseDemand::tableName()." b", "a.pur_number = b.pur_number")
            ->leftJoin(PlatformSummary::tableName()." c", "b.demand_number = c.demand_number")
            ->where(['in','a.purchas_status',[1,2]])
            ->andwhere(['a.operation_type'=>2])
            ->andwhere(['a.supplier_code'=>$model->supplier_code])
            ->andwhere(['a.warehouse_code'=>$model->purchase_warehouse])
            ->andwhere(['a.is_drawback'=>$model->is_back_tax == 1 ? 2 : 1])
            ->andWhere(['c.transport_style'=>$model->transport_style])
            ->andWhere("c.agree_time > '2018-08-29 10:00:00'")
            ->andWhere("c.demand_status < 7")
            ->one();
        
        $price = $model->is_back_tax == 1 ? round($product_quote->supplierprice*(1+$model->pur_ticketed_point/100)) : $product_quote->supplierprice;
        if ($purchase_model) {
            $new_demand_model = new PurchaseDemand();
            $new_demand_model->pur_number = $purchase_model->pur_number;
            $new_demand_model->demand_number = $model->demand_number;
            $new_demand_model->create_id = $model->create_id;
            $new_demand_model->create_time = $model->create_time;
            $new_demand_model->save(false);
            
            $item_model = PurchaseOrderItems::find()->where(['pur_number'=>$purchase_model->pur_number,'sku'=>$model->sku])->one();
            if ($item_model) {
                $item_model->qty = $item_model->ctq += $model->purchase_quantity;
                $item_model->items_totalprice += $price*$model->purchase_quantity;
                $item_model->save(false);
            } else {
                $item_model = new PurchaseOrderItems();
                $item_model->pur_number = $purchase_model->pur_number;
                $item_model->sku = $model->sku;
                $item_model->name = !empty($model->product_name) ? $model->product_name : '';
                $item_model->base_price = $product_quote->supplierprice;
                $item_model->price = $price;
                $item_model->qty = $item_model->ctq = $model->purchase_quantity;
                $item_model->items_totalprice = $price*$model->purchase_quantity;
                $item_model->pur_ticketed_point = $model->pur_ticketed_point;
                $item_model->save(false);
            }
        } else {
            
            $supplierinfo = Supplier::find()->where(['supplier_code'=>$model->supplier_code])->one();
            
            $model_order = new PurchaseOrder();
            $model_order->pur_number      = CommonServices::getNumber('ABD');
            $model_order->operation_type  ='2';
            $model_order->warehouse_code = $model->purchase_warehouse;
            $model_order->supplier_code = $model->supplier_code;
            $model_order->e_supplier_name = $model_order->supplier_name = $supplierinfo->supplier_name;
            $model_order->created_at      = date('Y-m-d H:i:s');
            $model_order->creator         = Yii::$app->user->identity->username;
            $model_order->merchandiser  = $product_quote->default_Merchandiser;
            $model_order->buyer           = !empty($buyer) ? $buyer : 'admin';
            $model_order->purchas_status  = 1;//待确认
            $model_order->create_type     = 1;//创建类型
            $model_order->is_transit      = $model->is_transit;
            $model_order->purchase_type   = 2;//海外
            $model_order->e_account_type  =$model_order->account_type = $supplierinfo->supplier_settlement;
            $model_order->transit_warehouse   = $model->transit_warehouse;//中转
            $model_order->is_expedited    = $model->demand_is_expedited;//加急
            $model_order->pay_type = $supplierinfo->payment_method;
            $model_order->is_drawback = $model->is_back_tax == 1 ? 2 : 1;
            $model_order->pur_type = 0;
            $model_order->save(false);
            
            $model_order_type = new PurchaseOrderPayType();
            $model_order_type->pur_number              = $model_order->pur_number;
            $model_order_type->freight                 = 0;
            $model_order_type->discount                = 0;
            $model_order_type->real_price              = 0;
            $model_order_type->freight_formula_mode    = '';
            $model_order_type->settlement_ratio        = '';
            $model_order_type->purchase_source         = 2;
            $model_order_type->purchase_acccount       = '';
            $model_order_type->platform_order_number   = '';
            $model_order_type->save(false);
            
            $item_model = new PurchaseOrderItems();
            $item_model->pur_number = $model_order->pur_number;
            $item_model->sku = $model->sku;
            $item_model->name = !empty($model->product_name) ? $model->product_name : '';
            $item_model->base_price = $product_quote->supplierprice;
            $item_model->price = $price;
            $item_model->qty = $item_model->ctq = $model->purchase_quantity;
            $item_model->items_totalprice = $price*$model->purchase_quantity;
            $item_model->pur_ticketed_point = $model->pur_ticketed_point;
            $item_model->save(false);
            
            $new_demand_model = new PurchaseDemand();
            $new_demand_model->pur_number = $model_order->pur_number;
            $new_demand_model->demand_number = $model->demand_number;
            $new_demand_model->create_id = $model->create_id;
            $new_demand_model->create_time = $model->create_time;
            $new_demand_model->save(false);
        }
    }
}
