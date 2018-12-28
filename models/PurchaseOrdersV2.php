<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use app\services\BaseServices;
use Yii;
use yii\behaviors\TimestampBehavior;
use app\services\CommonServices;

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
class PurchaseOrdersV2 extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%domestic_purchase_order}}';
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
            [['shipping_method', 'operation_type','review_remarks'], 'string'],
            [['created_at', 'date_eta','supplier_name', 'e_date_eta','e_supplier_name','is_expedited','submit_time','all_status','total_price'], 'safe'],
            [['create_type', 'audit_return', 'purchas_status','is_drawback','review_status'], 'integer'],
            [['pur_number', 'creator'], 'string', 'max' => 20],
            [['warehouse_code', 'supplier_code', 'reference'], 'string', 'max' => 30],
            [['currency_code'], 'string', 'max' => 10],
            [['pur_number'], 'unique'],
            //[['total_price'], 'number'],
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
            'all_status' => Yii::t('app', '订单操作状态'),
            'total_price' => Yii::t('app', '订单总额'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItemsV2::className(), ['pur_number' => 'pur_number']);
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
     * 修改订单状态
     * @param $ids
     * @param $type
     * @return bool|void
     */
    public  static  function  UpdatePurchaseStatus($ids,$type)
    {
        if (!$ids) return;
        $map['id']=strpos($ids,',') ? explode(',',$ids):$ids;
        $orders=self::find()
            ->select('id,purchas_status,pur_number')
            ->where($map)
            ->andWhere(['<','purchas_status',4])
            ->andWhere(['<','review_status',3])
            ->all();
        if(!empty($orders)){
            foreach ($orders as $v)
            {
                if ($v->purchas_status != $type)
                {
                    $v->purchas_status=$type;

                    if($type==4){
                        $v->all_status=1;
                    }else{
                        $v->all_status=$type;
                    }

                    $data['type']=5;
                    $data['pid']=$v->id;
                    $data['pur_number']=$v->pur_number;
                    $data['module']='采购管理';
                    $data['content']="撤销确认数据成功-单号($v->pur_number)：".OperatLog::subLogstr($v).'【成功】';

                    $result =$v->save(false);
                } else {

                    $data['type']=5;
                    $data['pid']=$v->id;
                    $data['pur_number']=$v->pur_number;
                    $data['module']='采购管理';
                    $data['content']="撤销确认数据失败-单号($v->pur_number)：".OperatLog::subLogstr($v).'【失败】';

                    $result= false;
                }
                Vhelper::setOperatLog($data);

            }
        }else{
            $result=false;
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

            $sname= BaseServices::getSupplierName($v['supplier_code']);

            if(!empty($sname) && $model->supplier_name != $sname){
                $model->e_supplier_name= $sname;
            }

            $model->date_eta        = !empty($v['date_eta'])?$v['date_eta']:date('Y-m-d H:i:s',time());
            $model->account_type    = $v['account_type'];

            $model->pay_type        = $v['pay_type'];
            $model->supplier_code        = $v['supplier_code'];
            $model->supplier_name        = $sname;

            $model->shipping_method = $v['shipping_method'];
            //$model->pay_ship_amount = $v['pay_ship_amount'];
            //$model->tracking_number = $v['tracking_number'];
            $model->purchas_status  = $v['submit']==2?2:1;
            $model->all_status  = $v['submit']==2?2:1;
            $model->audit_return    = 2;
            $model->buyer           = Yii::$app->user->identity->username;
            $model->submit_time     = date('Y-m-d H:i:s',time());
            $model->is_drawback     = !empty($v['is_drawback'])?$v['is_drawback']:'1';

            if(isset($v['is_transit']) && $v['is_transit']==1)
            {
                $model->transit_warehouse = $v['transit_warehouse'];
                $model->is_transit        = $v['is_transit'];
            }

            //添加采购单日志
            $s = [
                'pur_number'=>$v['pur_number'],
                'note'      =>'采购确认',
            ];
            PurchaseLog::addLog($s);
            $status = $model->save(false);

            $data['type']=$v['submit']==2 ? 4 : 2;
            $data['pid']=$model->id;
            $data['pur_number']=$model->pur_number;
            $data['module']='采购管理';
            $title=$v['submit']==2 ? '确认-' : '修改-';
            $data['content']=$title."采购单数据成功-单号({$v['pur_number']})：".OperatLog::subLogstr($model).'恭喜你【成功】！';
            Vhelper::setOperatLog($data);


        }
        return $status;
    }
    public  function  PurchaseOrderItems($data)
    {
        foreach ($data as $v)
        {
            $model = PurchaseOrderItemsV2::findOne(['pur_number'=>$v['pur_number'],'sku'=>$v['sku']]);

            if(!empty($v['price']) && $model->price != $v['price']){
                $model->e_price= $v['price'];
            }

            if(!empty($v['ctq']) && $model->ctq != $v['ctq']){
                $model->e_ctq= $v['ctq'];
            }

            $plink=\app\models\SupplierQuotes::getUrl($v['sku']);
            if(!empty($v['product_link']) && $plink != $v['product_link']){
                $model->product_link  = $v['product_link'];
            }

            $model->price               = $v['price'];
            $model->ctq                 = $v['ctq'];
            $model->items_totalprice    = $v['totalprice'];
            $status                     = $model->save(false);

            $cprice=PurchaseOrderItemsV2::find()->where(['pur_number'=>$v['pur_number']])->sum('items_totalprice');
            $qprice=PurchaseOrderItemsV2::find()->where(['pur_number'=>$v['pur_number']])->sum('qty*price');

            $cprice=!empty($cprice) ? $cprice : 0;
            $qprice=!empty($qprice) ? $qprice : 0;

            $_model = self::findOne(['pur_number'=>$v['pur_number']]);
            $_model->total_price  = !empty($cprice) ? $cprice : $qprice;

            $_model->save(false);

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
                $prodescmodel = new ProductDescription();
                //采购订单详表
                $product                 = $promodel->findOne(['sku'=>$v['sku']]);
                $prodesc                 = $prodescmodel->findOne(['sku'=>$v['sku']]);
                if(empty($product) && empty($prodesc))
                {
                    return false;
                }
                //$suppquotes = $quotesmodel->findOne(['product_sku' => $product->sku]);
                //$histroys                = $histroy->findOne(['sku'=>$v['sku']]);
                $sb= PurchaseOrderItemsV2::findOne(['pur_number'=>$pur_number,'sku'=>$v['sku']]);
                //Vhelper::dump($sb);
                if($sb)
                {
                    $sb->qty         += $v['purchase_quantity'];
                    $sb->save(false);
                } else{
                    $itemsmodel              = new PurchaseOrderItemsV2();
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

    /**
     * 复制数据到 PurchaseOrder、PurchaseOrderItems 表
     * @param $data
     * @return bool
     */
    public static function SaveForders($data){
        if(!empty($data)){
            $findone=PurchaseOrder::findOne(['pur_number'=>$data['pur_number']]);
            $orderitems=PurchaseOrderItemsV2::findAll(['pur_number'=>$data['pur_number']]);
            $model=$findone ? $findone : new PurchaseOrder();

            $model->pur_number=$data->pur_number;
            $model->warehouse_code=$data->warehouse_code;
            $model->supplier_code=$data->supplier_code;
            $model->supplier_name=$data->supplier_name;
            $model->pur_type=$data->pur_type;
            $model->shipping_method=$data->shipping_method;
            $model->operation_type=$data->operation_type;
            $model->created_at=$data->created_at;
            $model->creator=$data->creator;
            $model->currency_code=$data->currency_code;
            $model->date_eta=$data->date_eta;
            $model->arrival_note=$data->arrival_note;
            $model->buyer=$data->buyer;
            $model->merchandiser=$data->merchandiser;
            $model->reference=$data->reference;
            $model->create_type=$data->create_type;
            $model->audit_return=$data->audit_return;
            $model->purchas_status=$data->purchas_status;
            $model->is_transit=$data->is_transit;
            $model->transit_warehouse=$data->transit_warehouse;
            $model->pay_number=$data->pay_number;
            $model->audit_time=$data->audit_time;
            $model->auditor=$data->auditor;
            $model->pay_type=$data->pay_type;
            $model->account_type=$data->account_type;
            $model->is_arrival=$data->is_arrival;
            $model->audit_note=$data->audit_note;
            $model->confirm_note=$data->confirm_note;
            $model->is_push=$data->is_push;
            $model->refund_status=$data->refund_status;
            $model->pay_status=$data->pay_status;
            $model->complete_type=$data->complete_type;
            $model->receiving_exception_status=$data->receiving_exception_status;
            $model->qc_abnormal_status=$data->qc_abnormal_status;
            $model->is_expedited=$data->is_expedited;
            $model->submit_time=$data->submit_time;
            $model->purchase_type=$data->purchase_type;
            $model->is_drawback=$data->is_drawback;
            $model->e_date_eta=$data->e_date_eta;
            $model->e_account_type=$data->e_account_type;
            $model->e_supplier_name=$data->e_supplier_name;
            $model->review_status=$data->review_status;
            $model->review_remarks=$data->review_remarks;
            $model->all_status=$data->all_status;
            $model->total_price=$data->total_price;
            $model->save(false);

            foreach ($orderitems as $ik=>$iv){
                $findone_i=PurchaseOrderItems::findOne(['pur_number'=>$iv['pur_number'],'sku'=>$iv['sku']]);

                $model_items=$findone_i ? $findone_i : new PurchaseOrderItems();
                $model_items->pur_number=$iv->pur_number;
                $model_items->sku=$iv->sku;
                $model_items->name=$iv->name;
                $model_items->qty=$iv->qty;
                $model_items->price=$iv->price;
                $model_items->ctq=$iv->ctq;
                $model_items->rqy=$iv->rqy;
                $model_items->cty=$iv->cty;
                $model_items->sales_status=$iv->sales_status;
                $model_items->product_img=$iv->product_img;
                $model_items->order_id=$iv->order_id;
                $model_items->is_exemption=$iv->is_exemption;
                $model_items->items_totalprice=$iv->items_totalprice;
                $model_items->product_link=$iv->product_link;
                $model_items->e_ctq=$iv->e_ctq;
                $model_items->e_price=$iv->e_price;
                $model_items->save(false);
            }
        }else{
            $data['type']=7;
            $data['module']='采购管理';
            $data['content']='申请付款数据为空，复制到 PurchaseOrder、PurchaseOrderItems 表动作【失败】！';
            Vhelper::setOperatLog($data);
        }
        return true;
    }

}
