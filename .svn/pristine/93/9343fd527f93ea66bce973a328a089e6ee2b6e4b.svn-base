<?php

namespace app\models;

use app\models\base\BaseModel;
use app\config\Vhelper;
use Yii;
use app\models\WarehouseResults;
use yii\behaviors\TimestampBehavior;
use app\services\CommonServices;
use app\models\PurchaseCancelQuantity;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\data\Pagination;
use yii\data\ArrayDataProvider;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
/**
 * This is the model class for table "{{%purchase_order}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $warehouse_code
 * @property string $supplier_code
 * @property string $pur_type
 * @property string $shipping_method
 * @property string $operation_type
 * @property string $created_at
 * @property string $creator
 * @property string $account_type
 * @property string $pay_type
 * @property string $currency_code
 * @property string $pay_ship_amount
 * @property string $date_eta
 * @property string $tracking_number
 * @property integer $buyer
 * @property integer $merchandiser
 * @property string $reference
 * @property integer $create_type
 * @property integer $audit_return
 * @property integer $purchas_status
 * @property integer $carrier
 * @property integer $confirm_note
 * @property integer $submit_time
 *
 * @property PurchaseOrderItems[] $purchaseOrderItems
 */
class PurchaseOrder extends BaseModel
{
    public $page_size;
    public static $arrival_warehouse = [5, 6, 8, 9]; //部分到货，全到货，部分到货等待剩余、部分到货不等待剩余
    const AUDIT_PURCHASE_ORDER = [6, 7, 8, 9]; //审核通过的单：全到货、等待到货、部分到货等待剩余、部分到货不等待剩余

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                   // \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['audit_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
        ];
    }
    //单号类型
    public  $singletype;
    //单号
    public  $code;
    //创建时间类型
    public  $createtimetype;
    public $end_time;
    public $start_time;
    public $arrivaltype;
    public $sku_type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'warehouse_code', 'supplier_code', 'pur_type', 'creator','is_expedited'], 'required'],
            [['review_remarks'], 'string'],
            [['created_at','account_type','purchase_type','pay_type','date_eta','supplier_name','transit_warehouse','buyer','e_date_eta','e_supplier_name','is_expedited','submit_time','request_payout_type'], 'safe'],
            [['create_type', 'audit_return', 'purchas_status','is_drawback','review_status','is_check_goods'], 'integer'],
            [['pur_number', 'creator'], 'string', 'max' => 20],
            [['warehouse_code', 'supplier_code', 'reference'], 'string', 'max' => 30],
            [['currency_code'], 'string', 'max' => 10],
            [['pur_number'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                => Yii::t('app', 'ID'),
            'pur_number'        => Yii::t('app', '采购单号'),
            'pay_number'        => Yii::t('app', '支付单号'),
            'warehouse_code'    => Yii::t('app', '仓库'),
            'supplier_code'     => Yii::t('app', '供应商'),
            'pur_type'          => Yii::t('app', '补货方式'),
            'shipping_method'   => Yii::t('app', '供应商运输'),
            'operation_type'    => Yii::t('app', '运营方式'),
            'created_at'        => Yii::t('app', '创建时间'),
            'creator'           => Yii::t('app', '创建人'),
            'account_type'      => Yii::t('app', '结算方式'),
            'e_account_type'      => Yii::t('app', '结算方式-对比使用'),
            'pay_type'          => Yii::t('app', '支付方式'),
            'currency_code'     => Yii::t('app', '币种'),
            'is_expedited'      => Yii::t('app', '加急'),
            'date_eta'          => Yii::t('app', '预计到货时间'),
            'e_date_eta'          => Yii::t('app', '预计到货时间-对比使用'),
            'e_supplier_name'          => Yii::t('app', '供应商名称-对比使用'),
            'buyer'             => Yii::t('app', '采购员'),
            'merchandiser'      => Yii::t('app', '跟单员'),
            'reference'         => Yii::t('app', '参考号'),
            'create_type'       => Yii::t('app', '创建类型'),
            'audit_return'      => Yii::t('app', '审核退回'),
            'purchas_status'    => Yii::t('app', '状态'),
            'audit_time'        => Yii::t('app', '审核时间'),
            'auditor'           => Yii::t('app', '审核人'),
            'carrier'           => Yii::t('app', '承运商'),
            'note'              => Yii::t('app', '备注'),
            'transit_warehouse' => Yii::t('app', '中转仓库'),
            'review_status' => Yii::t('app', '审核状态'),
            'review_remarks' => Yii::t('app', '审核操作记录'),
            'request_payout_type'=>Yii::t('app','请款方式')
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItems::className(), ['pur_number' => 'pur_number']);
    }
    /**
     * @return 当确认订单后--有确认数量后使用
     */
    public function getPurchaseOrderItemsCtq()
    {
        return $this->hasMany(PurchaseOrderItems::className(), ['pur_number' => 'pur_number'])->andWhere(['OR', ['>', 'ctq', 0], ['=', 'is_cancel', 2]]);
    }
    /**
     * @return 当确认订单后--有确认数量后使用-数组
     */
    public function getPurchaseOrderItemsCtqArr()
    {
        return $this->hasMany(PurchaseOrderItems::className(), ['pur_number' => 'pur_number'])->andWhere(['OR', ['>', 'ctq', 0], ['=', 'is_cancel', 2]])->asArray();
    }
    /**
     * 关联未作废的sku
     */
    public function getNoCancelPurchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItems::className(), ['pur_number' => 'pur_number'])->andWhere(['=', 'is_cancel', 2]);
    }

    /**
     * 关联供应商
     * @return \yii\db\ActiveQuery
     */
    public  function  getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code']);
    }

    /**
     * 关联供应商联系方式一对一
     * @return \yii\db\ActiveQuery
     */
    public function  getSupplierContent()
    {
        return $this->hasOne(SupplierContactInformation::className(), ['supplier_code' => 'supplier_code']);
    }
    /**
     * 关联快递商
     * @return \yii\db\ActiveQuery
     */
    public  function  getLogistics()
    {
        return $this->hasOne(LogisticsCarrier::className(),['id'=>'carrier']);
    }
    /**
     * 关联快递商记录
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrderShip()
    {
        return $this->hasOne(PurchaseOrderShip::className(),['pur_number'=>'pur_number'])->orderBy('id asc');
    }

    public function getFbaOrderShip()
    {
        return $this->hasMany(PurchaseOrderShip::className(),['pur_number'=>'pur_number']);
    }

    public function getFbaOrderShipFreight()
    {
        return $this->hasOne(PurchaseOrderShip::className(),['pur_number'=>'pur_number'])->where('Not isNull(freight)');
    }
    /**
     * 关联采购备注
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrderNote()
    {
        return $this->hasOne(PurchaseNote::className(),['pur_number'=>'pur_number'])->orderBy('id asc');
    }
    /**
     * 关联采购拍单号
     * @return \yii\db\ActiveQuery
     */
    public  function  getOrderOrders()
    {
        return $this->hasOne(PurchaseOrderOrders::className(),['pur_number'=>'pur_number'])->orderBy('id asc');
    }
    /**
     * 关联采购单一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseOrderPay()
    {
        return $this->hasOne(PurchaseOrderPay::className(),['pur_number'=>'pur_number']);
    }

    public function getPurchaseOrderPays()
    {
        return $this->hasMany(PurchaseOrderPay::className(),['pur_number'=>'pur_number']);
    }
    /**
     * 关联采购单与需求单号关系表
     * @return \yii\db\ActiveQuery
     */
    public function getDemand(){
        return $this->hasMany(PurchaseDemand::className(),['pur_number'=>'pur_number']);
    }
    /**
     * 关联需求表一对多
     */
    public function getPlatformSummary()
    {
        return $this->hasMany(PlatformSummary::className(),['demand_number'=>'demand_number'])->via('demand');
    }
    /**
     * 关联账号表一对一
     * @return $this
     */
    public function getPurchaseOrderAccount()
    {
        return $this->hasOne(PurchaseOrderAccount::className(),['pur_number'=>'pur_number']);
    }

    /**
     * 关联订单优惠表 一对一
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseDiscount()
    {
        return $this->hasOne(PurchaseDiscount::className(),['pur_number'=>'pur_number']);
    }


    public function getPurchaseOrderBreakage()
    {
        return $this->hasMany(PurchaseOrderBreakage::className(),['pur_number'=>'pur_number']);
    }

    /**
     * 关联订单支付类型表 一对一
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderPayType()
    {
        return $this->hasOne(PurchaseOrderPayType::className(),['pur_number'=>'pur_number']);
    }

    public function getPurchaseOrderPayDetail()
    {
        return $this->hasMany(PurchaseOrderPayDetail::className(),['pur_number'=>'pur_number']);
    }

    public function getArrival(){
        return $this->hasMany(ArrivalRecord::className(),['purchase_order_no'=>'pur_number']);
    }

    public function getPurchaseCompact()
    {
        return $this->hasOne(PurchaseCompactItems::className(), ['pur_number' => 'pur_number'])->where(['bind' => 1]);
    }

    //获取采购单退款数据
    public function getPurchaseOrderRefund(){
        return $this->hasMany(PurchaseOrderReceipt::className(),['pur_number'=>'pur_number']);
    }
    /**
     * 关联合同中间表
     */
    public function getPurchaseCompactItems()
    {
        return $this->hasOne(PurchaseCompactItems::className(), ['pur_number' => 'pur_number'])->where(['bind'=>1]);
    }

    //根据sku获取上一次采购单的拍单号
    public static function getLastPlatformNum($sku){//部分页面增加账号的显示
        $items = PurchaseOrderItems::find()->where(['sku'=>$sku])->orderBy('id desc')->one();
        $platform_order_number = '';
        if($items){
            $platform =  PurchaseOrderPayType::find()->where(['pur_number'=>$items->pur_number])->one();
            $platform_order_number = !empty($platform->platform_order_number) ? $platform->platform_order_number : '';
            if(!$platform_order_number){
                $orderOrders = PurchaseOrderOrders::find()->where(['pur_number'=>$items->pur_number])->orderBy('id desc')->one();
                $platform_order_number = !empty($orderOrders->order_number) ? $orderOrders->order_number : '';
            }
        }
        return $platform_order_number;
    }
    /**
     * 修改订单状态
     * @param $ids
     * @param $type
     * @return bool|void
     */
    public  static  function  UpdatePurchaseStatus($ids,$type)
    {
        if (!$ids) return;
        $map['id']=strpos($ids,',') ? explode(',',$ids):$ids;
        $orders=self::find()->select('id,purchas_status,is_push')->where($map)->all();
        foreach ($orders as $v)
        {
            if (($v->purchas_status != $type) && ($v->purchas_status==1 || $v->purchas_status==2))
            {
                $v->purchas_status=$type;
                $v->is_push=0;

                //表修改日志-更新
                $change_content = TablesChangeLog::updateCompare($v->attributes, $v->oldAttributes);
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                $result =$v->save(false);
            } else {
                $result= false;
            }
        }
        return $result;
    }

    public function  PurchaseOrder($data)
    {
        foreach ($data as $v)
        {
            $model                  = self::findOne(['pur_number'=>$v['pur_number']]);
            if(!in_array($model->purchas_status,array(1,2))){
                throw new HttpException(500,'存在采购单状态异常的数据');
            }
            if(!empty($v['date_eta']) && $model->date_eta != $v['date_eta']){
                $model->e_date_eta= $v['date_eta'];
            }

            if(!empty($v['account_type']) && $model->account_type != $v['account_type']){
                $model->e_account_type= $v['account_type'];
            }

            if(!empty($v['supplier_name']) && $model->supplier_name != $v['supplier_name']){
                $model->e_supplier_name= $v['supplier_name'];
            }

            $model->date_eta        = !empty($v['date_eta'])?$v['date_eta']:date('Y-m-d H:i:s',time());

            if (!empty($v['account_type'])) {
                $model->account_type        = $v['account_type'];
            }
            if (!empty($v['pay_type'])) {
                $model->pay_type        = $v['pay_type'];
            }
            $model->supplier_code        = $v['supplier_code'];
            $model->supplier_name        = $v['supplier_name'];
            $model->shipping_method = $v['shipping_method'];
            if(!empty($v['is_expedited'])) {
                $model->is_expedited = $v['is_expedited'];
            }
            //$model->pay_ship_amount = $v['pay_ship_amount'];
            //$model->tracking_number = $v['tracking_number'];
            if (!empty($v['submit'])) {
                $model->purchas_status  = $v['submit']==2?2:1;
            }
            $model->audit_return    = 2;
            $model->buyer           = Yii::$app->user->identity->username;
            $model->submit_time     = date('Y-m-d H:i:s',time());
            $model->is_drawback     = !empty($v['is_drawback'])?$v['is_drawback']:'1';
            if(isset($v['is_transit']) && $v['is_transit']==1)
            {
                $model->transit_warehouse = $v['transit_warehouse'];
                $model->is_transit        = $v['is_transit'];
            }

            if(isset($v['source'])) {
                $model->source = $v['source'];
            }

            //$model->confirm_note    = isset($v['confirm_note'])?:'no';
            //添加采购单日志
            $s = [
                'pur_number'=>$v['pur_number'],
                'note'      =>'采购确认',
            ];
            PurchaseLog::addLog($s);

            //表修改日志
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            $status                 = $model->save(false);

        }

        return $status;
    }

    public function  FbaPurchaseOrderItems($data,$orderData,$orderTaxes){
        $order=[];
        if(!empty($orderData)){
            foreach ($orderData as $v){
                $order[$v['pur_number']]['is_drawback']=isset($v['is_drawback']) ? $v['is_drawback'] : 1;
            }
        }
        if(!empty($orderTaxes['taxes'])){
            foreach ($orderTaxes['taxes'] as $taxes){
                $order[$taxes['pur_number']]['taxes'][$taxes['sku']]=isset($taxes['taxes']) ?$taxes['taxes'] :0;
            }
        }
        foreach ($data as $v)
        {
            $model                      = PurchaseOrderItems::findOne(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']]);

            if(!empty($v['price']) && $model->price != $v['price']){
                $model->e_price= $v['price'];
            }

            if(!empty($v['ctq']) && $model->ctq != $v['ctq']){
                $model->e_ctq= $v['ctq'];
            }
            $model->price               = $v['price'];
            $model->ctq                 = $v['ctq'];
            $base_price = ProductProvider::find()
                ->select('supplierprice')
                ->alias('t')
                ->leftJoin(SupplierQuotes::tableName().' sq','t.quotes_id=sq.id')
                ->where(['t.is_supplier'=>1,'t.sku'=>$v['sku']])
                ->scalar();
            $pur_ticketed_point = !empty($order[$v['pur_number']]['taxes'][$v['sku']])&&!empty($order[$v['pur_number']]['is_drawback'])&&$order[$v['pur_number']]['is_drawback']==2 ?$order[$v['pur_number']]['taxes'][$v['sku']] :0;
            $model->base_price          = $base_price ? $base_price : 0;
            $model->pur_ticketed_point  = $pur_ticketed_point;
            $model->items_totalprice    = $v['ctq']*$v['price'];
            $taxeModel = PurchaseOrderTaxes::find()->where(['sku'=>$v['sku'],'pur_number'=>$v['pur_number']])->one();
            if(empty($taxeModel)){
                $taxeModel =  new PurchaseOrderTaxes();
            }
            $taxeModel->pur_number = $v['pur_number'];
            $taxeModel->sku        = $v['sku'];
            $taxeModel->is_taxes   = !empty($order[$v['pur_number']]['is_drawback']) ? $order[$v['pur_number']]['is_drawback'] : 1;
            $taxeModel->taxes      = $pur_ticketed_point;
            $taxeModel->create_id  = Yii::$app->user->id;
            $taxeModel->create_time= date('Y-m-d H:i:s',time());
            $taxeModel->save(false);

            $plink=\app\models\SupplierQuotes::getUrl($v['sku']);
            if(!empty($v['product_link']) && $plink != $v['product_link']){
                $model->product_link  = $v['product_link'];
            }

            //表修改日志
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_items', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $status                     = $model->save(false);

        }
        return $status;
    }

    public  function  PurchaseOrderItems($data)
    {
        foreach ($data as $v)
        {
            $model                      = PurchaseOrderItems::findOne(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']]);

            if(!empty($v['price']) && $model->price != $v['price']){
                $model->e_price= $v['price'];
            }

            if(!empty($v['ctq']) && $model->ctq != $v['ctq']){
                $model->e_ctq= $v['ctq'];
            }
            $model->price               = $v['price'];
            $model->ctq                 = $v['ctq'];
            $model->items_totalprice    = $v['ctq']*$v['price'];

            $plink=\app\models\SupplierQuotes::getUrl($v['sku']);
            if(!empty($v['product_link']) && $plink != $v['product_link']){
                $model->product_link  = $v['product_link'];
            }

            //表修改日志
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_items', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            $status                     = $model->save(false);

        }
        return $status;
    }

    /**
     * 根据id和预计到货时间判断修改到货状态
     * @param $id
     * @param $time
     */
    public static function UpdateArrival($pur_number,$time)
    {

        if (!empty($time) && strtotime($time) < time())
        {
            $model             = self::findOne(['pur_number' => $pur_number]);
            $arr = ['6','5'];
            $model->is_arrival = in_array($model->purchas_status,$arr)? 0:1;
            $model->save(false);
        }
    }

    /**
     * 保存手动创建的采购单,分别生成主表和从表记录
     * @param array $data
     * @return bool
     */
    public static function Savepurdata($data=array())
    {

        $model        = new self();

        $suppmodel    = new Supplier();
        //采购订单主表
        $model->pur_number            = self::getPurnumber($data['purdesc']['purchase_type']);
        $model->warehouse_code    = $data['purdesc']['warehouse_code'];
        $model->is_transit        = $data['purdesc']['is_transit'];
        $model->transit_warehouse = $data['purdesc']['transit_warehouse'];
        $model->pur_type          = isset($data['purdesc']['pur_type']) ? $data['purdesc']['pur_type'] : '1';
        $model->supplier_code     = $data['purdesc']['supplier_code'];

        // 订单生成时的结算方式，以供应商的结算方式为准，如果没有结算方式，默认为：1款到发货
        $supplier                 = $suppmodel->findOne(['supplier_code' => $model->supplier_code]);
        if($supplier) {
            $model->supplier_name = $supplier->supplier_name; // 供应商名字
            $model->account_type = $supplier->supplier_settlement; // 供应商结算方式
            $model->pay_type = $supplier->payment_method; // 供应商支付方式
        } else {
            $model->account_type = 1; // 供应商结算方式
            $model->pay_type = 2; // 供应商结算方式
        }

        $model->buyer             = isset($data['purdesc']['buyer'])?$data['purdesc']['buyer']:Yii::$app->user->identity->username;
        $model->currency_code     = isset($data['purdesc']['currency_code'])?$data['purdesc']['currency_code']:'RMB';
        //$model->tracking_number   = $data['purdesc']['tracking_number'];
        $model->shipping_method   = isset($data['purdesc']['shipping_method']) ? $data['purdesc']['shipping_method'] : 2;
        //$model->pay_ship_amount   = $data['purdesc']['pay_ship_amount'] ? $data['purdesc']['pay_ship_amount'] : '0';
        $model->operation_type    = isset($data['purdesc']['operation_type']) ? $data['purdesc']['operation_type'] : 2;
        $model->merchandiser      = isset($data['purdesc']['merchandiser'])?$data['purdesc']['merchandiser']:Yii::$app->user->identity->username;
        $model->reference         = isset($data['purdesc']['reference']) ? $data['purdesc']['reference'] :date('YmdHis');
        $model->creator           = Yii::$app->user->identity->username;
        $model->create_type       = 2;
        $model->is_expedited      = $data['purdesc']['is_expedited'];
        $model->purchase_type     = $data['purdesc']['purchase_type'];
        if (isset($data['purdesc']['is_back_tax'])) {
            $model->is_drawback = $data['purdesc']['is_back_tax'] == 1 ? 2 : 1;
        }
        //如果仓库为：东莞仓FBA虚拟仓 则不含税
        if (isset($data['purdesc']['warehouse_code']) && ($data['purdesc']['warehouse_code']=='FBA_SZ_AA') ) {
            $model->is_drawback = 1;
        }

           //添加采购单日志
            $s = [
                'pur_number'=>$model->pur_number,
                'note'      =>'手动创建采购计划单',
            ];
            PurchaseLog::addLog($s);
            if($model->save(false))
            {
                //表修改日志-新增
                $change_content = "insert:新增id值为{$model->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
                return $model->attributes['pur_number'];
            }

    }

    /**
     * 手动创建采购单从表
     * @param $pur_number
     * @return bool
     */
    public static function OrderItems($pur_number,$data,$type=null,$wcode=null,$tcode=null)
    {

        //$quotesmodel  = new SupplierQuotes();

        //$product_id = PurchaseTemporary::find()->where(['create_id' => Yii::$app->user->id])->groupBy('sku')->asArray()->all();
        if (!empty($data))
        {

            foreach ($data as $k => $v)
            {
                $promodel     = new Product();
                $histroy      = new PurchaseHistory();
                //采购订单详表
                $product                 = $promodel->findOne(['sku'=>$v['sku']]);
                $prodesc                 = ProductDescription::find()->where(['sku'=>$v['sku']])->orderBy('id desc')->one();
                if(empty($product) && empty($prodesc))
                {
                    return false;
                }
                //$suppquotes = $quotesmodel->findOne(['product_sku' => $product->sku]);
                //$histroys                = $histroy->findOne(['sku'=>$v['sku']]);
                $sb= PurchaseOrderItems::findOne(['pur_number'=>$pur_number,'sku'=>$v['sku']]);
                //Vhelper::dump($sb);
                if($sb)
                {
                    $sb->qty         += $v['purchase_quantity'];

                    //表修改日志-更新
                    $change_content = TablesChangeLog::updateCompare($sb->attributes, $sb->oldAttributes);
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                        'change_type' => '2', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);

                    $sb->save(false);
                } else{
                    $itemsmodel              = new PurchaseOrderItems();
                    $itemsmodel->pur_number  = $pur_number;
                    $itemsmodel->qty         = !empty($v['purchase_quantity'])?$v['purchase_quantity']:'10';
                    $itemsmodel->sku         = !empty($v['sku']) ? $v['sku'] : $product->sku;
                    $itemsmodel->name        = !empty($v['title']) ? $v['title'] : $prodesc->title;
                    $itemsmodel->price       = !empty($v['purchase_price']) ? $v['purchase_price'] : '10';
                    $itemsmodel->product_img = !empty($product->uploadimgs) ? $product->uploadimgs : '';
                    $itemsmodel->save(false);

                    //表修改日志-新增
                    $change_content = "insert:新增id值为{$itemsmodel->id}的记录";
                    $change_data = [
                        'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                        'change_type' => '1', //变动类型(1insert，2update，3delete)
                        'change_content' => $change_content, //变更内容
                    ];
                    TablesChangeLog::addLog($change_data);
                }

            }

            //表修改日志-删除
            $create_id = Yii::$app->user->id;
            $change_content = "delete:删除create_id值为{$create_id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_pay_type', //变动的表名称
                'change_type' => '3', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);

            PurchaseTemporary::deleteAll(['create_id'=>Yii::$app->user->id]);


        } else {
            return false;
        }
    }

    /**
     * 通过采购单号设置qc的状态
     * @param $pur_number
     * @param $stauts
     */
    public static  function  setQcStatus($pur_number,$stauts,$is_returd=null)
    {
        switch($stauts)
        {
            case 1:
                //销毁，采购方承担
                $s = 3;
                break;
            case 2:
                //销毁供应商承担，然后是给采购单加上退款状态
                $s = 4;
                $is_returd =1;
                break;
            case 3:
                //退回，供应商退回款项
                $s = 5;
                //$is_returd =1;
                break;
            case 4:
                //换货，供应商重新发货
                $s = 6;
                break;
            case 5:
                //不良品上架
                $s = 7;
                break;
            default:
                //默认是销毁，采购方承担
                $s = 1;
                break;
        }
        $is_returd = !empty($is_returd)?$is_returd:'';
        self::updateAll(['qc_abnormal_status'=>$s,'complete_type'=>1,'refund_status'=>$is_returd],['pur_number'=>$pur_number]);
    }

    /**
     * 得到采购单号
     * @param $stauts
     * @return int
     */
    public static function getPurnumber($stauts)
    {
        switch($stauts)
        {
            case 1:
                //国内
                $s = CommonServices::getNumber('PO');
                break;
            case 2:
                //海外
                $s = CommonServices::getNumber('ABD');
                break;
            case 3:
                //FBA
                $s = CommonServices::getNumber('FBA');
                break;
            default:
                //默认国内
                $s = CommonServices::getNumber('PO');
                break;
        }

        return $s;
    }

    public static function createFbaPurOrder($data)
    {
        $tran = Yii::$app->db->beginTransaction();
        try{
            if(empty($data)){
                throw new HttpException(500,'没有符合要求的采购需求！');
            }
            $back_taxs = [];
            foreach($data as $value){
                $vsupplier = !empty($value->defaultSupplier) ? $value->defaultSupplier->supplier_code : '';
                if(empty($vsupplier)){
                    throw new HttpException(500,'有选中商品缺少默认供应商');
                }
                $wdata[]   = $value['purchase_warehouse'];
                $sdata[]   = $vsupplier;
                $transit[] = $value['is_transit'];
                $tranware[]= $value['transit_warehouse'];
                $pur_type[]  = $value['purchase_type'];
                $demand[]  = $value['demand_number'];
                $back_taxs[] = $value['is_back_tax'] == 1 ? 1 : 2;
            }
            $warehouse = array_unique($wdata);
            $supplier  = array_unique($sdata);
            $istransit = array_unique($transit);
            $tranw     = array_unique($tranware);
            $type      = array_unique($pur_type);
            $back_taxs = array_unique($back_taxs);
            if(count($warehouse)>1 || count($supplier)>1 || count($istransit)>1 || count($tranw)>1){
                throw new HttpException(500,'所选采购需求有多个供应商或采购仓库或中转属性或不同的退税不能生成一个采购单！');
            }
            if (count($back_taxs) > 1) {
                throw new HttpException(500,'所选采购需求有不同的【是否退税】，不能生成一个采购单！');
            }
            $orderdata['purdesc']['warehouse_code']    = $warehouse[0];
            $orderdata['purdesc']['is_transit']        = $istransit[0];
            $orderdata['purdesc']['transit_warehouse'] = $tranw[0];
            $orderdata['purdesc']['supplier_code']     = $supplier[0];
            $orderdata['purdesc']['is_expedited']      = 1;
            $orderdata['purdesc']['purchase_type']     = $type[0];
            $orderdata['purdesc']['is_back_tax']       = $back_taxs[0];
            $pur_number                                = self::Savepurdata($orderdata);
            PlatformSummary::Updates($demand);
            PurchaseDemand::saveOne($pur_number,$data);
            $itemsave = self::FbaOrderItems($pur_number, $data,3);
            if($itemsave == false){
                throw new HttpException(500,'采购单商品添加失败');
            }
            $tran->commit();
            $result = ['status'=>'success','message'=>'采购单生成成功！'];
        }catch(HttpException $e){
            $tran->rollBack();
            $result = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $result;
    }

    // 根据勾选的需求，生成采购单
    public static function createFbaPurOrder2($data)
    {
        $list = [];
        foreach($data as $value) {
            $supplier = $value->defaultSupplier;
            if(!Yii::$app->cache->add($value->demand_number,time(),60)){
                return [
                    'error' => 1,
                    'message' => '有需求存在重复生成采购单的可能，请等待一分钟之后重新操作,需求单号：'.$value->demand_number,
                ];
            }
            if($supplier) {
                //$is_back_tax = $value->is_back_tax == 1 ? 1 : 2;
                $code = $supplier->supplier_code.'_'.$value->purchase_warehouse.'_'.$value->is_transit.'_'.$value->transit_warehouse;
                $value->supplier_code = $supplier->supplier_code;
                $list[$code][] = $value;
            } else {
                break;
            }
        }
        if(empty($list)) {
            return [
                'error' => 1,
                'message' => '没有符合要求的采购需求'
            ];
        }
        try {
            $success = 0;
            $defeat  = 0;
            foreach($list as $v) {
                $tran = Yii::$app->db->beginTransaction();
                $orderdata['purdesc']['warehouse_code']    = $v[0]['purchase_warehouse'];
                $orderdata['purdesc']['is_transit']        = $v[0]['is_transit'];
                $orderdata['purdesc']['transit_warehouse'] = $v[0]['transit_warehouse'];
                $orderdata['purdesc']['supplier_code']     = $v[0]['supplier_code'];
                $orderdata['purdesc']['is_expedited']      = 1;
                $orderdata['purdesc']['purchase_type']     = $v[0]['purchase_type'];
                $orderdata['purdesc']['is_back_tax']       = $v[0]['purchase_warehouse'] =='TS' ? 1 :2;
                $pur_number                                = self::Savepurdata($orderdata);

                $demand = [];
                foreach($v as $i) {
                    $demand[] = $i->demand_number;
                }

                PlatformSummary::Updates($demand);
                PurchaseDemand::saveOne($pur_number, $v);
                $itemsave = self::FbaOrderItems($pur_number, $v, 3);

                if($itemsave == false) {
                    $defeat++;
                    $tran->rollBack();
                } else {
                    $success++;
                    $tran->commit();
                }
            }
            $result = [
                'error' => 0,
                'message' => "采购单生成成功，共生成 {$success} 条采购单，失败 {$defeat} 条"
            ];
        } catch(\Exception $e) {
            $result = [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        }
        return $result;
    }

    public static function FbaOrderItems($pur_number,$data)
    {
        if (!empty($data))
        {

            foreach ($data as $k => $v)
            {
                $promodel     = new Product();
                //采购订单详表
                $product                 = $promodel->findOne(['sku'=>$v['sku']]);
                $prodesc                 = ProductDescription::find()->where(['sku'=>$v['sku']])->orderBy('id desc')->one();
                if(empty($product) && empty($prodesc))
                {
                    return false;
                }
                $sb= PurchaseOrderItems::findOne(['pur_number'=>$pur_number,'sku'=>$v['sku']]);
                if($sb)
                {
                    $sb->qty         += $v['purchase_quantity'];
                    $sb->save(false);
                } else{
                    $itemsmodel              = new PurchaseOrderItems();
                    $itemsmodel->pur_number  = $pur_number;
                    $itemsmodel->qty         = !empty($v['purchase_quantity'])?$v['purchase_quantity']:'10';
                    $itemsmodel->sku         = !empty($v['sku']) ? $v['sku'] : $product->sku;
                    $itemsmodel->name        = !empty($v['product_name']) ? $v['product_name'] : $prodesc->title;
                    $itemsmodel->price       = !empty($v->defaultQuotes) ? $v->defaultQuotes->supplierprice : '10';
                    $itemsmodel->base_price  = !empty($v->defaultQuotes) ? $v->defaultQuotes->supplierprice : '10';
                    $itemsmodel->product_img = !empty($product->uploadimgs) ? $product->uploadimgs : '';
                    $itemsmodel->pur_ticketed_point       = !empty($v->pur_ticketed_point) ? $v->pur_ticketed_point : 0;
                    if($itemsmodel->save() == false){
                        return false;
                    }
                }

            }
            return true;
        } else {
            return false;
        }
    }
    /**
     * 获取采购单状态
     * @param $sku
     * @return bool|void
     */
    public static function getPurchasStatus($pur_number) {
        return self::find()->where(['pur_number'=>$pur_number])->one()['purchas_status'];
    }

    /**
     * 获取采购员
     * @param $sku
     * @return bool|void
     */
    public static function getBuyer($pur_number) {
        return self::find()->select('buyer')->where(['pur_number'=>$pur_number])->one()['buyer'];
    }

    /**
     * 获取采购单创建时间
     * @param $sku
     * @return bool|void
     */
    public static function getCreatedAt($pur_number) {
        return self::find()->where(['pur_number'=>$pur_number])->one()['created_at'];
    }
    /**
     * 获取采购单审核时间
     * @param $sku
     * @return bool|void
     */
    public static function getAuditTime($pur_number) {
        return self::find()->select('audit_time')->where(['pur_number'=>$pur_number])->one()['audit_time'];
    }
    /**
     * 获取供货商名称
     * @param $sku
     * @return bool|void
     */
    public static function getSupplierName($pur_number) {
        return self::find()->select('supplier_name')->where(['pur_number'=>$pur_number])->one()['supplier_name'];
    }
    /**
     * 获取预计到货时间
     * @param $sku
     * @return bool|void
     */
    public static function getDateEta($pur_number) {
        return self::find()->select('date_eta')->where(['pur_number'=>$pur_number])->one()['date_eta'];
    }

    /**
     * 取消部分到货等待剩余
     * @param $id
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function cancelPartialArrival($id){
        $transaction=\Yii::$app->db->beginTransaction();
        try {
            // 获取到采购单数据
            $order_info = self::findOne(['id' => $id]);
            //获取采购类型
            $purchase_type = $order_info->purchase_type;
            // 改变 采购单状态
            $order_info->purchas_status = 9;
            $order_info->is_push = 0;
            $order_info->save(false);

            // FBA采购单
            $items_model = PurchaseOrderItems::find()->where(['pur_number' => $order_info->pur_number])->all();
            $pay_model = PurchaseOrderPay::find()->where(['pur_number' => $order_info->pur_number])->one();

            $confirm = 0;
            $receipt = 0;
            $purchase_price = 0;
            $pay_price = 0; //整个采购单，到货总价
            if (!empty($items_model) && $purchase_type == 1) { //国内采购单
                foreach ($items_model as $k => $v) {
                    //实际入库数量
                    $results = WarehouseResults::getResults($v->pur_number, $v->sku, 'instock_user,instock_date,arrival_quantity');
                    $arrival_quantity = !empty($results->arrival_quantity) ? $results->arrival_quantity : '0';

                    //单个sku到货总价
                    $items_totalprice = $arrival_quantity * $v->price;
                    // 所有sku到货总价
                    $pay_price += $arrival_quantity * $v->price;

                    $items_model[$k]->items_totalprice = $items_totalprice;
                    $result = $items_model[$k]->save();

                    //记录要减去在途的数量
                    //未到货数量
                    $weidaohuo = $v->ctq-$arrival_quantity;//总数-到货数量
                    if($result && $weidaohuo>0){
                        $refundQuantity = new PurchaseOrderRefundQuantity();
                        $refundQuantity->sku = $v->sku;
                        $refundQuantity->name = $v->name;
                        $refundQuantity->refund_qty = $weidaohuo;
                        $refundQuantity->purchase_qty = $v->ctq;
                        $refundQuantity->price = $v->price;
                        $refundQuantity->pur_number = $v->pur_number;
                        $refundQuantity->requisition_number = '';//因为作废订单的采购单没有付款  没有收款记录
                        $refundQuantity->refund_status = 0;
                        $refundQuantity->creator = Yii::$app->user->identity->username;
                        $refundQuantity->created_at = date('Y-m-d H:i:s', time());
                        $refundQuantity->is_cancel = 1;//作废订单的标记
                        $refundQuantity->save();
                    }
                }
                if (!empty($pay_model)) {
                    $pay_model->pay_price = $pay_price;
                    $pay_model->save();
                }
            } else if (!empty($items_model) && $purchase_type == 3) { //FBA采购单
                foreach ($items_model as $k => $v) {
                    //实际入库数量
                    //$results = WarehouseResults::getResults($v->pur_number, $v->sku, 'instock_user,instock_date,arrival_quantity');
                    //$arrival_quantity = !empty($results->arrival_quantity)?$results->arrival_quantity:'0';

                    //获取确认数量
                    //$confirm += $v->ctq;
                    //获取收货数量
                    //$receipt += $v->rqy;
                    //采购总单价
                    //$purchase_price += $v->ctq * $v->price;

                    // 单个sku到货总价
                    $items_totalprice = $v->rqy * $v->price;
                    //所有sku到货总价
                    $pay_price += $v->rqy * $v->price;

                    $items_model[$k]->items_totalprice = $items_totalprice;
                    $items_model[$k]->save();
                }
                if (!empty($pay_model)) {
                    $pay_model->pay_price = $pay_price;
                    $pay_model->save();
                }
                //总价
                //            number_format($purchase_price,3);
                //采购总数
                //            number_format($confirm,3);
                //收货总数
                //            $receipt;
            }
            $transaction->commit();
            return true;
        }catch (Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 根据订单号获取订单详情
     * @param  [type]  $purNumber          [description]
     * @param  array   $skuNum             [description]
     * @param  [type]  $requisition_number [description]
     * @param  boolean $is_view            [前端显示或导出：只显示或导出未作废的sku]
     * @return [type]                      [description]
     */
    public function getOrderDetail($purNumber, $skuNum = [],$requisition_number = null, $is_view=false)
    {
        $skus = []; //当前申请请款的sku
        if (!empty($requisition_number)) {
            $skus = \app\models\OrderPayDemandMap::getCurrentPaySkus(['requisition_number'=>$requisition_number]); //获取当前请款的sku
        }

        $model = self::find()->where(['pur_number' => $purNumber])->one();
        if(!$model) {
            return null;
        }

        $orderInfo = $model->attributes;

        if(!empty($model->is_drawback) && $model->is_drawback == 2) {
            $is_drawback = true;
        } else {
            $is_drawback = false;
        }

        $freight = 0;
        $discount = 0;
        $type = $model->purchaseOrderPayType;

        if($type) {
            //FBA、国内因为是勾选运费、优惠，所以先判断之前是否有勾选运费、优惠，然后计算可勾选运费、优惠
            if(preg_match('/^ABD/',$model->pur_number)){
                $freight  = $type->freight ? $type->freight : 0;
                $discount = $type->discount ? $type->discount : 0;
            }else{
                $alreadyPaid = PurchaseOrderPay::$alreadyPaid;
                //运费
                $detail_freight = PurchaseOrderPayDetail::find()
                    ->alias('opd')
                    ->select('sum(freight)')
                    ->joinWith('purchaseOrderPay')
                    ->where(['opd.pur_number'=>$model->pur_number])
                    ->andWhere(['in', 'pay_status', $alreadyPaid])
                    ->scalar();
                //优惠额
                $detail_discount = PurchaseOrderPayDetail::find()
                    ->alias('opd')
                    ->select('sum(discount)')
                    ->joinWith('purchaseOrderPay')
                    ->where(['opd.pur_number'=>$model->pur_number])
                    ->andWhere(['in', 'pay_status', $alreadyPaid])
                    ->scalar();
                $checked_freight = !empty($detail_freight)?$detail_freight:0;
                $checked_discount = !empty($detail_discount)?$detail_discount:0;
                $freight  = $type->freight ? ($type->freight - $checked_freight < 0) ? 0 : $type->freight - $checked_freight : 0;
                $discount = $type->discount ? ($type->discount - $checked_discount< 0) ? 0 : $type->discount - $checked_discount : 0;
            }
        }

        $orderInfo['order_freight']    = $freight;   // 订单运费
        $orderInfo['order_discount']   = $discount;  // 订单优惠额

        if ($is_view) {
            $items = $model->purchaseOrderItemsCtq;
            if (empty($items)) return false;
        } else {
            $items = $model->purchaseOrderItems;
        }
        $sku_count_money = 0;
        $orderItems = [];
        $type = Vhelper::getNumber($purNumber);

        if($items) {
            foreach($items as $k => $v) {
                //过滤掉未请款的sku
                if (!empty($skus) && !in_array($v['sku'],$skus)) continue;

                $num = $v['ctq'] ? $v['ctq'] : 0;
                $price = $v['price'] ? $v['price'] : 0;
                // 含税的单，计算税后单价，
                //FBA的退税仓的产品单价为含税单价不需要重新计算含税单价
                if($is_drawback&&$model->warehouse_code=='TS'&&$model->purchase_type==3){
                    $price=$v['price'];
                }elseif($is_drawback&&$model->purchase_type!=3) {
                    $point = PurchaseOrderTaxes::getABDTaxes($v['sku'], $v['pur_number']);
                    $price = round($v['price']*$point/100 + $v['price'], 3);
                }

                $sku_count_money += $num * $price;

                $warehouse_result = WarehouseResults::getResults($v['pur_number'], $v['sku'], ['nogoods', 'check_qty', 'instock_qty_count', 'arrival_quantity', 'instock_date']);

                $arr = [
                    'nogoods' => 0,
                    'check_qty' => 0,
                    'instock_qty_count' => 0,
                    'arrival_quantity' => 0,
                    'instock_date' => ''
                ];

                if($warehouse_result) {
                    $arr = [
                        'nogoods'           => $warehouse_result->nogoods,            // 不良品数量
                        'check_qty'         => $warehouse_result->check_qty,          // 检查数量
                        'instock_qty_count' => $warehouse_result->instock_qty_count,  // 上架数量
                        'arrival_quantity'  => $warehouse_result->arrival_quantity,   // 到货数量
                        'instock_date'      => $warehouse_result->instock_date        // 入库时间
                    ];
                }

                $type = Vhelper::getNumber($purNumber);

                $arr['yizhifu_num']    = isset($skuNum[$v['sku']]) ? $skuNum[$v['sku']] : 0; // 已支付数量

                if ($type == 1) {
                    //国内仓
                    $arr['quxiao_num'] = PurchaseOrderRefundQuantity::getCancelCtq($purNumber, $v['sku']);
                } else {
                    //海外仓和FBA
                    $arr['quxiao_num']     = PurchaseOrderCancelSub::getCancelCtq($purNumber, $v['sku']); // 取消数量
                }

                $arr['shouhuo_num']    = $arr['arrival_quantity']; // 收货数量

                $arr['weidaohuo_num']  = $v['ctq'] - $arr['shouhuo_num']; // 未到货数量

                $ruku_num = 0;

                if($type == 2) {
                    if(!empty($warehouse_result->arrival_quantity)) {
                        // if($warehouse_result->arrival_quantity == $v['ctq']) { // 海外仓，上架数量必须等于采购数量，才算入库
                            $ruku_num = $warehouse_result->arrival_quantity;
                        // }
                    }
                } else {
                    $ruku_num = $arr['instock_qty_count']; // 国内，FBA 上架就是入库
                }

                $arr['ruku_num'] = $ruku_num;

                $arr['freight']    = $freight;   // 订单运费
                $arr['discount']   = $discount;  // 订单优惠额
                $orderItems[] = array_merge($v->attributes, $arr);
            }
        }

        $orderInfo['sku_count_money']  = $sku_count_money; // 订单产品总额
        $orderInfo['order_real_money'] = $sku_count_money + $freight - $discount; // 订单实际价格
        $orderInfo['purchaseOrderItems'] = $orderItems;
        return $orderInfo;

    }

    // 根据订单号获取订单详情（最新版本，这个适合财务模块的合同单）
    public static function getOrderSkuInfo($purNumber,$requisition_number=null)
    {
        $skus = [];
        if (!empty($requisition_number)) {
            $skus = \app\models\OrderPayDemandMap::getCurrentPaySkus(['requisition_number'=>$requisition_number]); //获取当前请款的sku
        }

        $model = self::find()->select(['id', 'pur_number'])->where(['pur_number' => $purNumber])->one();

        if(!$model) {
            return null;
        }
        $items = $model->purchaseOrderItemsCtq;
        $orderItems = [];
        if($items) {
            foreach($items as $item) {
                if (!empty($skus) && !in_array($item['sku'],$skus)) continue;
                $warehouse_result = WarehouseResults::getResults($item['pur_number'], $item['sku'], ['nogoods', 'check_qty', 'instock_qty_count', 'arrival_quantity', 'instock_date']);
                $arr = [
                    'nogoods' => 0,
                    'check_qty' => 0,
                    'instock_qty_count' => 0,
                    'arrival_quantity' => 0,
                    'instock_date' => ''
                ];
                if($warehouse_result) {
                    $arr = [
                        'nogoods'           => $warehouse_result->nogoods,            // 不良品数量
                        'check_qty'         => $warehouse_result->check_qty,          // 检查数量
                        'instock_qty_count' => $warehouse_result->instock_qty_count,  // 上架数量  instock_qty_count
                        'arrival_quantity'  => $warehouse_result->arrival_quantity,   // 到货数量
                        'instock_date'      => $warehouse_result->instock_date        // 入库时间
                    ];
                }
                $arr['quxiao_num']     = PurchaseOrderCancelSub::getCancelCtq($purNumber, $item['sku']);// 取消数量
                $arr['shouhuo_num']    = $item['cty'] ? $item['cty'] : $arr['arrival_quantity'];   // 收货数量
                $arr['weidaohuo_num']  = $item['ctq'] - $arr['shouhuo_num'];                       // 未到货数量
                //如果是海外仓的，取到货数量
                $type = Vhelper::getNumber($purNumber);
                $arr['ruku_num']       = ($type==2)?( ($arr['instock_qty_count'] >= $arr['arrival_quantity']) ? $arr['instock_qty_count']:$arr['arrival_quantity']):$arr['instock_qty_count'];// 入库数量(收货数量数据异常：当入库>=收货时)
                $orderItems[] = array_merge($item->attributes, $arr);
            }
        }
        $data = \yii\helpers\ArrayHelper::toArray($model);
        $data['purchaseOrderItems'] = $orderItems;
        return $data;
    }

    // 获取采购单总额
    public function getOrderMoney($pur_number)
    {
        $order = self::findOne(['pur_number' => $pur_number]);
        if(!$order)
            return null;
        $items = $order->purchaseOrderItems;
        if(!$items)
            return null;
        $money = 0;
        foreach($items as $item) {
            $num = ($item->ctq) ? ($item->ctq) : ($item->qty);
            $money += ($item->price)*$num;
        }
        return $money;
    }

    /**
     * 获取采购单中的信息
     * @param $pur_number
     * @param null $field 获取字段的值
     * @param null $purchas_status
     * @return array|bool|mixed|null|\yii\db\ActiveRecord
     */
    public static function gerOrderOneInfo($pur_numbers,$purchas_status=null)
    {
        if (empty($pur_numbers)) {
            return false;
        }
        foreach ($pur_numbers as $v) {
            $pur_number = $v['pur_number'];
            $query =  self::find();
            if (empty($pur_number)) {
                return false;
            }
            $query = $query->where(['pur_number'=>$pur_number]);
            //订单状态
            if (!empty($purchas_status)) {
                $res = $query->andWhere(['in','purchas_status',$purchas_status])->one();
                if (!empty($res)) {
                    return $res;
                }
            }
        }
        return false;
    }

    //合并采购单
    public static function mergePurchase($pur_number)
    {
        $purchaseOrders = PurchaseOrder::find()
            ->select('t.*,GROUP_CONCAT(t.pur_number) as merge_number')
            ->alias('t')
            ->andFilterWhere(['t.pur_number'=>$pur_number])
            ->groupBy('t.warehouse_code,t.create_type,t.purchas_status,t.is_transit,t.transit_warehouse,t.purchase_type')->asArray()->all();
        if(count($purchaseOrders)!==1){
            throw new HttpException(500,'采购单的创建类型,状态,采购仓库,中转类型,中转仓库,采购类型一致才能合并');
        }
        if(!isset($purchaseOrders[0])){
            throw new HttpException(500,'采购单数据异常');
        }
        if($purchaseOrders[0]['purchas_status'] !=1){
            throw new HttpException(500,'只有待确定的采购单才能合并！');
        }
        $model        = new self();
        //采购订单主表
        $model->pur_number            = self::getPurnumber($purchaseOrders[0]['purchase_type']);
        $model->warehouse_code    = $purchaseOrders[0]['warehouse_code'];
        $model->is_transit        = $purchaseOrders[0]['is_transit'];
        $model->transit_warehouse = $purchaseOrders[0]['transit_warehouse'];
        $model->pur_type          = $purchaseOrders[0]['pur_type'];
        $model->supplier_code     = $purchaseOrders[0]['supplier_code'];
        $model->supplier_name     = $purchaseOrders[0]['supplier_name'];
        $model->buyer             = $purchaseOrders[0]['buyer'];
        $model->currency_code     = $purchaseOrders[0]['currency_code'];
        $model->shipping_method   = isset($purchaseOrders[0]['shipping_method']) ? $purchaseOrders[0]['shipping_method'] : 2;
        $model->operation_type    = isset($purchaseOrders[0]['operation_type']) ? $purchaseOrders[0]['operation_type'] : 2;
        $model->merchandiser      = isset($purchaseOrders[0]['merchandiser'])?$purchaseOrders[0]['merchandiser']:Yii::$app->user->identity->username;
        $model->reference         = isset($purchaseOrders[0]['reference']) ? $purchaseOrders[0]['reference'] :date('YmdHis');
        $model->creator           = Yii::$app->user->identity->username;
        $model->create_type       = 2;
        $model->is_expedited      = $purchaseOrders[0]['is_expedited'];
        $model->purchase_type     = $purchaseOrders[0]['purchase_type'];

        //添加采购单日志
        $s = [
            'pur_number'=>$model->pur_number,
            'note'      =>'合并采购单'.$purchaseOrders[0]['merge_number'],
        ];
        PurchaseLog::addLog($s);

        if($model->save(false))
        {
            //表修改日志-新增
            //$change_content = TablesChangeLog::insertCompare($model->attributes);
            $change_content = "insert:新增id值为{$model->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            return ['pur_number'=>$model->attributes['pur_number'],'merge_number'=>$purchaseOrders[0]['merge_number']];
        }
    }

    //合并订单产品表
    public static function mergeOrderItems($pur_number,$pur_numbers)
    {
        $purchaseOrderItems = PurchaseOrderItems::find()
            ->alias('t')
            ->leftJoin('pur_purchase_order as order','t.pur_number=order.pur_number')
            ->andFilterWhere(['order.pur_number'=>$pur_numbers])
            ->asArray()->all();
        if(empty($purchaseOrderItems)){
            throw new HttpException(500,'订单产品为空');
        }
        foreach ($purchaseOrderItems as $k => $v)
        {
            $promodel     = new Product();
            //采购订单详表
            $product                 = $promodel->findOne(['sku'=>$v['sku']]);
            $prodesc                 = ProductDescription::find()->where(['sku'=>$v['sku']])->orderBy('id desc')->one();
            if(empty($product) || empty($prodesc))
            {
                throw new HttpException(500,'sku不存在或产品名称不存在');
            }
            $sb= PurchaseOrderItems::findOne(['pur_number'=>$pur_number,'sku'=>$v['sku']]);
            if($sb)
            {
                PurchaseOrderItems::updateAllCounters(['qty'=>$v['qty']],['pur_number'=>$pur_number,'sku'=>$v['sku']]);

                //表修改日志-更新
                $change_data = [
                    'table_name' => 'pur_purchase_order_items', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => "update:id:{$sb['id']},qty:{$sb['qty']}=>{$v['qty']}", //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            } else{
                $itemsmodel              = new PurchaseOrderItems();
                $itemsmodel->pur_number  = $pur_number;
                $itemsmodel->qty         = isset($v['qty'])?$v['qty']:'10';
                $itemsmodel->sku         = !empty($v['sku']) ? $v['sku'] : $product->sku;
                $itemsmodel->name        = $prodesc->title;
                $itemsmodel->price       = !empty($v['price']) ? $v['price'] : '10';
                $itemsmodel->product_img = !empty($product->uploadimgs) ? $product->uploadimgs : '';
                $itemsmodel->save(false);

                //表修改日志-新增
                $change_content = "insert:新增id值为{$itemsmodel->id}的记录";
                $change_data = [
                    'table_name' => 'pur_purchase_order_items', //变动的表名称
                    'change_type' => '1', //变动类型(1insert，2update，3delete)
                    'change_content' => $change_content, //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }
        }
    }


    /*
     * 获取合同通用数据
     * $pos 订单号列表，以英文逗号隔开
     */
    public static function getCompactGeneralData($dd, $type = 2)
    {
        if(!is_array($dd)) {
            $dd = explode(',', $dd);
        }
        if($type == 2) {
            $models = PurchaseOrder::find()->where(['in', 'pur_number', $dd])->all();
        } else {
            $models = PurchaseOrder::find()->where(['in', 'id', $dd])->all();
        }
        $codes = [];
        foreach($models as $mod) {
            $compactModel = PurchaseCompactItems::find()->where(['pur_number'=>$mod->pur_number,'bind'=>1])->one();
            if(!empty($compactModel)){
                return [
                    'error' =>1,
                    'message'=>'数据异常,采购单号：'.$mod->pur_number.'已经生成了合同单：'.$compactModel->compact_number,
                ];
            }
            if(in_array($mod->purchas_status, [10])) { // 采购单在 待确认，取消，作废状态中
                return [
                    'error' => 1,
                    'message' => '你选择的采购单中，存在作废的单。'
                ];
            }
            if($mod->shipfees_audit_status == 0) { // 采购单修改了运费优惠，但是还没有审核
                return [
                    'error' => 1,
                    'message' => '你有采购单修改了运费优惠信息，还没有通过审核。'
                ];
            }
            $codes[] = $mod->supplier_code;
        }
        $code = array_unique($codes);
        if(count($code) > 1) {
            return [
                'error' => 1,
                'message' => '创建合同时，只允许同一家供应商的订单。'
            ];
        }
        $model = $models[0];
        $data = $model->attributes;
        $data['telephone'] = User::find()->select('telephone')->where(['username'=>$data['buyer']])->scalar(); //采购员电话号码
        $data['email'] = User::find()->select('email')->where(['username'=>$data['buyer']])->scalar(); //采购员电话号码
        $data['is_drawback'] = $model->is_drawback;
        $data['settlement_ratio'] = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->settlement_ratio : [];
        $data['supplier'] = !empty($model->supplier) ? $model->supplier : [];
        $data['supplierContent'] = !empty($model->supplierContent) ? $model->supplierContent : [];
        $supplierAccount = SupplierPaymentAccount::find()->where(['supplier_code' => $model->supplier_code,'status'=>1])->orderBy('pay_id desc')->asArray()->one();
        $data['supplierAccount'] = !empty($supplierAccount) ? $supplierAccount : [];
        foreach($models as $mod) {
            $products = $mod->purchaseOrderItems;
            if(empty($products)) {
                return [
                    'error' => 1,
                    'message' => '有个订单没有sku数据'
                ];
            }
            $pur_numbers[] = $mod->pur_number;
            $data['purchaseOrderItems'][$mod->pur_number] = $products;
        }
        $data['pos'] = $pur_numbers;
        // 获取订单总运费和总优惠额
        $data['freight_discount'] = PurchaseOrderPayType::find()
            ->select(['freight'=>'sum(ifnull(freight,0))','discount'=>'sum(ifnull(discount,0))'])
            ->where(['in', 'pur_number', $pur_numbers])
            ->asArray()
            ->one();

        return [
            'error' => 0,
            'message' => 'SUCCESS',
            'data' => $data
        ];
    }
    /**
     * FBA合同规则
     * 生成合同判断：相同供应商、是否退税、支付方式、结算方式、运输方式、结算比例
     */
    public static function ContractRules($ids)
    {
        if (is_array($ids)) $ids = implode(',', $ids);

        $sql = 'SELECT o.pur_number,supplier_code,supplier_name,is_drawback,pay_type,account_type,shipping_method,settlement_ratio,purchas_status 
                FROM pur_purchase_order o LEFT JOIN pur_purchase_order_pay_type opt ON opt.pur_number=o.pur_number 
                WHERE o.id in (' . $ids . ')';
        $models = Yii::$app->db->createCommand($sql)->queryAll();

        $res = Vhelper::changeData($models);
        $pur_numbers = $res['pur_number'];

        $itemsInfo = PurchaseOrderItems::find()->where(['in', 'pur_number', $pur_numbers])->asArray()->all();
        $skus = array_column($itemsInfo, 'sku');
        $productInfo = Product::find()->where(['in', 'sku', $skus])->asArray()->all();

        foreach ($res as $k => $v) {
            $unique = array_unique($v);
            if (count($unique) == 1 && !empty($unique[0])) {
                if ($k=='purchas_status' && $unique[0]!=7)  return ['error'=>1, 'message'=>'只有状态为等待到货的单，才可以创建合同'];
                if ($k=='is_drawback' && $unique[0]==2) {
                    //退税
                    if (empty($productInfo)) continue;
                    foreach ($productInfo as $key => $value) {
                        $declare_cname = !empty($value['declare_cname'])?$value['declare_cname']:''; //开票品名
                        $declare_unit = !empty($value['declare_unit'])?$value['declare_unit']:''; //开票单位
                        if (empty($declare_cname) || empty($declare_unit)) {
                             return ['error'=>1, 'message'=>'FBA含税合同开票品名及开票单位必填，否则不能生成合同--' . $value['sku']];
                        }
                    }
                }
                continue;
            } else {
                return self::caseSwitch($k);
            }
        }
        return ['error'=>0, 'message' => $res['settlement_ratio'][0],'supplier_name'=>$res['supplier_name'][0]];
    }
    /**
     * 根据对比结果，返回相应数据
     */
    public static function caseSwitch($n)
    {
        switch ($n){
        case 'supplier_code':
          $res = ['error'=>1, 'message'=>'供应商不一致'];
          break;  
        case 'is_drawback':
          $res = ['error'=>1, 'message'=>'退税状态不一致'];
          break;
        case 'pay_type':
          $res = ['error'=>1, 'message'=>'支付方式不一致'];
          break;
        case 'account_type':
          $res = ['error'=>1, 'message'=>'结算方式不一致'];
          break;
        case 'shipping_method':
          $res = ['error'=>1, 'message'=>'运输方式不一致'];
          break;
        case 'purchas_status':
          $res = ['error'=>1, 'message'=>'采购单状态不一致'];
          break;
        default:
          $res = ['error'=>1, 'message'=>'结算比例不能为空或不一致'];
        }
        return $res;
    }
    
    public static function getOverseasCompactGeneralData($pur_numbers, $compact_number = '')
    {
        $result = [];
        $models = PurchaseOrder::find()->where(['in', 'pur_number', $pur_numbers])->all();
        $result['purchase'] = $model = $models[0];
        $result['purchase_pay'] = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType : [];
        $result['supplier'] = !empty($model->supplier) ? $model->supplier : [];
        $result['supplierContent'] = !empty($model->supplierContent) ? $model->supplierContent : [];
        $payAccountType = $model->is_drawback==1 ? 2 : 1;//银行卡账户类型，退税对公不退税对私
        $supplierAccount = SupplierPaymentAccount::find()->where(['supplier_code'=>$model->supplier_code,'account_type'=>$payAccountType,'status'=>1])->asArray()->one();
        $result['supplierAccount'] = !empty($supplierAccount) ? $supplierAccount : [];
        foreach($models as $mod) {
            $result['purchaseItems'][$mod->pur_number] = $mod->purchaseOrderItems;
        }
        $result['j_company'] = BaseServices::getBuyerCompany($model->is_drawback);
        if ($compact_number == '') {
            $result['compact_number'] = 'ABD-HTxxxxxx';
        }
        return $result;
    }

    // 获取海外仓-订单商品总金额（含开票点）
    public static function getOverseasOrderMoney($model)
    {
        if(empty($model)) {
            return 0;
        }
        $items = $model->purchaseOrderItems;
        if(empty($items)) {
            return 0;
        }
        $m = 0;
        foreach($items as $item) {
            $num = ($item->ctq) ? ($item->ctq) : 0;
            if($model->is_drawback == 2) {
                $rate = PurchaseOrderTaxes::getABDTaxes($item['sku'], $item['pur_number']);
                $price = ((float)$rate*$item->price)/100 + $item->price;
            } else {
                $price = $item->price;
            }
            $m += $num*$price;
        }
        return $m;
    }

    // 获取国内仓-订单商品总金额
    public static function getHomeOrderMoney($model)
    {
        if(empty($model)) {
            return 0;
        }
        $items = $model->purchaseOrderItems;
        if(empty($items)) {
            return 0;
        }
        $m = 0;
        foreach($items as $item) {
            $n = ($item->ctq) ? ($item->ctq) : 0;
            $p = $item->price;
            $m += $n*$p;
        }
        return $m;
    }

    /**
     * 获取订单总金额
     * 采购单金额-优惠+运费
     */
    public static function getOrderTotalPrice($pur_number)
    {
        //采购单金额
        $total_price = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number])->sum('ctq*price');

        //优惠和运费
        $price_info = PurchaseOrderPayType::getDiscountPrice($pur_number);

        $freight = !empty($price_info['freight']) ?$price_info['freight'] : 0;
        $discount = !empty($price_info['discount']) ?$price_info['discount'] : 0;

        return (!empty($total_price) ? $total_price : 0) - $discount + $freight;
    }

    /*
     * 根据FBA缺货数据生成需求及采购单
     */
    public static function createFbaErpOrder($orderIds){
        $datas = AmazonOutofstockOrder::getCreateOrderData($orderIds);
        if(empty($datas['purchaseItems'])){
            return ['status'=>'error','message'=>'无可生成采购的单产品数据'];
        }
        $skus = array_keys($datas['purchaseItems']);
        if(count($skus)!=count(array_unique($skus))){
            return ['status'=>'error','message'=>'产品数据不唯一'];
        }
        $product = ProductDescription::find()->select('sku,title')->where(['in','sku',$skus])->andWhere(['language_code'=>'Chinese'])->asArray()->all();
        $skuNameData = ArrayHelper::map($product,'sku','title');
        $priceDatas = ProductProvider::find()->select('t.sku,sq.supplierprice')->alias('t')
                    ->leftJoin(SupplierQuotes::tableName().' sq','t.quotes_id=sq.id')
                    ->where(['t.is_supplier'=>1])
                    ->andWhere(['in','sku',$skus])
                    ->asArray()->all();
        $skuPriceDatas =ArrayHelper::map($priceDatas,'sku','supplierprice');
        $skuImg = Product::find()->select('sku,uploadimgs,product_category_id')->where(['in','sku',$skus])->asArray()->all();
        $skuImgDatas = ArrayHelper::map($skuImg,'sku','uploadimgs');
        $skuCategoryDatas = ArrayHelper::map($skuImg,'sku','product_category_id');
        $tran = Yii::$app->db->beginTransaction();
        try{

            //生成采购单主表
            $orderdata['purdesc']['warehouse_code']    = 'SZ_AA';
            $orderdata['purdesc']['is_transit']        = 2;
            $orderdata['purdesc']['transit_warehouse'] = '';
            $orderdata['purdesc']['supplier_code']     = $datas['supplier_code'];
            $orderdata['purdesc']['is_expedited']      = 1;
            $orderdata['purdesc']['purchase_type']     = 3;
            $pur_number                                = self::Savepurdata($orderdata);
            if(empty($pur_number)){
                throw new HttpException(500,'采购单生成失败');
            }
            $itemsDatas = [];
            $i=0;
            foreach ($datas['purchaseItems'] as $sku=>$value){
                if(!isset($skuNameData[$sku])){
                    throw new HttpException('sku产品名称不存在:'.$sku);
                }
                if(!isset($skuPriceDatas[$sku])){
                    throw new HttpException('sku产品单价不存在:'.$sku);
                }
                $itemsDatas[$i][] =$sku;
                $itemsDatas[$i][] =$pur_number;
                $itemsDatas[$i][] =$value['suggest_num'];
                $itemsDatas[$i][] =$skuNameData[$sku];
                $itemsDatas[$i][] =empty($skuPriceDatas[$sku]) ? 0 : $skuPriceDatas[$sku];
                $itemsDatas[$i][] =isset($skuImgDatas[$sku]) ? $skuImgDatas[$sku] :'';

                $plaformModel = new PlatformSummary();
                $plaformModel->sku = $sku;
                $plaformModel->platform_number = 'AMAZON';
                $plaformModel->product_name = $skuNameData[$sku];
                $plaformModel->purchase_quantity = $value['suggest_num'];
                $plaformModel->purchase_warehouse = 'SZ_AA';
                $plaformModel->transit_warehouse = '';
                $plaformModel->is_transit = 1;
                $plaformModel->create_id = Yii::$app->user->identity->username;
                $plaformModel->product_category = isset($skuCategoryDatas[$sku]) ? $skuCategoryDatas[$sku] : 0;
                $plaformModel->create_time = date('Y-m-d H:i:s',time());
                $plaformModel->level_audit_status = 1;
                $plaformModel->demand_number = CommonServices::getNumber('RD');
                $plaformModel->agree_user = Yii::$app->user->identity->username;
                $plaformModel->agree_time = date('Y-m-d H:i:s',time());
                $plaformModel->is_purchase = 2;
                $plaformModel->purchase_type = 3;
                $plaformModel->transit_number =0;
                $plaformModel->sales_note ='erp亚马逊平台订单缺货需求';
                $plaformModel->group_id ='ERP_AMAZON';
                $plaformModel->sales ='ERP_AMAZON';
                $plaformModel->source =2;
                if($plaformModel->save()==false){
                    throw new HttpException(500,'需求添加失败');
                }

                $demandModel = new PurchaseDemand();
                $demandModel->demand_number = $plaformModel->attributes['demand_number'];
                $demandModel->pur_number    = $pur_number;
                $demandModel->create_id     = Yii::$app->user->identity->username;
                $demandModel->create_time   = date('Y-m-d H:i:s',time());
                if($demandModel->save()==false){
                    throw new HttpException(500,'绑定关系添加失败');
                }
                AmazonOutofstockOrder::updateAll(['status'=>1,'demand_number'=>$plaformModel->attributes['demand_number']],
                    'status=0 and sku=:sku and pay_time > :payTime  and (create_time>:time or update_time>:time)',
                    [':sku'=>$sku,':time'=>date('Y-m-d 00:00:00'),':payTime'=>'2018-06-30 23:59:59']);
                AmazonOutofstockOrder::updateAll(['is_show'=>0],
                    'sku=:sku and pay_time > :payTime  and (create_time>:time or update_time>:time)',
                    [':sku'=>$sku,':time'=>date('Y-m-d 00:00:00'),':payTime'=>'2018-06-30 23:59:59']);
                $i++;

            }
            if(empty($itemsDatas)){
                throw new HttpException(500,'产品详情数据为空');
            }
            Yii::$app->db->createCommand()->batchInsert(PurchaseOrderItems::tableName(),
                ['sku','pur_number','qty','name','price','product_img'],$itemsDatas)->execute();
            $tran->commit();
            $result = ['status'=>'success','message'=>'采购计划单生成成功'];
        }catch(HttpException $e){
            $tran->rollBack();
            $result = ['status'=>'error','message'=>$e->getMessage()];
        }
        return $result;
    }
    /**
     * 修改采购到货状态
     */
    public static function updateArrivalStatus($pur_number)
    {
        $order_model = PurchaseOrder::findOne(['pur_number'=>$pur_number]);

            $time_model = PurchaseEstimatedTime::find()
                    ->where(['pur_number'=>$pur_number])
                    ->andWhere(['not', ['estimated_time' => null]])
                    ->count();
            $items_model = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number])->count();

           $time_status = bccomp($time_model, $items_model);

           if ($time_model == 0) {
               //未到货
               $order_model->arrival_status = 1;
           } elseif ($time_status == -1) {
               //部分到货
               $order_model->arrival_status = 2;
           } else {
               //全到货
               $order_model->arrival_status = 3;
           }
           $order_model->save();
    }

    public static  function  getShippingMethod($type=null)
    {
        $types = [
            '1' =>'自提',
            '2' =>'快递',
            '3' =>'物流',
            '4' =>'送货',
        ];
        return isset($type) ?  $types[$type]:'';
    }

    //判断供应商是否属于广东省内
    public static  function  checkProvince($pur_number)
    {
        $province = self::find()
            ->select('pur_provincial.id')
            ->leftJoin('pur_supplier','pur_supplier.supplier_code=pur_purchase_order.supplier_code')
            ->leftJoin('pur_provincial','pur_provincial.id=pur_supplier.province')
            ->where(['pur_purchase_order.pur_number'=>$pur_number])
            ->one();
        $is_gd = 0;
        if($province && $province->id == 20){//广东省内标记为1
            $is_gd = 1;
        }
        return $is_gd;
    }
    /**
     * 获取仓库入库列表明细
     */

    public static function getList($params, $is_export=false)
    {
        //默认：1是入库明细：8部分到货等待剩余，9部分到货不等待剩余，6全到货
        //2是付款明细：只统计7等待到货和8部分到货等待剩余
        $is_warehouse = isset($params['is_warehouse'])?(int)$params['is_warehouse'] : 1;
        $purchase_type = !empty($params['PurchaseOrder']['purchase_type'])?$params['PurchaseOrder']['purchase_type']:null;
        $time = isset($params['WarehouseResults']['time'])?$params['WarehouseResults']['time']:null;
        $recentMonth = date("Y-m-d H:i:s",strtotime("-10 day"));

        if ($is_warehouse == 1) {
            // 1是入库明细：8部分到货等待剩余，9部分到货不等待剩余
            $sql = "
            SELECT
                IFNULL(ci.compact_number,null) as compact_number,
                IFNULL(ci.bind,null) as bind,
              a.pur_number as pur_number,
              case a.purchase_type
                when 1 then '国内'
                when 2 then '海外'
                when 3 then 'FBA'
              end as purchase_type,
              a.supplier_name as supplier_name,
              a.supplier_code as supplier_code, 
              c.instock_date as instock_date, 
              c.receipt_number as receipt_number, 
              a.warehouse_code as warehouse_code,
              b.sku as sku,
              b.`name` as name,
              c.purchase_quantity as purchase_quantity,
              c.instock_qty_count as instock_qty_count,
              c.instock_qty_count as instock_qty_count,
              c.nogoods as nogoods,
              b.price as price,
              a.is_drawback as is_drawback,
              e.supplier_settlement_name as supplier_settlement_name,
              CASE
                a.pay_type 
                WHEN 1 THEN
                '现金' 
                WHEN 2 THEN
                '支付宝' 
                WHEN 3 THEN
                '银行卡转账' 
                WHEN 4 THEN
                'Paypal' 
                WHEN 5 THEN
                '富友' 
                END AS pay_type,
              GROUP_CONCAT(d.note separator ';') as note
            FROM
              pur_purchase_order AS a
            LEFT JOIN pur_purchase_order_items AS b ON (a.pur_number = b.pur_number)
            LEFT JOIN pur_warehouse_results AS c ON (
              b.pur_number = c.pur_number
              AND b.sku = c.sku
            )
            left join pur_purchase_note as d on (a.pur_number = d.pur_number)
            left join pur_supplier_settlement as e on (a.account_type = e.supplier_settlement_code)
            left JOIN pur_purchase_compact_items as ci on (a.pur_number=ci.pur_number and ci.bind=1)
            where a.purchas_status in (8, 9) and c.instock_qty_count > 0
            ";
            if (!empty($purchase_type)) {
                $sql .= ' AND a.purchase_type=' . $purchase_type;
            }
            if (!empty($time)) {
                $start_time = $params['WarehouseResults']['start_time'];
                $end_time = $params['WarehouseResults']['end_time'];
                $sql .= " AND (instock_date BETWEEN '{$start_time}' AND '{$end_time}')";
            } else {
                $start_time = $recentMonth;
                $end_time = date('Y-m-d H:i:s', time());
                $sql .= " AND (instock_date BETWEEN '{$start_time}' AND '{$end_time}')";
            }
            $sql .= ' group by b.pur_number, b.`name`';
        } else {
            // 2是付款明细：只统计7等待到货和8部分到货等待剩余
            $sql = "SELECT
              b.supplier_name as supplier_name,
              a.sku as sku,
              a.`name` as name,
              a.pur_number as pur_number, 
              f.compact_number as compact_number,
              case b.purchase_type
                when 1 then '国内'
                when 2 then '海外'
                when 3 then 'FBA'
              end as purchase_type, 
              b.buyer as buyer,
              a.ctq as ctq, 
              a.price as price, 
              a.price * a.ctq as total_price,
              c.arrival_quantity as arrival_quantity, 
              b.created_at as created_at,
              e.payer_time as payer_time,
              case b.purchas_status
                when 1 THEN '是待确认'
                when 2 THEN '采购确认'
                when 3 THEN '已审批'
                when 4 THEN '取销'
                when 5 THEN '部分到货'
                when 6 THEN '全到货'
                when 7 THEN '未到货'
                when 8 THEN '部分到货等待剩余'
                when 9 THEN '部分到货不等待剩余'
                when 10 THEN '作废)'
              end
              as purchas_status,
              d.note as note
            FROM
              pur_purchase_order_items AS a
            LEFT JOIN pur_purchase_order AS b ON (a.pur_number = b.pur_number)
            LEFT JOIN pur_warehouse_results AS c ON (a.pur_number = c.pur_number and a.sku = c.sku)
            left join pur_purchase_note as d on (a.pur_number = d.pur_number)
            left join pur_purchase_order_pay as e on (a.pur_number = e.pur_number)
            left join pur_purchase_compact_items as f on (a.pur_number = f.pur_number)
            where b.purchas_status in (7, 8) and b.pay_status in (5)";
            if (!empty($purchase_type)) {
                $sql .= ' AND b.purchase_type=' . $purchase_type;
            }
            if (!empty($time)) {
                $start_time = $params['WarehouseResults']['start_time'];
                $end_time = $params['WarehouseResults']['end_time'];
                $sql .= " AND (payer_time BETWEEN '{$start_time}' AND '{$end_time}')";
            } else {
                $start_time = $recentMonth;
                $end_time = date('Y-m-d H:i:s', time());
                $sql .= " AND (payer_time BETWEEN '{$start_time}' AND '{$end_time}')";
            }
            $sql .= ' GROUP BY a.pur_number, a.sku';
        }

        $model = new self();
        
        $q = Yii::$app->db->createCommand($sql)->queryAll();

        //缓存数据
        $cache = Yii::$app->cache;
        $cache->delete('warehouse-details');
        $cache->add('warehouse-details', $q);

        $pages = new Pagination(['totalCount'=>count($q),]);
        $list = Yii::$app->db->createCommand($sql." limit ".$pages->limit." offset ".$pages->offset."")->queryAll();  
        $dataprovider = new ArrayDataProvider(['allModels' => $list,]);  
        return [
            'is_warehouse' => $is_warehouse,
            'params' => $params,
            'model' => $model,
            'data' => $list,
            'pagination' => $pages
        ];
    }
    /**
     * 获取订单主表信息
     */
    public static function getDetails($pur_number,$cancel_id=null)
    {
        $res = [];
        $model = self::find()->where(['pur_number'=>$pur_number])->one();
        if ( ($model->source == 1) && !empty($model->purchaseCompactItems->compact_number) ) {
            // 1合同 2网络【默认】 3账期采购
            $res['compact_number'] = !empty($model->purchaseCompactItems->compact_number)?$model->purchaseCompactItems->compact_number:null;
        }
        $res['pur_number'] = $pur_number; //订单号
        $res['supplier_name'] = $model['supplier_name']; //供应商
        $res['purchas_status'] = $model['purchas_status']; //采购单状态

        $res['order_price'] = PurchaseOrder::getOrderTotalPrice($pur_number); //订单总额
        $res['pay_price'] = PurchaseOrderPay::getOrderPaidMoney($pur_number); //已付款的
        $res['cancel_price'] = PurchaseOrderCancelSub::getCancelPriceOrder($pur_number); //已取消总金额
        if (!empty($cancel_id)) {
            $res['buyer_note'] = PurchaseOrderCancel::getBuyerNote($cancel_id); //采购员备注
        }
        return $res;
    }
    /**
     * FBA-判断是否验货
     * 广东省内 且 订单总额大于：5000 五千
     */
    public static function isCheckGoods($model)
    {
        //广东省内的编号是：20
        // $province = $model->supplier->province;
        $price = PurchaseOrderItems::getCountPrice($model->pur_number);
        // if ((int)$province===20 && $price>=20000) {
        if ($price>=5000) {
            return 1; //是验货
        } else {
            return 2; //否验货
        }
    }
    /**
     * 判断是否全部取消：针对整个采购单
     * 报损、取消数量、入库数量
     * 返回：采购单状态和是否全部取消
     * $data 指定当前操作的【报损、取消数量】的数据
     * $type 取消类型（1报损，2取消数量，3入库数量）
     */
    public static function isAllCancel($pur_number, $data, $type=null)
    {
        //-1未全部取消，1全部取消
        $is_all_cancel = -1;

        //报损数量：审核通过的
        $breakage_num = PurchaseOrderBreakage::find()->where(['pur_number'=>$pur_number,'status'=>3])->sum('breakage_num');
        if ($type==1) {
            //加上当前处理的
            $breakage_num += PurchaseOrderBreakage::find()->where(['id'=>$data])->sum('breakage_num');
        }
        //取消数量
        $cancel_ctq = PurchaseOrderCancelSub::getCancelCtqOrder($pur_number);
        if ($type == 2) {
            //加上当前处理的:$data=cancel_id
            $cancel_ctq += PurchaseOrderCancelSub::getCancelDetail($data)['cancel_ctq_total']; //当前取消的数量
        }
        //入库数量
        $ruku_num = WarehouseResults::getOrderInstockInfo($pur_number)['instock_qty_count'];

        //采购数量
        $ctq = PurchaseOrderItems::find()->where(['pur_number'=>$pur_number])->sum('ctq');

        //获取采购单状态
        $purchase_status = PurchaseOrder::find()->select('purchas_status')->where(['pur_number'=>$pur_number])->scalar();

        //比较：采购数量  报损数量+取消数量+入库数量
        // [>1][<-1][=0]
        $bcc = bccomp($ctq,$breakage_num+$cancel_ctq+$ruku_num);
        if ($bcc<1) {
            if ($purchase_status==8) {
                #部分到货等待剩余
                $purchase_status = 9;
            } else {
                #其他作废
                $purchase_status = 10;
            }
            $is_all_cancel = 1;
        }
        return ['purchase_status'=>$purchase_status,'is_all_cancel'=>$is_all_cancel];
    }
}
