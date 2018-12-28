<?php

namespace app\models;

use app\api\v1\models\InlandAvgDelieryTime;
use app\config\Vhelper;
use Yii;
use yii\helpers\Html;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\services\SupplierServices;

/**
 * This is the model class for table "{{%purchase_order_items}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property string $name
 * @property integer $qty
 * @property string $price
 * @property integer $ctq
 * @property integer $rqy
 * @property integer $cty
 * @property integer $sales_status
 *
 * @property PurchaseOrder $purNumber
 */
class PurchaseOrderItems extends \yii\db\ActiveRecord
{
    public $purchase_num;
    public $purchase_type;
    public $supplier_name;
    public $is_drawback;
    public $account_type;
    public $product_category_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_items}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'sku', 'name', 'qty'], 'required'],
            [['qty', 'ctq','e_ctq', 'rqy', 'cty', 'sales_status'], 'integer'],
            [['price','e_price'], 'number'],
            [['purchase_link','e_ctq','e_price'], 'safe'],
            [['pur_number'], 'string', 'max' => 20],
            [['sku'], 'string', 'max' => 30],
            [['name'], 'string', 'max' => 300],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
            [['pur_number'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrder::className(), 'targetAttribute' => ['pur_number' => 'pur_number']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', '采购单号'),
            'product_link' => Yii::t('app', '产品链接'),
            'sku' => Yii::t('app', 'sku'),
            'name' => Yii::t('app', '商品名称'),
            'product_img' => Yii::t('app', '图片'),
            'qty' => Yii::t('app', 'Qty'),
            'price' => Yii::t('app', '订单价格'),
            'ctq' => Yii::t('app', '订单数量'),
            'e_price' => Yii::t('app', 'Price'),
            'e_ctq' => Yii::t('app', 'Ctq'),
            'rqy' => Yii::t('app', 'Rqy'),
            'cty' => Yii::t('app', 'Cty'),
            'sales_status' => Yii::t('app', 'Sales Status'),
            'purNumber.supplier_name' => Yii::t('app', '供货商名称'),
            'purchaseTicketOpen.open_time' => Yii::t('app', '开票日期'),
            'purchaseTicketOpen.ticket_name' => '开票品名',
            'purchaseTicketOpen.issuing_office' => '开票单位',
            'purchaseTicketOpen.tickets_number' => '开票数量',
            'purchaseTicketOpen.total_par' => '票面总金额',
            'purchaseTicketOpen.invoice_code' => '发票编码',
            'purchaseTicketOpen.note' => '备注',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumber()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    }
    public function getSuppliers()
    {
    	return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code'])->via('purNumber');
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPurNumbers()
    {
        return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number'])->where(['purchas_status'=>1]);
    }

    public function getWarnStatus(){
        return $this->hasMany(PurchaseWarningStatus::className(),['pur_number'=>'pur_number','sku'=>'sku']);
    }
    /**
     * @desc 通过获取采购单对应的供应商信息
     * @author Jimmy
     * @date 2017-04-06 14:25:11
     */
    public function getPurchaseSupplier(){
    	return $this->hasOne(PurchaseOrder::className(), ['pur_number' => 'pur_number']);
    
    }
    /**
     * @desc 通过获取sku对应销量
     * @author Jimmy
     * @date 2017-04-06 14:25:11
     */
    public function getSkuSale(){
    	return $this->hasOne(SkuSalesStatistics::className(), ['sku' => 'sku']);
    
    }
    /**
     * @desc 通过获取sku获取13天库存
     * @author Jimmy
     * @date 2017-04-06 14:25:11
     */
    public function getSkustock(){
    	return $this->hasOne(PurchaseSuggest::className(), ['sku' => 'sku']);
    }

    /**
     * @return \yii\db\ActiveQuery 关联国内仓权均到货日期
     */
    public function getInlandAvgDelivery(){
        return $this->hasOne(InlandAvgDelieryTime::className(),['sku'=>'sku']);
    }
    /**
     * 根据采购单号获取sku数
     * @param $purnumber
     */
    public static function  getSKU($purnumber)
    {
        return self::find()->where(['pur_number'=>$purnumber])->count();
    }


    public function getDesc(){
        return $this->hasOne(ProductDescription::className(),['sku'=>'sku']);
    }


    public function getProduct(){
        return $this->hasOne(Product::className(),['sku'=>'sku']);
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
     * 关联sku预计到货时间 一对一
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseEstimatedTime()
    {
        return $this->hasOne(PurchaseEstimatedTime::className(),['pur_number'=>'pur_number','sku'=>'sku']);
    }
    /**
     * 关联开票信息
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseTicketOpen()
    {
        return $this->hasOne(PurchaseTicketOpen::className(),['pur_number'=>'pur_number','sku'=>'sku']);
    }
    /**
     * 关联表
     * @return $this
     */
    public function getPurchaseOrderItemsStock(){
        return $this->hasOne(PurchaseOrderItemsStock::className(), ['pur_number'=>'pur_number', 'sku'=>'sku']);
    }
    /**
     * @desc 获取默认供应商
     * @return $this
     */
    public function getDefaultSupplier(){
        return $this->hasOne(ProductProvider::className(), ['sku' => 'sku'])->where(['is_supplier'=>1]);
    }
    /**
     * @desc 通过中间表获取默认供应商报价
     */
    public function getSupplierQuote(){
        return $this->hasOne(SupplierQuotes::className(), ['id'=>'quotes_id'])->where(['status'=>1])
                ->via('defaultSupplier');
    }
    /**
     * 关联订单支付类型表 一对一
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderPayType()
    {
        return $this->hasOne(PurchaseOrderPayType::className(),['pur_number'=>'pur_number']);
    }
    /**
     * 根据采购单号获取统计总金额
     * @param $purnumber
     * @return mixed
     */
    public static function getCountPrice($purnumber)
    {
        //单个审核
        $map['pur_purchase_order.pur_number'] = $purnumber;//税金税金税金
        $ordersitmes = PurchaseOrder::find()->joinWith(['purchaseOrderItems','supplier','orderNote'])->where($map)->one();
        $total_price = 0;
        if($ordersitmes->is_drawback == 2){
            if(isset($ordersitmes->purchaseOrderItems) && count($ordersitmes->purchaseOrderItems)>0){
                foreach ($ordersitmes->purchaseOrderItems as $item){
                    //FBA退税单价
                    if($ordersitmes->warehouse_code=='TS'&&$ordersitmes->purchase_type==3){
                        $total_price += bcmul($item['ctq'],$item['price'],2);
                    }else{
                        $taxes = PurchaseOrderTaxes::getABDTaxes($item['sku'],$item['pur_number']);
                        if($taxes > 0){
                            $tax = bcadd(bcdiv($taxes,100,2),1,2);
                            $total_price += round($tax*$item['price']*$item['ctq'],2);//数量*单价*(1+税点)
                        }else{
                            $total_price += bcmul($item['ctq'],$item['price'],2);
                        }
                    }
                }
            }
        }else{
            $total_price = self::find()->where(['pur_number'=>$purnumber])->sum('ctq*price');
            if (empty($total_price)) {
                $total_price = self::find()->where(['pur_number'=>$purnumber])->sum('qty*price');
                // $total_price = self::find()->where(['pur_number'=>$purnumber])->sum('items_totalprice');
            }
        }

        return $total_price;
    }
    /**
     * 根据采购单号获取sku
     * @param $purnumber
     * @return mixed
     */
    public static function getSkus($purnumber,$type=1,$code='SZ_AA',$limit=5)
    {
        $model = self::find()->select('sku,items_totalprice,price,ctq')->where(['pur_number'=>$purnumber])->limit($limit)->all();
        $s='';
       foreach($model as $v)
       {
           if($type==1)
           {
               $s.=$v->sku.' : '.$v->items_totalprice.' RMB'."<Br/>";
               $s.='包装方式：'.Product::getSkuCode($v->sku)."<Br/>";
               $sb= SupplierQuotes::getUrl($v->sku);
               $s.="采购链接：<a href='$sb' title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a><Br/>";
           } else{
               $s.=$v->sku.'&nbsp;&nbsp;RMB'.$v->price.'&nbsp;数量:'.$v->ctq.
               Html::a('',['product/viewskusales'], ['class' => "glyphicon glyphicon-signal b",'data'=>$v->sku , 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#create-modal',]).'&nbsp;'.
               Html::a('',['#'], ['class' => "fa fa-fw fa-th data-updates",'data'=>$v->sku , 'title' => '历史报价','data-toggle' => 'modal', 'data-target' => '#create-modal',])."<Br/>";
           }
       }
        return $s;

    }

    /**
     * 根据采购单号获取sku
     * @param $purnumber
     * @return false|null|string
     */
    public static function  getSKUc($purnumber)
    {
        return self::find()->select('sku,ctq')->where(['pur_number'=>$purnumber])->asArray()->all();
    }

    public function getSkuPurchaseLink(){
        return !empty($this->product) ? !empty($this->product->supplierQuote) ? !empty($this->product->supplierQuote->supplier_product_address) ? $this->product->supplierQuote->supplier_product_address : 'https://www.1688.com' : 'https://www.1688.com' : 'https://www.1688.com';
    }

    /**
     * 获取实际采购数量
     * @param $sku
     * @return bool|mixed
     */
    public static function getCtq($pur_number, $sku) {
        $ctq = self::find()->select('ctq')->where(['pur_number'=>$pur_number, 'sku'=>$sku])->scalar();
        return $ctq?:0;
    }
    /**
     * 获取建议采购数量
     * @param $sku
     * @return bool|mixed
     */
    public static function getQty($sku) {
        if (empty($sku)) {
            return false;
        }
        return self::find()->where(['sku'=>$sku])->one()['qty'];
    }

    /**
     * 获取采购单状态
     * @param $sku
     * @return bool|void
     */
    public static function getPurchasStatus($sku) {
//        Vhelper::dump($sku);
        if (empty($sku)) {
            return false;
        }
        $pur_number = self::find()->where(['sku'=>$sku])->one()['pur_number'];
        return PurchaseOrder::getPurchasStatus($pur_number);
    }
    public static function getOrderInfo($sku, $warehouse_code) {

        $created_at  = date('2018-01-01 00:00:00',time());
        $order_info = self::find()
            ->select(['id','pur_number','sku','name','qty','ctq','rqy','cty'])
            ->where(['sku'=>$sku])
            ->orderBy('id desc')
            ->limit(5)
            ->asArray()->all();

        foreach ($order_info as $k => $v) {
            $model = new PurchaseOrder();
            $ship_model = new PurchaseOrderShip();

            $res = $model::find()
                ->select(['created_at','buyer','creator','purchas_status','pay_status','shipping_method','account_type'])
                ->where(['pur_number'=>$v['pur_number'],'warehouse_code'=>$warehouse_code])
                ->andWhere(['>','created_at',$created_at])
                ->andWhere(['in','purchas_status',['3','5','6','7','8','9','10']])
                ->asArray()->one();

            $ship_res = $ship_model::find()
                ->select(['cargo_company_id','express_no'])
                ->where(['pur_number'=>$v['pur_number']])
                ->asArray()
                ->all();

            if (empty($res)) {
                unset($order_info[$k]);
            } else {
                $order_info[$k]['created_at'] = $res['created_at'];
                $order_info[$k]['account_type'] = $res['account_type'];
                $order_info[$k]['buyer'] = $res['buyer'];
                $order_info[$k]['creator'] = $res['creator'];
                $order_info[$k]['purchas_status'] = $res['purchas_status'];
                $order_info[$k]['pay_status'] = $res['pay_status'];
                $order_info[$k]['shipping_method'] = $res['shipping_method'];

                if (empty($order_info[$k]['shipping_method'])) {
                    $order_info[$k]['wuliu'] = '';
                } else {
                    $order_info[$k]['wuliu'] =PurchaseOrderServices::getShippingMethod($order_info[$k]['shipping_method']);
                }
                if (empty($ship_res)) {
                    $order_info[$k]['wuliu'] = '';
                } else {
                    foreach($ship_res as $key => $value) {
                        if(!empty($value['cargo_company_id'])) {
                            $s =!empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                            $url ='https://www.kuaidi100.com/chaxun?com='.$s.'&nu='.$value['express_no'];
                            $order_info[$k]['wuliu'] .= !preg_match ("/^[a-z]/i",$value['cargo_company_id'])?"<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>":"--<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>";   //主要通过此种方式实现
                            $order_info[$k]['wuliu'] .= (preg_match("/^[a-z]/i",$value['cargo_company_id'])?"<a target='_blank' href='$url'>" . $value['cargo_company_id'] : "<a target='_blank' href='$url'>" . $value['cargo_company_id']) . "</a>&nbsp;";
                            $order_info[$k]['wuliu'] .= $value['express_no'];
//                            $order_info[$k]['wuliu'] .= preg_match("/^[a-z]/i",$value['cargo_company_id']) ? BaseServices::getLogisticsCarrier($value['cargo_company_id'])->name:$value['cargo_company_id'];   //主要通过此种方式实现
                        }
                    }
                }
            }
        }

        $data='';
        foreach ($order_info as $k => $v) {
            $data .= '名称：'. $v['name'] .'</br>';
            $data .= '采购单号：'. $v['pur_number'] .'</br>';
            $data .= '实际采购数量：'. $v['ctq'] .'</br>';
            $data .= '采购时间：'. $v['created_at'] .'</br>';
            $pay_status = !empty($v['pay_status']) ? PurchaseOrderServices::getPayStatus($v['pay_status']) : '';
            $account_type = SupplierServices::getSettlementMethod($v['account_type']);
            $data .= '付款状态：'. $pay_status . ' -- ' . $account_type .'</br>';
            $purchas_status = !empty($v['purchas_status']) ? PurchaseOrderServices::getPurchaseStatus($v['purchas_status']) : '';
            $data .= '采购状态：'. $purchas_status .'</br>';
            $data .= '运输方式：'. $v['wuliu'] .'</br>';
            $data .= '创建人：'. $v['creator'] .'</br>';
            $data .= '采购员：<span style="color:#00a65a">'. $v['buyer'] .'</span></br>';
            $data .= '建议采购数量：'. $v['qty'] .'</br><hr>';
        }
        $data .= '<span style="color: red;">备注：已审批之后的采购数量，变成在途库存</span>';
        return $data;
    }
    /**
     * 获取sku实时查询的信息
     * @param  [type] $sku            [description]
     * @param  [type] $warehouse_code [description]
     * @return [type]                 [description]
     */
    public static function getOrderOne($data) {

        $ship_res = PurchaseOrderShip::find()
                ->select(['cargo_company_id','express_no'])
                ->where(['pur_number'=>$data['pur_number']])
                ->asArray()
                ->all();
        if (empty($data['shipping_method'])) {
                    $data['wuliu'] = '';
                } else {
                    $data['wuliu'] =PurchaseOrderServices::getShippingMethod($data['shipping_method']);
                }
                if (empty($ship_res)) {
                    $data['wuliu'] = '';
                } else {
                    foreach($ship_res as $key => $value) {
                        if(!empty($value['cargo_company_id'])) {
                            $s =!empty($value['cargo_company_id']) ? $value['cargo_company_id'] : '';
                            $url ='https://www.kuaidi100.com/chaxun?com='.$s.'&nu='.$value['express_no'];
                            $data['wuliu'] .= !preg_match ("/^[a-z]/i",$value['cargo_company_id'])?"<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>":"--<a target='_blank' href='$url'><span class='fa fa-fw fa-truck'  title='快递单号'></span></a>";   //主要通过此种方式实现
                            $data['wuliu'] .= (preg_match("/^[a-z]/i",$value['cargo_company_id'])?"<a target='_blank' href='$url'>" . $value['cargo_company_id'] : "<a target='_blank' href='$url'>" . $value['cargo_company_id']) . "</a>&nbsp;";
                            $data['wuliu'] .= $value['express_no'];
                        }
                    }
                }
        $state = is_array(PurchaseOrderServices::getState($data['state']))?'':PurchaseOrderServices::getState($data['state']);
        $sourcing_status = is_array(PurchaseOrderServices::getSourcingStatus($data['sourcing_status'])) ? '' : PurchaseOrderServices::getSourcingStatus($data['sourcing_status']);
        $warn_status = is_array(PurchaseOrderServices::getWarnStatus($data['warn_status']))?'':PurchaseOrderServices::getWarnStatus($data['warn_status']);

        $html = '';
        $html .= "采购单号：{$data['pur_number']}<br />"; 
        $html .= "建议采购数量：{$data['qty']}<br />";
        $html .= "实际采购数量：{$data['ctq']}<br />";
        $html .= "采购建议状态：{$state}（{$data['suggest_note']}）<br />";
        $html .= "货源状态：{$sourcing_status}<br />";
        $pay_status = is_array(PurchaseOrderServices::getPayStatus($data['pay_status'])) ? '' : PurchaseOrderServices::getPayStatus($data['pay_status']);
        $account_type = is_array(SupplierServices::getSettlementMethod($data['account_type'])) ? '' : SupplierServices::getSettlementMethod($data['account_type']);
        $html .= "采购付款状态：{$pay_status} -- {$account_type}<br />"; //??
        $pwa = PurchaseWarehouseAbnormal::find()->where(['purchase_order_no'=> $data['pur_number'], 'sku'=> trim($data['sku'])])->one();
        $msg = ''; //是否采购异常
        if (!empty($pwa)) $msg = "<span style='color:red'>采购单异常</span>";
        $cty = WarehouseResults::find()
        ->select('arrival_quantity')
        ->where(['pur_number'=>$data['pur_number'], 'sku' => $data['sku']])
        ->scalar();
        $cty = $cty?:0;
        $arrival_quantity = '';
        if ($cty<=0) {
            $data['purchas_status'] = 7; //等待到货
        } elseif ($data['ctq'] == $cty) {
            $data['purchas_status'] = 6; //全到货
        } else {
            $data['purchas_status'] = 5; //部分到货
            $arrival_quantity = '（到货数量{$cty}）';
        }
        $purchas_status = is_array(PurchaseOrderServices::getPurchaseStatus($data['purchas_status'])) ?'' : PurchaseOrderServices::getPurchaseStatus($data['purchas_status']);
        $html .= "采购到货状态：{$purchas_status} -- <span style='color:red'>{$arrival_quantity}</span> {$msg}<br />"; //-采购单异常
        $html .= "运输方式：{$data['wuliu']}<br />"; //
        $html .= "预警状态：{$warn_status}<br />"; 
        $html .= "创建人：{$data['creator']}<br />"; 
        $html .= "采购员：<span style='color:#00a65a'>{$data['buyer']}</span><br />"; 

        return $html;
    }
    public static function getAvailableStock($sku,$warehouse_code)
    {
        $order_info = self::find()->select(['id','pur_number','ctq','rqy'])->where(['sku'=>$sku])->asArray()->all();
        foreach ($order_info as $k => $v) {
            if ( !PurchaseOrder::find()->where(['pur_number'=>$v['pur_number']])->andWhere(['warehouse_code'=>$warehouse_code])->asArray()->one()) {
                unset($order_info[$k]);
            }
        }
        $sum = '';
        foreach ($order_info as $k => $v) {

            $sum += ($v['ctq'] - $v['rqy']);
        }
        // 在途数量
        return !empty($sum)?$sum:'0';
    }

    /**
     * 获取采购员
     * @param $sku
     * @return bool|void
     */
    public static function getBuyer($sku) {
        if (empty($sku)) {
            return false;
        }
        $pur_number = self::find()->where(['sku'=>$sku])->one()['pur_number'];
        return PurchaseOrder::getBuyer($pur_number);
    }

    /**
     * 获取采购单创建时间
     * @param $sku
     * @return bool|void
     */
    public static function getCreatedAt($sku) {
        if (empty($sku)) {
            return false;
        }
        $pur_number = self::find()->where(['sku'=>$sku])->one()['pur_number'];
        return PurchaseOrder::getCreatedAt($pur_number);
    }


    public static  function  getItem($pur_number,$sku,$filed='*')
    {
        return self::find()->select($filed)->where(['pur_number'=>$pur_number,'sku'=>$sku])->one();
    }

    /**
     * 获取采购单中的信息（最新的一条）
     * @param $field 获取那个字段的数据
     * @param $sku
     * @param null $purchas_status
     * @return bool|mixed
     */
    public static function getOrderOneInfo($sku,$purchas_status=null)
    {
        if (empty($sku)) {
            return false;
        }
        $pur_numbers = self::find()->where(['sku'=>$sku])->orderBy('id desc')->all();
        return PurchaseOrder::gerOrderOneInfo($pur_numbers,$purchas_status);
	}
	/**
     * 获取订单的总采购数量
     */
	public static function getCtqTotal($pur_number)
    {
        return self::find()->where(['pur_number'=>$pur_number])->sum('ctq');
    }

    public  function  getPurchaseSuggestNote()
    {
        return '';
        return $this->hasOne(PurchaseSuggestNote::className(),['sku'=>'sku'])
            ->leftJoin('pur_purchase_order','pur_purchase_order.warehouse_code=pur_purchase_suggest_note.warehouse_code');
    }
    /**
     * 对比近三次的下单单价
     * $price 当前单价
     */
    public static function comparePrice($sku,$price,$supplier_code)
    {
        $pur_items=PurchaseOrderItems::find()
            ->alias('b')
            ->leftJoin('pur_purchase_order as a','a.pur_number = b.pur_number')
            ->where(['NOT IN','a.purchas_status',[1,2,4,10]])
            ->andFilterWhere(['b.sku'=>$sku])
            ->orderBy('submit_time desc')
            ->asArray()
            ->all();

        $bool = 1; //默认：1黑色
        if (!empty($pur_items)) {
            //如果有数据，则不是新品
            foreach ($pur_items as $key => $value) {
                if ($price>$value['price']) {
                    //超过近3次单价中任意一个 此单单价都标红
                    $bool = 2; //2红色
                    break;
                }
                if ($price<$value['price']) {
                    $bool = 3; //3绿色
                }
                if ($key>=2) {
                    break;
                }
            }
        } else {
            //否则就是第一次下单
            $supplier_info = SupplierQuotes::getQuotes($sku,$supplier_code);
            if (!empty($supplier_info['supplierprice'])) {
                if ($price > (float)$supplier_info['supplierprice']) {
                    $bool = 2; //红色
                } elseif($price < (float)$supplier_info['supplierprice']) {
                    $bool = 3; //绿色
                }
            } else {
                $bool = 2; //红色
            }
        }
        return $bool;
    }

    //统计采购次数
    public static function getQuotes($sku,$purchase_type){
        $total = self::find()
            ->alias('items')
            ->leftJoin('pur_purchase_order order','order.pur_number=items.pur_number')
            ->andFilterWhere(['in','order.purchas_status',['3', '5', '6', '7', '8', '9', '10']])
            ->andFilterWhere(['items.sku'=>$sku])
            ->andFilterWhere(['order.purchase_type'=>$purchase_type])
            ->count();
        return $total;
    }
    /**
     * 海外仓信息系统-获取订单详情信息
     * 传入：PO和sku的二维数组
     */
    public static function getDetails($purchase_demand_sku_map)
    {
        $res = [];
        $cancel_id = false;
        if (is_array($purchase_demand_sku_map)) {
            foreach ($purchase_demand_sku_map as $key => $v) {
                $select = 'sku,price,name,ctq';
                $where = ['pur_number'=>$v['pur_number'], 'sku' => $v['sku']];
                $items = self::find()->select($select)->where($where)->asArray()->one();

                $res[$key]['pur_number'] = $v['pur_number'];//pur_number
                $res[$key]['sku'] = $v['sku'];//SKU
                $res[$key]['demand_number'] = $v['demand_number'];//需求单号
                $res[$key]['demand_status'] = PlatformSummary::getDemandStatus($v['demand_number']);//需求状态
                $res[$key]['price'] = $items['price'];//单价
                $res[$key]['name'] = $items['name'];//名称
                $res[$key]['ctq'] = $items['ctq'];//订单数量
                //状态

                $res[$key]['quxiao_ctq'] = PurchaseOrderCancelSub::getCancelCtq($v['pur_number'], $v['sku']); // 已取消数量
                $res[$key]['instock_qty_count'] = WarehouseResults::getInstockInfo($v['pur_number'],$v['sku'])['instock_qty_count'];//入库数量
                $mapInfo = OrderPayDemandMap::getPayPrice($v['demand_number']);
                $res[$key]['pay_price'] = $mapInfo['pay_price']; //请款金额
                $res[$key]['freight'] = OverseasPurchaseOrderSearch::getDemandPayInfo($v['demand_number'], 'freight'); //运费
                $res[$key]['discount'] = OverseasPurchaseOrderSearch::getDemandPayInfo($v['demand_number'], 'discount'); //优惠额
            }
        } else {
            //只有【作废订单待审核】的时候才可以审核
            $cancels = PurchaseOrderCancel::find()->where(['pur_number'=>$purchase_demand_sku_map, 'audit_status'=>1])->one();
            if (empty($cancels)) {
                #只有【作废订单待审核】的时候才可以审核
                Yii::$app->getSession()->setFlash('warning',"不是作废待审核状态的需求，不能够审核");
                return false;
            } else {
                $cancel_id = $cancels->id;
                foreach ($cancels->purchaseOrderCancelSub as $key => $v) {
                    $select = 'sku,price,name,ctq';
                    $where = ['pur_number'=>$v['pur_number'], 'sku' => $v['sku']];
                    $items = self::find()->select($select)->where($where)->asArray()->one();
                    
                    $res[$key]['pur_number'] = $v['pur_number'];//pur_number
                    $res[$key]['sku'] = $v['sku'];//SKU
                    $res[$key]['demand_number'] = $v['demand_number'];//需求单号
                    $res[$key]['price'] = $items['price'];//单价
                    $res[$key]['name'] = $items['name'];//名称
                    $res[$key]['ctq'] = $items['ctq'];//订单数量
                    //状态

                    $res[$key]['quxiao_ctq'] = PurchaseOrderCancelSub::getCancelCtq($v['pur_number'], $v['sku']); // 已取消数量
                    $res[$key]['instock_qty_count'] = WarehouseResults::getInstockInfo($v['pur_number'],$v['sku'])['instock_qty_count'];//入库数量
                    $res[$key]['cancel_ctq'] = $v['cancel_ctq']; //获取取消数量

                    $mapInfo = OrderPayDemandMap::getPayPrice($v['demand_number']);
                    $res[$key]['pay_status'] = $mapInfo['pay_status']; //请款状态
                    $res[$key]['price_type'] = $mapInfo['price_type']; //1:比例请款,2:手动请款
                    $res[$key]['pay_ratio'] = $mapInfo['pay_ratio']; //请款比例
                    $res[$key]['pay_price'] = $mapInfo['pay_price']; //请款金额
                    //旧的需求状态
                    $res[$key]['old_demand_status'] = PurchaseOrderCancelSub::find()->select('old_demand_status')->where(['demand_number'=>$v['demand_number']])->scalar();
                    $res[$key]['freight'] = OverseasPurchaseOrderSearch::getDemandPayInfo($v['demand_number'], 'freight'); //运费
                    $res[$key]['discount'] = OverseasPurchaseOrderSearch::getDemandPayInfo($v['demand_number'], 'discount'); //优惠额
                }
            }
        }
        return ['res' => $res, 'cancel_id'=>$cancel_id];
    }
}
