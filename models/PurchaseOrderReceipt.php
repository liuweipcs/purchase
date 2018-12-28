<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;
use app\models\PurchaseOrderPay;

/**
 * This is the model class for table "{{%purchase_order_receipt}}".
 *
 * @property integer $id
 * @property integer $pay_status
 * @property string $pur_number
 * @property string $requisition_number
 * @property string $supplier_code
 * @property integer $settlement_method
 * @property string $pay_name
 * @property string $pay_price
 * @property string $create_notice
 * @property integer $applicant
 * @property integer $auditor
 * @property integer $approver
 * @property string $application_time
 * @property string $review_time
 * @property string $processing_time
 * @property integer $pay_type
 * @property string $currency
 * @property string $review_notice
 * @property string $cost_types
 * @property integer $payer
 * @property string $payer_time
 * @property integer $payment_cycle
 * @property integer $step
 */
class PurchaseOrderReceipt extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_receipt}}';
    }
    public  $start_time;
    public  $end_time;
    public  $pay_types;
    public  $payment_notice;
    public  $buyer;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pay_status', 'settlement_method', 'applicant', 'pay_type', 'payer', 'payment_cycle','step'], 'integer'],
            [['pay_price'], 'number'],
            [['payment_notice', 'review_notice'], 'string'],
            [['application_time', 'review_time', 'processing_time', 'payer_time', 'payer_notice'], 'safe'],
            [['pur_number', 'requisition_number', 'supplier_code', 'pay_name'], 'string', 'max' => 30],
            [['currency'], 'string', 'max' => 20],
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
            'pay_status' => Yii::t('app', '付款状态(0作废1待审核2审核未通过3待审批4已审批（待付款）5已付款)'),
            'pur_number' => Yii::t('app', '采购单号'),
            'requisition_number' => Yii::t('app', '申请单号'),
            'supplier_code' => Yii::t('app', '供应商'),
            'settlement_method' => Yii::t('app', '结算方式'),
            'pay_name' => Yii::t('app', '名称'),
            'pay_price' => Yii::t('app', '金额'),
            'create_notice' => Yii::t('app', '创建备注'),
            'applicant' => Yii::t('app', '申请人'),
            'auditor' => Yii::t('app', '审核人'),
            'approver' => Yii::t('app', '审批人'),
            'application_time' => Yii::t('app', '申请时间'),
            'review_time' => Yii::t('app', '审核时间'),
            'processing_time' => Yii::t('app', '审批时间'),
            'pay_type' => Yii::t('app', '支付方式（从供应商拉取）'),
            'currency' => Yii::t('app', '币种'),
            'review_notice' => Yii::t('app', '审核备注'),
            'cost_types' => Yii::t('app', '费用类型'),
            'payer' => Yii::t('app', '收款人'),
            'payer_time' => Yii::t('app', '付款时间'),
            'payment_cycle' => Yii::t('app', '支付周期'),
            'step' => Yii::t('app', '退款步骤'),
            'payer_notice' => Yii::t('app', '收款备注'),
        ];
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
     * 关联取消货物数量 一对多
     */
    public function getPurchaseCancelQuantitys()
    {
        return $this->hasMany(PurchaseCancelQuantity::className(),['pur_number'=>'pur_number']);
    }


    /**
     * 保存单条数据
     * @param $data
     * @return bool
     */
    public function saveOne($data)
    {
        $model = self::find()->where(['pur_number'=>$data['pur_number']])->one();
        if(empty($model))
        {
            $this->pay_status = 1; //付款状态(0作废1待退款2已退款)
            $this->pur_number = $data['pur_number']; //采购单号
            $this->requisition_number = 'PR' . date('YmdHis') . mt_rand(10, 99); //申请单号
            $this->supplier_code = $data['supplier_code']; //供应商
            $this->settlement_method = empty($data['account_type']) ? 1 : $data['account_type']; //结算方式
            $this->pay_name = $data['pay_name']; //名称
            $this->pay_price = $data['pay_price']; //退款金额
            $this->applicant = $data['applicant']; //申请人ID
            $this->application_time = $data['application_time']; //申请时间
            $this->pay_type = empty($data['pay_type']) ? 1 : $data['pay_type']; //支付方式
            $this->currency = $data['currency_code']; //币种
            $this->review_notice = $data['review_notice']; //备注
            $this->payment_cycle = 3; //支付周期
            $this->step = $data['step']; //退款步骤
            $this->freight  = isset($data['freight'])?$data['freight']:0;  // 运费
            $this->discount = isset($data['discount'])?$data['discount']:0; // 优惠

            if (!empty($data['cancel_id'])) {
                PurchaseOrderCancel::saveRequisitionNumber($data['cancel_id'],$this->requisition_number);
            }

            //表修改日志-更新
            $change_content = TablesChangeLog::updateCompare($this->attributes, $this->oldAttributes);
            $change_data = [
                'table_name' => 'pur_purchase_order_receipt', //变动的表名称
                'change_type' => '2', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
            return $this->save(false);
        } else {
            return true;
        }

    }

    // 新增一条数据（数据表存在 pur_number, requisition_number 符合唯一索引）
    public function insertRow($data)
    {
        if(!empty($data)) {

            $this->pay_status         = 1;                              // 付款状态(0作废1待退款2已退款)
            $this->pur_number         = $data['pur_number'];            // 采购单号
            $this->requisition_number = 'PR' . date('YmdHis') . mt_rand(10, 99); // 申请单号
            $this->supplier_code      = $data['supplier_code'];         // 供应商
            $this->settlement_method  = empty($data['account_type']) ? 1 : $data['account_type']; // 结算方式
            $this->pay_name           = $data['pay_name'];              // 名称
            $this->pay_price          = $data['pay_price'];             // 退款金额
            $this->applicant          = $data['applicant'];             // 申请人ID
            $this->application_time   = $data['application_time'];      // 申请时间
            $this->pay_type           = empty($data['pay_type']) ? 1 : $data['pay_type']; // 支付方式
            $this->currency           = $data['currency_code'];
            $this->review_notice      = $data['review_notice'];
            $this->payment_cycle      = 3;                              // 支付周期
            $this->step               = $data['step'];                  // 退款步骤
            $this->freight            = isset($data['freight'])?$data['freight']:0;  // 运费
            $this->discount           = isset($data['discount'])?$data['discount']:0; // 优惠

            if (!empty($data['cancel_id'])) {
                PurchaseOrderCancel::saveRequisitionNumber($data['cancel_id'],$this->requisition_number);
            }
            return self::save(false);

            //表修改日志-新增
            $change_content = "insert:新增id值为{$this->id}的记录";
            $change_data = [
                'table_name' => 'pur_purchase_order_receipt', //变动的表名称
                'change_type' => '1', //变动类型(1insert，2update，3delete)
                'change_content' => $change_content, //变更内容
            ];
            TablesChangeLog::addLog($change_data);
        } else {
            return false;
        }
    }

    // 获取订单的退款信息
    public function getOrderRefundInfo($pur_number)
    {
        $data = self::find()
            ->alias('a')
            ->leftJoin('pur_user b', 'b.id=a.applicant')
            ->select(['a.pay_status','a.pay_price','a.currency','a.pur_number','a.requisition_number','a.application_time','a.discount','a.freight','b.username'])
            ->where(['a.pur_number' => $pur_number])
            ->asArray()
            ->all();

        $finalData = [
            'has_refund_money' => 0,
            'refund_num' => 0,
            'refund_status_list' => [],
            'currency' => '',
            'refund_list' => [],
            'has_refund_money_true'=>0
        ];

        if($data) {

            $finalData['refund_num'] = count($data);

            foreach($data as $k=>$v) {

                if(!in_array($v['pay_status'], [5])) {
                    $finalData['has_refund_money'] += $v['pay_price'];
                    $finalData['refund_status_list'][] = $v['pay_status'];
                    $finalData['currency'] = $v['currency'];
                    $finalData['refund_list'][] = $v;
                    if(!in_array($v['pay_status'], [10])){//驳回的不计算
                        $finalData['has_refund_money_true'] += $v['pay_price'];
                    }else{
                        $finalData['has_refund_money_true'] += 0;
                    }
                }

            }

            return $finalData;
        } else {
            return $finalData;
        }
    }

    public static function getTotalRefund($pur_number)
    {
        $price = self::find()
            ->select('sum(pay_price)')
            ->where(['pur_number' => $pur_number])
            ->andWhere(['not in', 'pay_status', [5,10]])
            ->scalar();
        return $price ? $price : 0;
    }

    // 退款申请金额校验
    public function verifyRefundEvent($data)
    {
        $refund_status = isset($data['refund_status']) ? (int)$data['refund_status'] : 0;
        //验证退货运费和优惠
        if($refund_status == 3 || $refund_status == 4){
            //获取可退运费和优惠
            $payType = PurchaseOrderPayType::find()->where(['pur_number'=>$data['pur_number']])->one();
            if($payType){
                //可退运费-已退运费  可退优惠-已退优惠 计算本次可退运费
                $freight = isset($payType->freight)?$payType->freight:0;
                $discount = isset($payType->discount)?$payType->discount:0;
                $quantity = self::find()
                    ->select('sum(discount) as discount,sum(freight) as freight')
                    ->where(['pur_number' => $data['pur_number']])
                    ->andWhere(['!=','pay_status', 10])
                    ->one();
                if($quantity){
                    if(!empty($quantity['freight']) && $freight>0){
                        $freight = bcsub($freight,$quantity['freight'],2);
                    }
                    if(!empty($quantity['discount']) && $discount>0){
                        $discount = bcsub($discount,$quantity['discount'],2);
                    }
                }
                if($data['freight']>$freight || $data['discount']>$discount){
                    return [
                        'error'   => 1,
                        'message' => '运费或者优惠不能超过金额限制'
                    ];
                }
            }
        }
        if($refund_status == 3) {
            $totalRefund = self::getTotalRefund($data['pur_number']);
            $totalPay = PurchaseOrderPay::getOrderPayMoney($data['pur_number']);

            //如果是取消未到货
            if ( isset($data['order_freight']) && isset($data['order_discount'])  && ($data['order_freight']>0 || $data['order_discount']>0) ) {
                if ( ($totalPay+$data['order_freight']-$data['discount']) < $data['money'] ) {
                    return [
                        'error'   => 1,
                        'message' => '总退款额已经超出了总支付额，不能再申请退款了'
                    ];
                }
            } else {
                if(($data['money'] + $totalRefund) > $totalPay) {
                    return [
                        'error'   => 1,
                        'message' => '总退款额已经超出了总支付额，不能再申请退款了'
                    ];
                }
            }
            return [
                'error'   => 0,
                'message' => '金额校验通过'
            ];
        } elseif($refund_status == 4) {
            return [
                'error'   => 0,
                'message' => '金额校验通过'
            ];
        } elseif($refund_status == 10) {
            $totalPaid = PurchaseOrderPay::getOrderPaidMoney($data['pur_number']);
            if($totalPaid > 0) {
                return [
                    'error'   => 1,
                    'message' => "订单已经支付了 {$totalPaid} ，不能直接作废"
                ];
            }
            return [
                'error' => 0,
                'message' => '作废检测通过'
            ];
        } else {
            return [
                'error' => 1,
                'message' => '未知的请求'
            ];
        }
    }
    /**
     * 通过申请单号获取收款状态
     */
    public static function getPayStatus($requisition_number)
    {
        return self::find()->select('pay_status')->where(['requisition_number'=>$requisition_number])->scalar();
    }

}
