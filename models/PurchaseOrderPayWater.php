<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
class PurchaseOrderPayWater extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_pay_water}}';
    }
    public  $start_time;
    public  $end_time;
    public function behaviors()
    {
        return [
//            [
//                'class' => TimestampBehavior::className(),
//                'attributes' => [
//                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
//                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => [],
//                ],
//                // if you're using datetime instead of UNIX timestamp:
//                'value' => date('Y-m-d H:i:s',time()),
//            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_id'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => [],
                ],
                'value' => Yii::$app->user->id,
            ],


        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_bill', 'write_off_sign', 'monthly_checkout', 'internal_offset_sign', 'create_id', 'beneficiary_payment_method'], 'integer'],
            [['price', 'write_off_price', 'original_price'], 'number'],
            [['create_time', 'pay_time'], 'safe'],
            [['pur_number', 'supplier_code', 'transaction_number', 'beneficiary_account', 'beneficiary_account_name', 'our_account_holder'], 'string', 'max' => 30],
            [['billing_object_type', 'our_account_abbreviation'], 'string', 'max' => 20],
            [['original_currency'], 'string', 'max' => 10],
            [['remarks'], 'string', 'max' => 200],
            [['beneficiary_branch', 'our_branch'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                         => Yii::t('app', 'ID'),
            'pur_number'                 => Yii::t('app', '采购单号'),
            'supplier_code'              => Yii::t('app', '结算对像(供应商code)'),
            'billing_object_type'        => Yii::t('app', '结算对象类型'),
            'transaction_number'         => Yii::t('app', '交易号'),
            'is_bill'                    => Yii::t('app', '是否指定账单'),
            'price'                      => Yii::t('app', '金额'),
            'write_off_price'            => Yii::t('app', '已核销金额'),
            'original_price'             => Yii::t('app', '原金额'),
            'original_currency'          => Yii::t('app', '原币种'),
            'write_off_sign'             => Yii::t('app', '核销完成标志'),
            'monthly_checkout'           => Yii::t('app', '参与月结账'),
            'internal_offset_sign'       => Yii::t('app', '内部抵销标志'),
            'remarks'                    => Yii::t('app', '备注'),
            'create_id'                  => Yii::t('app', '创建人'),
            'create_time'                => Yii::t('app', '创建时间'),
            'beneficiary_payment_method' => Yii::t('app', '收款方支付方式'),
            'beneficiary_branch'         => Yii::t('app', '收款方支行'),
            'beneficiary_account'        => Yii::t('app', '收款方帐号'),
            'beneficiary_account_name'   => Yii::t('app', '收款方开户名'),
            'our_branch'                 => Yii::t('app', '我方支行'),
            'our_account_abbreviation'   => Yii::t('app', '我方支行简称'),
            'our_account_holder'         => Yii::t('app', '我方开户人'),
            'pay_time'                   => Yii::t('app', '交易时间'),
        ];
    }

    /**
     * 保存单条数据
     * @param $data
     * @return bool
     */
    public static function saveOne($data)
    {

        $water                             = new self;
        $water->supplier_code              = $data['supplier_code'];
        $water->pur_number                 = $data['pur_number'];
        $water->original_currency          = !empty($data['currency'])?$data['currency']:'RMB';
        $water->beneficiary_payment_method = $data['pay_types'];
        $water->transaction_number         = 'PY' . date('YmdHis', time()) . mt_rand(10, 99);
        $water->billing_object_type        = 1;
        $water->price                      = $data['pay_price'];
        $water->original_price             = $data['pay_price'];
        $water->our_branch                 = $data['branch'];
        $water->our_account_abbreviation   = $data['account_abbreviation'];
        $water->our_account_holder         = $data['account_holder'];
        $water->create_time                = !empty($data['created_at'])?$data['created_at']:date('Y-m-d H:i:s');
        $water->pay_time                   = !empty($data['payer_time'])?$data['payer_time']:date('Y-m-d H:i:s');
        $water->is_bill                    = 2;
        $water->write_off_sign             = 2;
        $water->monthly_checkout           = 1;
        $water->internal_offset_sign       = 2;
        return $water->save(false);
    }

    // 1688支付时的流水记录
    public static function saveOneForAli($data)
    {
        $water = new self;
        $water->pur_number            = $data['pur_number'];
        $water->supplier_code         = $data['supplier_code'];
        $water->billing_object_type   = 1;
        $water->transaction_number    = 'PY' . date('YmdHis', time()) . mt_rand(10, 99);
        $water->is_bill               = 2;
        $water->price                 = $data['pay_price'];
        $water->original_price        = $data['pay_price'];
        $water->original_currency     = $data['currency'];
        $water->our_account_abbreviation = $data['account_abbreviation'];
        $water->write_off_sign        = 2;
        $water->monthly_checkout      = 1;
        $water->internal_offset_sign  = 2;
        $water->remarks               = '1688批量在线付款V3.0';
        $water->create_id             = Yii::$app->user->id;
        $water->pay_time              = date('Y-m-d H:i:s', time());
        $water->create_time           = date('Y-m-d H:i:s', time());
        return $water->save(false);
    }

    // 合同单支付流水
    public static function saveOneForCompact($data)
    {
        $water = new self;
        $pay_water = $data['PayWater'];
        $pay_water['remarks'] = $data['payment_notice'];
        $pay_water['write_off_price'] = $data['real_pay_price'];
        $pay_water['original_price'] = $pay_water['price'];
        $pay_water['pay_time'] = $data['payer_time'];
        $pay_water['create_id'] = Yii::$app->user->id;
        $pay_water['create_time'] = date('Y-m-d H:i:s', time());

        $water->attributes = $pay_water;
        $water->transaction_number = 'PY'.date('YmdHis', time()).mt_rand(10, 99);
        $water->billing_object_type = 1;  // 结算对象类型
        $water->is_bill = 2; // 是否指定账单（1是 2否）
        $water->write_off_sign = 2; // 核销完成标志(1是 2否）
        $water->monthly_checkout = 1; // 参与月结账(1是 2否）
        $water->internal_offset_sign = 2; // 内部抵销标志(1是 2否）
        return $water->save(false);
    }

}
