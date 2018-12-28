<?php
namespace app\models;

use app\models\base\BaseModel;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use app\config\Vhelper;

class PurchaseOrderReceiptWater extends BaseModel
{
    public $start_time;
    public $end_time;

    public static function tableName()
    {
        return '{{%purchase_order_receipt_water}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => [],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
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

    // 保存单条数据
    public static function saveOne($data)
    {
        $water                             = new self;
        $water->supplier_code              = $data['supplier_code']; // 结算对像(供应商code)
        $water->pur_number                 = $data['pur_number']; // 采购单号
        $water->original_currency          = $data['currency'];
        $water->beneficiary_payment_method = $data['pay_types']; // 收款方支付方式
        $water->transaction_number         = 'PY' . date('YmdHis', time()) . mt_rand(10, 99); // 交易号

        $water->price                      = $data['pay_price'];
        $water->original_price             = $data['pay_price'];

        // 银行卡信息
        $water->our_branch                 = $data['branch']; // 我方支行（中国银行深圳龙华中国银行支行）
        $water->our_account_abbreviation   = $data['account_abbreviation']; // 我方支行简称（银行卡账号后四位）
        $water->our_account_holder         = $data['account_holder']; // 我方开户人

        $water->pay_time                   = $data['payer_time'];
        $water->remarks                    = $data['payment_notice'];

        $water->is_bill                    = 2; // 是否指定账单（1是2否）
        $water->write_off_sign             = 2; // 核销完成标志(1是2否）
        $water->monthly_checkout           = 1; // 参与月结账(1是2否）
        $water->internal_offset_sign       = 2; // 内部抵销标志(1是2否）
        $water->billing_object_type        = 1; // 结算对象类型

        return $water->save(false);
    }

}
