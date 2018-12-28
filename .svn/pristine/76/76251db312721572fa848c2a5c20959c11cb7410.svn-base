<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\services\CommonServices;
use yii\web\HttpException;

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
class PurchaseAmount extends BaseModel
{
    public $page_size;
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
            [['created_at', 'date_eta','supplier_name', 'e_date_eta','e_supplier_name','is_expedited','submit_time'], 'safe'],
            [['create_type', 'audit_return', 'purchas_status','is_drawback','review_status'], 'integer'],
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
            if ($v->purchas_status != $type)
            {
                $v->purchas_status=$type;
                $v->is_push=0;
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
            $model->account_type    = $v['account_type'];

            $model->pay_type        = $v['pay_type'];
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

            //$model->confirm_note    = isset($v['confirm_note'])?:'no';
            //添加采购单日志
            $s = [
                'pur_number'=>$v['pur_number'],
                'note'      =>'采购确认',
            ];
            PurchaseLog::addLog($s);
            $status                 = $model->save(false);

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
            $supplier                 = $suppmodel->findOne(['supplier_code' => $model->supplier_code]);
            $model->supplier_name     = $supplier['supplier_name'];
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

           //添加采购单日志
            $s = [
                'pur_number'=>$model->pur_number,
                'note'      =>'手动创建采购计划单',
            ];
            PurchaseLog::addLog($s);
            if($model->save(false))
            {
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
                }

            }
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

    public static function createFbaPurOrder($data){
        $tran = Yii::$app->db->beginTransaction();
        try{
            if(empty($data)){
                throw new HttpException(500,'没有符合要求的采购需求！');
            }
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
            }
            $warehouse = array_unique($wdata);
            $supplier  = array_unique($sdata);
            $istransit = array_unique($transit);
            $tranw     = array_unique($tranware);
            $type      = array_unique($pur_type);
            if(count($warehouse)>1 || count($supplier)>1 || count($istransit)>1 || count($tranw)>1){
                throw new HttpException(500,'所选采购需求有多个供应商或采购仓库或中转属性不能生成一个采购单！');
            }

            $orderdata['purdesc']['warehouse_code']    = $warehouse[0];
            $orderdata['purdesc']['is_transit']        = $istransit[0];
            $orderdata['purdesc']['transit_warehouse'] = $tranw[0];
            $orderdata['purdesc']['supplier_code']     = $supplier[0];
            $orderdata['purdesc']['is_expedited']      = 1;
            $orderdata['purdesc']['purchase_type']     = $type[0];
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
                    $itemsmodel->product_img = !empty($product->uploadimgs) ? $product->uploadimgs : '';
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
        return self::find()->where(['pur_number'=>$pur_number])->one()['buyer'];
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
                    $items_model[$k]->save();
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
     * 获取采购单中的信息
     * @param $pur_number
     * @param null $field 获取字段的值
     * @param null $purchas_status
     * @return array|bool|mixed|null|\yii\db\ActiveRecord
     */
    public static function gerOrderOneInfo($pur_number,$field=null,$purchas_status=null)
    {
        $query =  self::find();
        if (empty($pur_number)) {
            return false;
        }
        $query = $query->where(['pur_number'=>$pur_number]);
        //订单状态
        if (!empty($purchas_status)) {
            $query->andWhere(['in','purchas_status',$purchas_status]);
        }
        //输出哪些数据
        if (!empty($field)) {
            $data = $query->one()[$field];
        } else {
            $data = $query->one();
        }
        return $data;
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
            } else{
                $itemsmodel              = new PurchaseOrderItems();
                $itemsmodel->pur_number  = $pur_number;
                $itemsmodel->qty         = isset($v['qty'])?$v['qty']:'10';
                $itemsmodel->sku         = !empty($v['sku']) ? $v['sku'] : $product->sku;
                $itemsmodel->name        = $prodesc->title;
                $itemsmodel->price       = !empty($v['price']) ? $v['price'] : '10';
                $itemsmodel->product_img = !empty($product->uploadimgs) ? $product->uploadimgs : '';
                $itemsmodel->save(false);
            }
        }
    }

}
