<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_order_pay_bak".
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
 * @property string $payment_notice
 * @property integer $source
 * @property string $pay_ratio
 * @property string $js_ratio
 * @property string $real_pay_price
 * @property string $images
 * @property string $purchase_account
 * @property string $pai_number
 * @property integer $pay_category
 */
class PurchaseOrderPayBak extends BaseModel
{
    public $file_execl;
    public $buyer_id;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_order_pay_bak';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pay_status', 'settlement_method', 'applicant', 'auditor', 'approver', 'pay_type', 'payer', 'payment_cycle', 'source', 'pay_category'], 'integer'],
            [['pay_price', 'real_pay_price'], 'number'],
            [['application_time', 'review_time', 'processing_time', 'payer_time'], 'safe'],
            [['pur_number', 'requisition_number', 'supplier_code', 'pay_name', 'pay_ratio'], 'string', 'max' => 30],
            [['create_notice'], 'string', 'max' => 1000],
            [['currency', 'cost_types'], 'string', 'max' => 20],
            [['review_notice', 'payment_notice'], 'string', 'max' => 200],
            [['js_ratio', 'purchase_account', 'pai_number'], 'string', 'max' => 100],
            [['images'], 'string', 'max' => 255],
            [['pur_number', 'requisition_number'], 'unique', 'targetAttribute' => ['pur_number', 'requisition_number'], 'message' => 'The combination of Pur Number and Requisition Number has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pay_status' => 'Pay Status',
            'pur_number' => 'Pur Number',
            'requisition_number' => 'Requisition Number',
            'supplier_code' => 'Supplier Code',
            'settlement_method' => 'Settlement Method',
            'pay_name' => 'Pay Name',
            'pay_price' => 'Pay Price',
            'create_notice' => 'Create Notice',
            'applicant' => 'Applicant',
            'auditor' => 'Auditor',
            'approver' => 'Approver',
            'application_time' => 'Application Time',
            'review_time' => 'Review Time',
            'processing_time' => 'Processing Time',
            'pay_type' => 'Pay Type',
            'currency' => 'Currency',
            'review_notice' => 'Review Notice',
            'cost_types' => 'Cost Types',
            'payer' => 'Payer',
            'payer_time' => 'Payer Time',
            'payment_cycle' => 'Payment Cycle',
            'payment_notice' => 'Payment Notice',
            'source' => 'Source',
            'pay_ratio' => 'Pay Ratio',
            'js_ratio' => 'Js Ratio',
            'real_pay_price' => 'Real Pay Price',
            'images' => 'Images',
            'purchase_account' => 'Purchase Account',
            'pai_number' => 'Pai Number',
            'pay_category' => 'Pay Category',
        ];
    }
}
