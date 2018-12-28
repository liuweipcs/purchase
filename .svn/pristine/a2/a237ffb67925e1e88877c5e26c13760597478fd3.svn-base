<?php

namespace app\models;


use app\config\Vhelper;
use app\services\CommonServices;
use Yii;
use yii\helpers\Html;
use app\services\PurchaseOrderServices;

/**
 * This is the model class for table "{{%platform_summary}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $platform_number
 * @property string $product_name
 * @property integer $purchase_quantity
 * @property string $purchase_warehouse
 * @property string $transit_warehouse
 * @property integer $is_transit
 * @property string $create_id
 * @property string $create_time
 * @property string $is_back_tax
 */
class PlatformSummary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%platform_summary}}';
    }
    public $uploadimgs;
    public $total_purchase_quantity;
    public $start_time;
    public $end_time;
    public $file_execl;
    public $pur_number;
    public $is_drawback;
    public $order_buyer;
    public $product_line;
    public $default_buyer;
    public $order_status;
    public $dimension;
    public $price;
    public $purchase_money;
    public $min_money;
    public $max_money;
    public $compact_number;
    public $product_is_new;
    public $tax_rate;
    public $is_expedited;
    public $currency_code;
    public $create_type;
    public $date_eta;
    public $base_price;
    public $account_type;
    public $pay_type;
    public $pay_percent;
    public $shipping_method;
    public $freight_payer;
    public $settlement_ratio;
    public $freight_formula_mode;
    public $purchase_acccount;
    public $platform_order_number;
    public $export_cname;
    public $declare_unit;
    public $product_link;
    public $cancel_cty;
    public $bad_qty;
    public $product_img;
    public $audit_time;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['purchase_quantity', 'is_transit','level_audit_status','purchase_type','pur_ticketed_point','is_back_tax'], 'integer'],
            [['create_time','agree_time','transit_number','transport_style','sales','is_back_tax','supplier_code','bh_type','demand_status'], 'safe'],
            [['sku', 'purchase_warehouse', 'transit_warehouse', 'create_id','agree_user','group_id'], 'string', 'max' => 30],
            [['platform_number'], 'string', 'max' => 20],
            [['sku'], 'trim'],
            [['audit_note','sales_note','purchase_note'], 'string', 'max' => 500],
            [['product_name','demand_number'], 'string', 'max' => 100],
            [['create_id'], 'default','value'=>!empty(Yii::$app->user->identity->username)?Yii::$app->user->identity->username:1],
            [['create_time'], 'default','value'=>date('Y-m-d H:i:s',time())],
            [['sku','purchase_quantity','product_name','platform_number','purchase_warehouse','product_category'], 'required'],
        ];
    }
    /**
     * 关联产品名
     * @return $this
     */
    public  function  getDesc()
    {
        return $this->hasOne(ProductDescription::className(), ['sku' => 'sku'])->where(['language_code'=>'Chinese']);
    }
    /**
     *库存综合查询表
     * @return $this
     */
    public function getStock()
    {
        return $this->hasOne(Stock::className(), ['sku' => 'sku']);
    }

    /**
     * 关联历史采购单供应商
     * @return \yii\db\ActiveQuery
     */
    public  function  getHistory()
    {
        return $this->hasOne(PurchaseHistory::className(), ['sku' => 'sku'])->orderBy('id desc');
    }
    /**
     * 关联历史采购单供应商
     * @return \yii\db\ActiveQuery
     */
    public  function  getHistoryB()
    {
        return $this->hasOne(PurchaseHistory::className(), ['sku' => 'sku']);
    }
    public  function  getSupplierQuotes()
    {
            return $this->hasOne(ProductProvider::className(), ['sku'=>'sku'])->where(['is_supplier'=>1]);
    }
    /**
     * 关联采购单号
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurNumber($sku)
    {
            return $this->hasOne(PurchaseOrderItems::className(), ['sku'=>'sku'])->where(['sku'=>$sku]);
    }


    /**
     * 通过中间表关联供应商表
     * @return $this
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['supplier_code'=>'supplier_code'])->via('supplierQuotes');
    }
    
    public function getSupplier2()
    {
        return $this->hasOne(Supplier::className(), ['supplier_code'=>'supplier_code']);
    }

    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
    }
    /**
     * 通过中间表关联产品分类表
     */
    public function getProductCategory()
    {
        return $this->hasOne(ProductCategory::className(), ['id'=>'product_category_id'])->via('product');
    }
    /**
     *库存综合查询表
     * @return $this
     */
    public function getSkusales()
    {
        return $this->hasOne(SkuSalesStatistics::className(), ['sku' => 'sku']);
    }

    //通过中间表关联采购单产品
    public function getPurOrderItem(){
        return $this->hasOne(PurchaseOrderItems::className(),['pur_number'=>'pur_number'])->where(['sku'=>$this->sku])->via('demand');
    }

    //通过中间表关联运费
    public function getShip(){
        return $this->hasOne(PurchaseOrderShip::className(),['pur_number'=>'pur_number'])->select('sum(freight) as freight')->via('demand');
    }

    //通过中间表关联运费
    public function getPay(){
        return $this->hasOne(PurchaseOrderPay::className(),['pur_number'=>'pur_number'])->via('demand');
    }

    //通过中间表关联到货记录
    public function getArrival(){
        return $this->hasMany(ArrivalRecord::className(),['purchase_order_no'=>'pur_number'])->where(['sku'=>$this->sku])->via('demand');
    }

    //通过中间表关联品检异常
    public function getQc(){
        return $this->hasMany(PurchaseQc::className(),['pur_number'=>'pur_number'])->where(['sku'=>$this->sku])->via('demand');
    }

    //通过中间表关联到货记录
    public function getReceive(){
        return $this->hasMany(PurchaseReceive::className(),['pur_number'=>'pur_number'])->where(['sku'=>$this->sku])->via('demand');
    }

    //通过中间表关联入库记录
    public function getWarehouseResults(){
        return $this->hasMany(WarehouseResults::className(),['pur_number'=>'pur_number'])->where(['sku'=>$this->sku])->via('demand');
    }

    //通过中间表关联采购单
    public function getPurOrder(){
        return $this->hasOne(PurchaseOrder::className(),['pur_number'=>'pur_number'])->via('demand');
    }

    //通过中间表获取默认报价
    public function getDefaultQuotes(){
        return $this->hasOne(SupplierQuotes::className(),['id'=>'quotes_id'])->via('defaultSupplier');
    }

    //sku关联默认供货商
    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(),['sku'=>'sku'])->where(['is_supplier'=>1]);
    }

    public function getDefaultSupplierLine(){
        return $this->hasOne(SupplierProductLine::className(),['supplier_code'=>'supplier_code'])->via('defaultSupplier')->where(['pur_supplier_product_line.status'=>1]);
    }
    /**
     * 关联中间表
     * @return \yii\db\ActiveQuery
     */
    public function getDemand(){
        return $this->hasOne(PurchaseDemand::className(),['demand_number'=>'demand_number']);
    }
    
    public function getPurchaseCompact()
    {
        return $this->hasOne(PurchaseCompactItems::className(), ['pur_number' => 'pur_number'])->where(['bind' => 1]);
    }
    
    public function getPurchaseOrderPay()
    {
        return $this->hasOne(PurchaseOrderPay::className(),['pur_number'=>'pur_number']);
    }

    public function getFbaAvgArrival(){
        return $this->hasOne(FbaAvgDelieryTime::className(),['sku'=>'sku']);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => Yii::t('app', 'ID'),
            'sku'                => Yii::t('app', 'sku'),
            'platform_number'    => Yii::t('app', '平台'),
            'product_name'       => Yii::t('app', '产品名'),
            'purchase_quantity'  => Yii::t('app', '数量'),
            'purchase_warehouse' => Yii::t('app', '采购仓'),
            'transit_warehouse'  => Yii::t('app', '中转仓'),
            'is_transit'         => Yii::t('app', '中转'),
            'is_purchase'        => Yii::t('app', '是否生成采购计划'),
            'create_id'          => Yii::t('app', '需求人'),
            'create_time'        => Yii::t('app', '需求时间'),
            'level_audit_status' => Yii::t('app', '需求状态'),
            'agree_user'         => Yii::t('app', '同意(驳回)人'),
            'agree_time'         => Yii::t('app', '同意(驳回)时间'),
            'demand_number'      => Yii::t('app', '需求单号'),
            'product_category'   => Yii::t('app', '产品类别'),
            'audit_note'         => Yii::t('app', '备注'),
            'transit_number'     => Yii::t('app', '中转数量'),
            'transport_style'    => Yii::t('app', '运输方式'),
            'sales_note'         => Yii::t('app', '销售备注'),
            'buyer'              => Yii::t('app', '采购员'),
            'purchase_time'      => Yii::t('app', '采购驳回时间'),
            'purchase_note'      => Yii::t('app', '采购驳回备注'),
            'group_id'           => Yii::t('app', '亚马逊小组'),
            'sales'              => Yii::t('app', '销售名称'),
            'is_back_tax'        => Yii::t('app', '是否退税'),
            'bh_type'            => Yii::t('app', '补货类型'),
        ];
    }

    /**
     * 统计数量
     * @param $sku
     * @param string $filed
     * @return false|null|string
     */
    public static function   getSku($product_id=null,$filed='*')
    {
        if($product_id)
        {
            return self::find()->select($filed)
                ->where(['id'=>$product_id])
                ->scalar();
        } else{

            return self::find()->select($filed)
                ->where(['id'=>$product_id])
                ->scalar();

        }

    }

    /**
     * 获取字段
     * @param $number
     * @param string $filed
     * @return array|null|\yii\db\ActiveRecord
     */
    public  static  function  getField($number,$filed='*')
    {
        return self::find()->select($filed)->where(['demand_number'=>$number])->one();
    }

    /**
     *
     */
    public  static  function  Updates($data)
    {
        if($data)
        {
            self::updateAll(['is_purchase'=>2],['in','demand_number',$data]);

        }
    }

    /**
     * 关联未处理备注一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseSuggestNote()//需求 FBA采购需求汇总-新增采购备注操作
    {
        return $this->hasOne(PurchaseSuggestNote::className(),['sku'=>'sku','warehouse_code'=>'purchase_warehouse','group_id'=>'group_id']);
    }
    
    //修改FBA需求优先推送状态
    public static function pushSummaryPriority($ids){
        if(!is_array($ids)){
            $ids = explode(',',$ids);
        }
        if(empty($ids)){
            return ['status'=>'error','message'=>'必要参数为空'];
        }
        $update_count = count($ids);
        $success_id = [];
        $count=0;
        foreach ($ids as $id){
            $update_num = self::updateAll(['is_push_priority'=>1],['id'=>$id,'is_push_priority'=>0,'is_purchase'=>1,'purchase_type'=>3,'level_audit_status'=>1]);
            if($update_num>=1){
                $success_id[]=$id;
                $count++;
            }
        }
        return ['status'=>'success','message'=>'优先推送需求{'.$update_count.'}条，成功{'.$count.'条}','success_id'=>$success_id];
    }
    /**
     * 获取需求状态
     */
    public static function getDemandStatus($demand_number)
    {
        $demand_status = self::find()->select('demand_status')->where(['demand_number'=>$demand_number])->scalar();
        return $demand_status?:false;
    }
    
    /**
     * 付款后更新需求付款状态 - 海外仓订单
     * @param unknown $requisition_number 请款单
     * @param unknown $payment_notice 付款备注
     */
    public static function overseasPayUpdateDemandPaystatus($requisition_number, $payment_notice)
    {
        $demand_maps = OrderPayDemandMap::find()->where(['requisition_number'=>$requisition_number])->all();
        if ($demand_maps) {
            foreach ($demand_maps as $map_model) {
                $demand_model = PlatformSummary::findOne(['demand_number'=>$map_model->demand_number]);
                $pur_number = PurchaseDemand::find()->select('pur_number')->where(['demand_number'=>$map_model->demand_number])->scalar();
                $price = PurchaseOrderItems::find()->select('price')->where(['pur_number'=>$pur_number,'sku'=>$demand_model->sku])->scalar();
                $totalprice = $price * $demand_model->purchase_quantity;
                
                $paid_amount = PurchaseOrderServices::getOverseasDemandPaidAmount($map_model->demand_number);
                $cancel_cty = PurchaseOrderServices::getOverseasDemandCancelCty($map_model->demand_number);
                $cancel_amount = $cancel_cty * $price;
                
                $demand_model->pay_status = 6;
                if ($totalprice <= $paid_amount + $cancel_amount + 0.05) {
                    $demand_model->pay_status = 5;
                }
                $demand_model->save(false);
                $message = "请款单付款\r\n付款备注:{$payment_notice}\r\n请款单:".$requisition_number;
                PurchaseOrderServices::writelog($map_model->demand_number, $message);
            }
        }
    }
}
