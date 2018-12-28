<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
use app\config\Vhelper;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Exception;
use app\services\BaseServices;

class PurchaseOrderPay extends BaseModel
{
    static  public $applyForPayment = [2,4,5,6,10,13]; //已申请付款的订单
    static  public $alreadyPaid = [5,6]; //已付款的订单

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_pay}}';
    }
    public $pay_types;
    //到货状态
    public $receipt_status;
    //开始时间
    public $start_time;
    //结束时间
    public $end_time;




    public $undonePay = [2, 4, 10]; // 未完成的交易状态
    public $noPayStatus = [0, 3, 11, 12]; // 不做计算的请款状态
    public $disabledOrder = [1, 2, 4, 10]; // 不可再使用的订单状态
    public $chuna; // 出纳
    public $contact_person;
    public $contact_number;



    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class'=>TimestampBehavior::className(),
                'attributes'=>[
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT=>['application_time']
                ],
                'value'=>date('Y-m-d H:i:s',time()),
            ],
            [
                'class'=>BlameableBehavior::className(),
                'attributes'=>[
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT=>['applicant']
                ],
                'value'=>Yii::$app->user->id,
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pay_status', 'settlement_method', 'applicant', 'auditor', 'approver','pay_type'], 'integer'],
            [['pay_price'], 'number'],
            [['review_notice','create_notice'], 'string'],
            [['application_time', 'review_time', 'processing_time', 'payment_detail', 'source', 'pay_ratio', 'js_ratio', 'real_pay_price', 'images', 'pay_category'], 'safe'],
            [['pur_number', 'requisition_number', 'supplier_code', 'pay_name'], 'string', 'max' => 30],
            [['requisition_number'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pay_status' => Yii::t('app', '付款状态'),
            'purchase_type'=>Yii::t('app','采购单类型'),
            'pur_number' => Yii::t('app', '采购单号'),
            'requisition_number' => Yii::t('app', '申请单号'),
            'supplier_code' => Yii::t('app', '供应商'),
            'settlement_method' => Yii::t('app', '结算方式'),
            'pay_name' => Yii::t('app', '名称'),
            'pay_price' => Yii::t('app', '金额'),
            'notice' => Yii::t('app', '创建备注'),
            'applicant' => Yii::t('app', '申请人'),
            'auditor' => Yii::t('app', '审核人'),
            'approver' => Yii::t('app', '审批人'),
            'application_time' => Yii::t('app', '申请时间'),
            'review_time' => Yii::t('app', '审核时间'),
            'processing_time' => Yii::t('app', '审批时间'),
            'pay_type'        => Yii::t('app','支付方式'),
            'pay_types'        => Yii::t('app','收款方支付方式'),
            'review_notice'        => Yii::t('app','审批备注'),
            'pay_category' => Yii::t('app','请款种类'),
        ];
    }

    /**
     * 关联支付日志表一对多
     * @return \yii\db\ActiveQuery
     */
    public function getPurchaseOrderPayLogs()
    {
        return $this->hasMany(PurchaseOrderPayLog::className(), ['pay_id' => 'id']);
    }

    /**
     * 关联供应商
     * @return \yii\db\ActiveQuery
     */
    public  function  getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['supplier_code' => 'supplier_code']);
    }

    // 关联支付详情
    public function getOrderPayDetail()
    {
        return $this->hasOne(PurchaseOrderPayDetail::className(), ['pur_number' => 'pur_number', 'requisition_number' => 'requisition_number']);
    }



    /**
     * 关联采购单一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::className(),['pur_number'=>'pur_number']);
    }
    /**
     * 关联采购单一对多
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseOrders()
    {
        return $this->hasMany(PurchaseOrderItems::className(),['pur_number'=>'pur_number'])->andWhere(['>', 'ctq', 0]);
    }
    /**
     * 关联采购单运费一对一
     * @return \yii\db\ActiveQuery
     */
    public  function  getPurchaseOrderShip()
    {
        return $this->hasOne(PurchaseOrderShip::className(),['pur_number'=>'pur_number']);
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
        return $this->hasOne(PurchaseOrderOrders::className(),['pur_number'=>'pur_number']);
    }

    /**
     * 关联采购单与需求单号关系表一对多
     * @return \yii\db\ActiveQuery
     */
    public function getDemand(){
        return $this->hasMany(PurchaseDemand::className(),['pur_number'=>'pur_number']);
    }

    /**
     * 关联需求表一对多
     * @return $this
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


    public function getPurchaseOrderPayType()
    {
        return $this->hasOne(PurchaseOrderPayType::className(),['pur_number'=>'pur_number']);
    }

    /**
     * 关联alibaba开发者账号
     */
    public function getAlibabaAccount()
    {
        return $this->hasOne(AlibabaAccount::className(), ['bind_account' => 'applicant']);
    }
    /**
     * 关联请款详情表一对多
     * @return $this
     */
    public function getOrderPayDemandMap()
    {
        return $this->hasMany(OrderPayDemandMap::className(),['requisition_number'=>'requisition_number']);
    }
    /**
     * 关联请款单--一对多
     */
    public function getPurchaseCompact()
    {
        return $this->hasMany(PurchaseCompactItems::className(), ['compact_number' => 'pur_number'])->where(['bind' => 1]);
    }
    /**
     * 通过合同-关联采购单表-一对多
     */
    public function getCompactPurchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::className(), ['pur_number' => 'pur_number'])->via('purchaseCompact');
    }
    /**
     * 通过合同-关联需求中间表-一对多
     */
    public function getCompactDemand(){
        return $this->hasMany(PurchaseDemand::className(),['pur_number'=>'pur_number'])->via('purchaseCompact');
    }
    /**
     * 通过合同-关联需求表-一对多
     */
    public function getCompactPlatformSummary()
    {
        return $this->hasMany(PlatformSummary::className(),['demand_number'=>'demand_number'])->via('compactDemand');
    }
    
    /**
     * 保存单条数据
     * @param $data
     */
    public function  saveOne($data)
    {
        $model = new self;
        $model->load($data);
        $model->save();
    }

    /**
     * 更新付款状态
     * @param $pur_number
     * @return bool
     */
    public static function  saveStatus($pur_number)
    {

        $model = self::find()->where(['pur_number'=>$pur_number])->one();
        if($model)
        {
            $model->pay_status = 0;

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($model->attributes, $model->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_pay', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            return $model->save();
        }

    }

    public static function updatePayStatus($pur_number, $pay_status)
    {
        $models = self::findAll(['pur_number' => $pur_number]);
        if(!empty($models)) {
            $ids = [];
            foreach($models as $m) {
                $ids[] = $m->id;
                //表修改日志-更新
                $change_data = [
                    'table_name' => 'pur_purchase_order_pay', //变动的表名称
                    'change_type' => '2', //变动类型(1insert，2update，3delete)
                    'change_content' => "update:id:{$m->id},", //变更内容
                ];
                TablesChangeLog::addLog($change_data);
            }
            $updateIds = Vhelper::getSqlArrayString($ids);
            $res = self::updateAll(['pay_status' => $pay_status],"id in ({$updateIds})");
            return $res;
        } else {
            return true;
        }
    }

    /**
     * 获取请款单的金额信息
     * @param  object  $model        请款单对象 [PurchaseOrderPaySearch extends PurchaseOrderPay]
     * @param  boolean $isOnlyTotal [仅显示：优惠后的总额]
     * @param  integer $source      [判断是否是合同：1合同，2网采]
     * @param  boolean  $money_list  是否返回所有金额的值
     * @return mixed
     */
    public static function getPrice($model, $isOnlyTotal = false, $source=2 ,$money_list = false)
    {
        $freight = 0;
        $discount = 0;

        if(!empty($model->orderPayDemandMap)) {
            return self::getNewPrice($model,$isOnlyTotal,$source,$money_list);
        }

        if(!empty($model->purchaseOrderPayType)) {
            $freight = $model->purchaseOrderPayType->freight ? $model->purchaseOrderPayType->freight : 0;
            $discount = $model->purchaseOrderPayType->discount ? $model->purchaseOrderPayType->discount : 0;
        }
        //$final_money = $model->pay_price + $freight - $discount;
        //判断是否有勾选运费和折扣
        $detail = PurchaseOrderPayDetail::find()->where(['requisition_number'=>$model->requisition_number])->one();
        $is_check_freight = 1;
        $is_check_discount = 1;
        //计算原始金额  因为pay_price是如果勾选了运费、优惠 已经计算了的值
        $freight = empty($detail->freight)?0:$detail->freight;
        $discount = empty($detail->discount)?0:$detail->discount;
        //如果运费、优惠为0  判断是不是批量付款过来付款通知
        if( (stripos($model->pur_number,'FBA') !== false) AND $model->pay_category != 30 ){// FBA 非批量请款不读取订单运费、优惠

        }else{
            if( $freight==0 && $discount==0 ){
                $pay_type = PurchaseOrderPayType::find()->where(['pur_number' => $model->pur_number])->one();
                $freight  = empty($pay_type->freight) ? 0 : $pay_type->freight;
                $discount = empty($pay_type->discount) ? 0 : $pay_type->discount;
            }
        }
        //如果该采购单位合同单，pay_price存的是计算优惠和运费后的请款金额，所以原请款额要 减运费+优惠
        if ($source==1) $model->pay_price = $model->pay_price-$freight+$discount;

        $data  = '<span style="color: #E06B26;font-weight: bold;">金额：'. $model->pay_price .' '. $model->currency . '</span><br/>';
        $final_money = $model->pay_price;
        $data .= '<span style="color: #E06B26;font-weight: bold;">运费：' . $freight . ' ' . $model->currency . '</span><br/>';
        if($is_check_freight) {
            $final_money += $freight;
        }
        $data .= '<span style="color: #E06B26;font-weight: bold;">优惠：' . $discount . ' ' . $model->currency . '</span><br/>';
        if($is_check_discount) {
            $final_money -= $discount;
        }
        if ($isOnlyTotal) {
            return $final_money;
        }

        if($money_list === true){// 返回显示的金额的值
            return [
                'final_money'   => $final_money,
                'freight'       => $freight,
                'discount'      => $discount,
                'currency'      => $model->currency
            ];
        }

        $data .= '<span style="color: #E06B26;font-weight: bold;">优惠后：'. $final_money .' '. $model->currency . '</span><br/>';
        return $data;
    }
    /**
     * 获取请款单的金额信息
     * @param  object  $model        请款单对象 [PurchaseOrderPaySearch extends PurchaseOrderPay]
     * @param  boolean $isOnlyTotal [仅显示：优惠后的总额]
     * @param  integer $source      [判断是否是合同：1合同，2网采]
     * @param  boolean $money_list  是否返回所有金额的值
     * @return mixed
     */
    public static function getNewPrice($model, $isOnlyTotal = false, $source=2 , $money_list = false)
    {
        $freight = 0;
        $discount = 0;
        $orderPayDemandMap = $model->orderPayDemandMap;
        foreach ($orderPayDemandMap as $ordermap){
            $freight += $ordermap['freight'];
            $discount += $ordermap['discount'];
        }

        //如果该采购单位合同单，pay_price存的是计算优惠和运费后的请款金额，所以原请款额要 减运费+优惠
        if ($source==1) $model->pay_price = $model->pay_price-$freight+$discount;

        $data  = '<span style="color: #E06B26;font-weight: bold;">金额：'. $model->pay_price .' '. $model->currency . '</span><br/>';
        $final_money = $model->pay_price;
        $data .= '<span style="color: #E06B26;font-weight: bold;">运费：' . $freight . ' ' . $model->currency . '</span><br/>';

        $final_money += $freight;

        $data .= '<span style="color: #E06B26;font-weight: bold;">优惠：' . $discount . ' ' . $model->currency . '</span><br/>';

        $final_money -= $discount;

        if ($isOnlyTotal) {
            return $final_money;
        }

        if($money_list === true){// 返回显示的金额的值
            return [
                'final_money'   => $final_money,
                'freight'       => $freight,
                'discount'      => $discount,
                'currency'      => $model->currency
            ];
        }

        $data .= '<span style="color: #E06B26;font-weight: bold;">优惠后：'. $final_money .' '. $model->currency . '</span><br/>';
        return $data;
    }


    /**
     * 修改财务付款金额
     */
    public static function updatePayPrice($pur_number,$pay_price)
    {
        $order_pay = self::find()->where(['pur_number'=>Yii::$app->request->get('pur_number')])->one();
        $order_pay->pay_price = $pay_price;
        $status = $order_pay->save();
        return $status;
    }

    // 根据订单号获取订单的支付信息
    public static function getPayDetail($pur_number)
    {
        $finalData = [
            'count_pay_freight'  => 0,    // 总申请运费
            'count_use_discount' => 0,    // 总使用优惠额
            'countPayMoney'      => 0,    // 总请款金额
            'hasPaidMoney'       => 0,    // 财务已经付过的款的总额
            'payStatusList'      => [],   // 各个请款单的支付状态集
            'payList'            => [],   // 支付详情列表
            'skuNum'             => []    // 商品数量
        ];

        $rows = self::find()
            ->where(['pur_number' => $pur_number])
            ->andWhere(['in', 'pay_status', [2,4,5,6,10,13]])
            ->asArray()
            ->all();

        if(empty($rows))
            return $finalData;

        $dataSku = [];

        // 获取支付明细
        foreach($rows as $k => $v) {

            $finalData['countPayMoney'] += $v['pay_price'];
            $finalData['payStatusList'][] = $v['pay_status'];

            if(in_array($v['pay_status'], [5, 6])) {
                $finalData['hasPaidMoney'] += $v['pay_price'];
            }

            $det = PurchaseOrderPayDetail::findOne(['pur_number' => $v['pur_number'], 'requisition_number' => $v['requisition_number']]);

            if($det) {
                $v['freight']  = $det->freight;
                $v['discount'] = $det->discount;
                $v['sku_list'] = $det->sku_list;
            } else {
                $v['freight']  = 0;
                $v['discount'] = 0;
                $v['sku_list'] = null;
            }

            if($v['sku_list']) {
                $dataSku[] = json_decode($v['sku_list'],1);
            }

            if($v['freight'] > 0) {
                $finalData['count_pay_freight'] += $v['freight'];
            }

            if($v['discount'] > 0) {
                $finalData['count_use_discount'] += $v['discount'];
            }

        }

        $finalData['payList'] = $rows;

        $skuNum = [];

        if(!empty($dataSku)) {
            foreach($dataSku as $v) {
                foreach($v as $i){
                    if(isset($skuNum[$i['sku']])){
                        $skuNum[$i['sku']] += $i['num'];
                    } else {
                        $skuNum[$i['sku']] = $i['num'];
                    }
                }
            }
        }

        $finalData['skuNum'] = $skuNum;
        return $finalData;
    }

    // 根据主键 id 获取支付单详情
    public function getPayDetailById($id)
    {
        $pay = self::findOne($id);
        if($pay) {
            $detail = $pay->orderPayDetail;
            $pay_info = $pay->attributes;
            if($detail) {
                $pay_info['did']          = $detail->id;
                $pay_info['freight']      = $detail->freight;
                $pay_info['discount']     = $detail->discount;
                $pay_info['order_number'] = $detail->order_number;
                $pay_info['sku_list']     = $detail->sku_list;
            } else {
                $pay_info['did']          = 0;
                $pay_info['freight']      = 0;
                $pay_info['discount']     = 0;
                $pay_info['order_number'] = '';
                $pay_info['sku_list']     = '';
            }
            return $pay_info;
        } else {
            return null;
        }
    }

    // 获取订单历史请款总额，除去作废的请款单以外所有的请款额
    public static function getOrderPayMoney($pur_number)
    {
        $count = self::find()->select('sum(pay_price)')
            ->where(['pur_number' => $pur_number])
            ->andWhere(['not in', 'pay_status', [0, 3, 11, 12]])
            ->scalar();
        if (empty($count)) {
            $compact_number = PurchaseCompactItems::find()->select('compact_number')
                ->where(['pur_number' => $pur_number, 'bind'=>1])
                ->scalar();

            if (!empty($compact_number)) {
                $count = self::find()->select('sum(pay_price)')
                ->where(['pur_number' => $compact_number])
                ->andWhere(['not in', 'pay_status', [0, 3, 11, 12]])
                ->scalar();
            }
        }
        return $count ? $count : 0;
    }

    // 获取订单已支付金额
    public static function getOrderPaidMoney($pur_number)
    {
        $count = self::find()->select('sum(pay_price)')
            ->where(['pur_number' => $pur_number])
            ->andWhere(['in', 'pay_status', [5, 6]])
            ->scalar();
        if (empty($count)) {
            $compact_number = PurchaseCompactItems::find()->select('compact_number')
                ->where(['pur_number' => $pur_number, 'bind'=>1])
                ->scalar();
            if (!empty($compact_number)) {
                $count = self::find()->select('sum(pay_price)')
                    ->where(['pur_number' => $compact_number])
                    ->andWhere(['in', 'pay_status', [5, 6]])
                    ->scalar();
            }
        }

        return $count ? $count : 0;
    }


    /**
     * 获取订单所有支付单的支付状态
     */
    public static function getOrderPayStatus($pur_number)
    {
        $list = self::find()
            ->select(['id', 'pay_status'])
            ->where(['pur_number' => $pur_number])
            ->andWhere(['not in', 'pay_status', [0]])
            ->asArray()
            ->all();
        if(!$list) {
            return '';
        }
        return $list;
    }

    // 根据主键id获取合同的请款数据及合同其它数据
    public static function getCompactPayData($model)
    {
        try {
            $pos = PurchaseCompact::getPurNumbers($model->pur_number);
            $models = PurchaseOrder::find()->where(['in', 'pur_number', $pos])->all();
            foreach($models as $m) {
                $items = $m->purchaseOrderItemsCtq;
                if(!empty($items)) {
                    $data[$m->pur_number] = $items;
                }
            }
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    // 根据主键id获取合同的请款数据及合同其它数据
    public static function getCompactPayDataV2($model)
    {
        try {
            $pos = PurchaseCompact::getPurNumbers($model->pur_number);
            $data = [];
            foreach($pos as $p) {
                $data[] = PurchaseOrder::getOrderSkuInfo($p,$model->requisition_number);
            }
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    // 获取合同的信息列表数据
    public static function getCompactSkuList($cpn)
    {
        try {
            $pos = PurchaseCompact::getPurNumbers($cpn);
            $models = PurchaseOrder::find()
                ->joinWith('purchaseOrderItems')
                ->where(['in', 'pur_purchase_order.pur_number', $pos])
                ->asArray()
                ->all();
            return $models;
        } catch (\Exception $e) {
            return null;
        }
    }

    // 检测采购单或合同单有没有请款记录
    public static function checkIsPayment($p)
    {
        $result = self::find()
            ->where(['pur_number' => $p])
            ->andWhere(['in', 'pay_status', [-1, 2, 4, 5, 6, 10]])
            ->all();
        return $result ? true : false;
    }

    // 获取合同已申请金额
    public function getCompactApplyMoney($cpn)
    {
        $money = self::find()->select('sum(pay_price)')
            ->where(['pur_number' => $cpn])
            ->andWhere(['not in', 'pay_status', $this->noPayStatus])
            ->scalar();
        return $money ? $money : 0;
    }

    //检验批量付款的数据是否能一起付款
    public static function checkPayApplyDatas($ids){
        if(empty($ids)){
            return ['status'=>'error','message'=>'请至少选择一个付款申请'];
        }
        $datas=$orderPayDatas = PurchaseOrderPay::find()
            ->where(['id'=>$ids])->asArray()->all();
        $payStatusArray = array_unique(array_column($datas,'pay_status'));
        if(count($payStatusArray)!=1||!in_array(4,$payStatusArray)){
            return ['status'=>'error','message'=>'勾选的数据不全部是待付款的数据'];
        }
        $sourceArray = array_unique(array_column($datas,'source'));
        if(count($sourceArray)!=1||empty($sourceArray)){
            return ['status'=>'error','message'=>'勾选的请款数据不是同一类型，网采，合同一次只能支付一个类型'];
        }
        if(in_array(1,$sourceArray)){
            $compact_numbers = array_column($datas,'pur_number');
            $drawback_info = PurchaseCompact::find()->select('is_drawback')->where(['compact_number'=>$compact_numbers])
                            ->column();
            if(count(array_unique($drawback_info))!=1||empty($drawback_info)){
                return ['status'=>'error','message'=>'勾选的付款申请对应的采购单不存在或存在多个退税属性'];
            }
        }

        if(in_array(2,$sourceArray)){
            $pur_numbers = array_column($datas,'pur_number');
            $drawback_info = PurchaseOrder::find()->select('is_drawback')->where(['pur_number'=>$pur_numbers])
                ->column();
            if(count(array_unique($drawback_info))!=1||empty($drawback_info)){
                return ['status'=>'error','message'=>'勾选的付款申请对应的采购单不存在或存在多个退税属性'];
            }
        }
        $supplierCodedArray = array_unique(array_column($datas,'supplier_code'));
        if(count($supplierCodedArray)!=1||empty($supplierCodedArray)){
            return ['status'=>'error','message'=>'勾选的数据不全部是同一家供应商'];
        }
        return ['status'=>'success','message'=>'验证通过','source'=>$sourceArray[0],'is_drawback'=>$drawback_info[0]];

    }
    //保存富友在线付款结果
    public static function saveFuiouPayResult($fuiouPayDatas,$fuiouPay){
        $ids = isset($fuiouPayDatas['ids']) ? $fuiouPayDatas['ids'] : '';
        if(empty($ids)){
            return ['status'=>'error','message'=>'付款信息不能为空'];
        }
        $ids = !is_array($ids) ? explode(',',$ids) :$ids;
        $payStatusArray = self::find()->select('pay_status')->where(['id'=>$ids])->column();
        $tranNo = isset($fuiouPay['tran']) ? $fuiouPay['tran'] :'';
        if(count(array_unique($payStatusArray))!==1 || !in_array(4,$payStatusArray)){
            if(isset($fuiouPay['status'])&&$fuiouPay['status']=='success'){
                $message = '存在重复付款的申请,请联系复核员驳回';
                return ['status'=>'error','message'=>empty($tranNo)? $message :$message.'交易流水号：'.$tranNo];
            }else{
                return ['status'=>'error','message'=>'存在重复付款的申请,请重新付款'];
            }
        }
        //接口请求成功
        if(isset($fuiouPay['status'])&&$fuiouPay['status']=='success'){
            //数据在富友对接成功
            if(isset($fuiouPay['responseBody']['rspCode'])&&$fuiouPay['responseBody']['rspCode']=='0000'){
                $responseMessage = isset($fuiouPay['responseBody']['rspDesc']) ? $fuiouPay['responseBody']['rspDesc'] : '数据对接成功，请检查是否成功提交转账申请！';
                $bankInfo = BankCardManagement::find()->where(['id'=>$fuiouPayDatas['Fuiou']['PayInfo'],'status'=>1])->asArray()->one();
                $tran = Yii::$app->db->beginTransaction();
                try{
                    foreach ($ids as $payApplyId){
                        $model = self::findOne(['id'=>$payApplyId,'pay_status'=>4]);
                        if(empty($model)){
                            throw new Exception('操作了不存在的付款申请,或者付款申请状态不符合要求');
                        }
                        $model->pay_status     = 13;//富友付款待审核
                        $model->payer          = Yii::$app->user->id;
                        $model->payer_time     = date('Y-m-d H:i:s', time());
                        $model->pay_account    = !empty($bankInfo)&&!empty($bankInfo['id']) ?  $bankInfo['id'] :0;
                        $model->pay_number    = !empty($bankInfo)&&!empty($bankInfo['account_number']) ?  $bankInfo['account_number'] :'';
                        $model->k3_account    = !empty($bankInfo)&&!empty($bankInfo['k3_bank_account']) ?  $bankInfo['k3_bank_account'] :'';
                        $model->pay_branch_bank    = !empty($bankInfo)&&!empty($bankInfo['branch']) ?  $bankInfo['branch'] :'';
                        if($model->save()==false){
                            throw new Exception('付款状态修改失败'.implode(',',$model->getFirstErrors()));
                        }
                        $s = [
                            'pur_number' => $model->pur_number,
                            'note'       => '富友在线付款,交易流水号：'.$tranNo,
                        ];
                        PurchaseLog::addLog($s);//添加采购日志
                        //绑定交易流水号和付款申请单号的关系
                        $saveBind = PurchaseOrderPayUfxfuiou::saveBindInfo($model,$tranNo);
                        if(!$saveBind){
                            throw new Exception('交易流水号绑定失败');
                        }
                    }
                    //保存交易申请详情
                    $saveDetail = UfxfuiouPayDetail::savePayDetail($fuiouPayDatas,$fuiouPay);
                    if(!$saveDetail){
                        throw new Exception('付款详情保存失败');
                    }
                    $tran->commit();
                    return ['status'=>'success','message'=>$responseMessage];
                }catch (Exception $e){
                    $tran->rollBack();
                    return ['status'=>'warning','message'=>$e->getMessage()];
                }
            }else{
                $responseMessage = isset($fuiouPay['responseBody']['rspDesc']) ? $fuiouPay['responseBody']['rspDesc'] : '数据对接失败，请检查是否成功提交转账申请！';
                return ['status'=>'warning','message'=>$responseMessage];
            }
        }else{
            return ['status'=>'warning','message'=>isset($fuiouPay['message']) ? $fuiouPay['message']: '付款异常，请联系技术部解决'];
        }
    }
    /**
     * 获取请款时的创建备注
     * 返回数据类型：1一维数组，2二维数组,3字符串
     */
    public static function getCreateNotice($where,$type=2,$field='create_notice')
    {
        $create_notice = [];
        if (empty($where)) return $create_notice;
        $res = self::find()->select('applicant,create_notice')->where($where)->asArray()->all();
        if($res && $type==3){
            $str ='';
            foreach($res as $v) $str .="\r\n".BaseServices::getEveryOne($v['applicant']).':'.$v['create_notice'];
            return $str;
        }
        if ($type==1) $res = array_column($res,$field);
        return $res;
    }

    /**
     * 合同-请款名称：付款单种类明细
     * @param $compactNumber 合同号
     * @param $payCategory 请款方式：比例请款(12),手动请款(21)
     * @param $payPrice 此次请款总额：这个合同的请款总额100
     * @param $orderFreight 此次请款的运费：当前填写的运费20
     * @param $payRatio 请款时的比例：当前请款 20%
     */
    public static function getPayCategory($compactNumber,$payCategory,$payPrice,$orderFreight,$payRatio=21)
    {
        $payinfo = ['ratio'=>$payRatio, 'pay_category'=>$payCategory];
        //合同运费：请款金额和运费
        if (!empty($payPrice) &&  !empty($orderFreight) ) {
            $bcc_pay_price = sprintf("%.3f",substr(sprintf("%.4f", $payPrice), 0, -1));
            $bcc_freight = sprintf("%.3f",substr(sprintf("%.4f", $orderFreight), 0, -1));
            $bcc_freight_pay_price = bccomp($bcc_pay_price, $bcc_freight, 3); // 0相等
            if ($bcc_freight>0 && $bcc_freight_pay_price==0 ) {
                $payinfo['pay_category'] = $payCategory = 22; //合同运费
            }
        }

        //比例请款
        if ($payCategory == 12) {
            $compact = PurchaseCompact::find()->where(['compact_number' => $compactNumber])->one();
            $settlement_ratio_arr = explode('+', $compact->settlement_ratio);
            $ratioCount = count($settlement_ratio_arr);

            $payModelCount = PurchaseOrderPay::find()
                ->from(PurchaseOrderPay::tableName().' as op')
//                ->leftJoin(OrderPayDemandMap::tableName().' as pdm', 'pdm.requisition_number = op.requisition_number')
                ->where(['op.pur_number'=>$compactNumber])
                ->andWhere(['in','op.pay_status', self::$applyForPayment])
//                ->select('pay_amount,pdm.requisition_number,op.pay_status') //pay_amount,freight,discount,a.requisition_number,b.payer_time,b.pay_status
                ->groupBy('op.requisition_number')
                ->count();

            //100% 合同全额付款
            if ($ratioCount == 1) {
                $payinfo['ratio'] = $settlement_ratio_arr[0];
                $payinfo['pay_category'] = 11;
            } elseif ($ratioCount == 2) {
                foreach ($settlement_ratio_arr as $k => $ratio) {
                    if ($payRatio == intval($ratio) ) {
                        $payinfo['ratio'] = $ratio;
                        $payinfo['pay_category'] = ($k==0)?12:20; //合同付订金(12),合同付尾款(20)
                        if ($payModelCount>0) $payinfo['pay_category'] = 20; //如果有付款记录：则合同付尾款(20)
                        break;
                    }
                }
            }  elseif ($ratioCount == 3) {
                //有三次比例的
                foreach ($settlement_ratio_arr as $k => $ratio) {
                    if ($payRatio == intval($ratio) ) {
                        $payinfo['ratio'] = $ratio;
                        $payinfo['pay_category'] = ($k==0)?12:( ($k==1)? 13 : 20); //合同付订金(12),合同付中期款项(13),合同付尾款(20)
                        if ($payModelCount == 1) {
                            //如果已请款一次
                            $payinfo['pay_category'] = 13;
                        } elseif ($payModelCount >1) {
                            //如果大于两次请款
                            $payinfo['pay_category'] = 20;
                        }
                        break;
                    }
                }
            }
        }
        return $payinfo;
    }

    /**
     * 如果合同单付了订金或中款，未付尾款
     * 不用生成退款单，直接在付尾款时减掉取消的金额
     * is_paid_pail=true 生成退款单， false请尾款时，需要减掉取消金额
     * return ['is_paid_pail'=>true,'cancel_price'=>0]
     */
    public static function getCancellationAmount($pur_number)
    {
        //is_paid_pail 是否已付尾款，是：需要生成退款单，否：尾款里面扣
        //cancel_price 取消金额
        $res = ['is_paid_pail'=>true,'cancel_price'=>0];

        $compactItemsInfo = PurchaseCompactItems::find()->where(['pur_number'=>$pur_number,'bind'=>1])->asArray()->all();
        if (empty($compactItemsInfo)) return ['is_paid_pail'=> true]; //判断是否是合同，不是合同，【生成退款单】

        $isPaidTail = self::find()->where(['pur_number'=>$pur_number,'pay_category'=>20, 'pay_status'=>self::$alreadyPaid])->one(); //已付尾款
        if ($isPaidTail) return ['is_paid_pail'=> true]; //如果已付尾款，就直接【生成退款单】

        //已付订金和中款
        $payInfo = self::find()
            ->where(['pur_number'=>$compactItemsInfo[0]['compact_number'],'pay_category'=>[12,13], 'pay_status'=>self::$alreadyPaid]) //已付定金和中款
            ->asArray()->all();
        if (empty($payInfo)) return ['is_paid_pail'=> true]; //未付订金和中款，【生成退款单】

        //如果已付订金和中款，未付尾款
        foreach ($compactItemsInfo as $k => $v) {
            //获取已经取消货物【金额】 -- 审核过的（整个单的）
            $cancel_price = PurchaseOrderCancelSub::getCancelPriceOrder($pur_number);
            $res['cancel_price'] += $cancel_price;
        }

        $res['is_paid_pail'] = false; //不需要生成退款单，金额在尾款里面减
        return $res;
    }
}
