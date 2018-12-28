<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_receive".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $supplier_code
 * @property string $supplier_name
 * @property string $buyer
 * @property string $sku
 * @property string $name
 * @property string $qty
 * @property string $delivery_qty
 * @property string $presented_qty
 * @property string $receive_type
 * @property string $handle_type
 * @property string $handler
 * @property string $auditor
 * @property string $bearer
 * @property string $created_at
 * @property string $time_handle
 * @property string $time_audit
 * @property string $receive_status
 * @property string $creator
 * @property string $price
 * @property string $note
 */
class PurchaseReceive extends BaseModel
{
    public $total_qty;//条目总数量
    public $total_delivery_qty;
    public $total_presented_qty;
    public $total_refund_amount;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_receive';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'supplier_code', 'supplier_name', 'buyer', 'sku', 'name', 'qty', 'delivery_qty', 'presented_qty', 'receive_type', 'handle_type', 'created_at', 'creator','currency_code'], 'required'],
            [['qty', 'delivery_qty', 'presented_qty'], 'integer'],
            [['receive_type', 'handle_type', 'bearer', 'receive_status'], 'string'],
            [['created_at', 'time_handle', 'time_audit'], 'safe'],
            [['price'], 'number'],
            [['pur_number', 'buyer', 'sku', 'handler', 'auditor', 'creator','is_return'], 'string', 'max' => 20],
            [['supplier_code', 'supplier_name'], 'string', 'max' => 30],
            [['name', 'note','note_handle','note_audit'], 'string', 'max' => 300],
            [['currency_code'], 'string', 'max' => 10],
            [['pur_number', 'sku'], 'unique', 'targetAttribute' => ['pur_number', 'sku'], 'message' => 'The combination of Pur Number and Sku has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => '采购单',
            'supplier_code' => '供应商编码',
            'supplier_name' => '供应商名称',
            'buyer' => '采购员',
            'sku' => 'Sku',
            'name' => '产品名称',
            'qty' => '预期数量',
            'delivery_qty' => '到货数量',
            'presented_qty' => '赠送数量',
            'receive_type' => '异常类型',
            'handle_type' => '处理方式',
            'handler' => '处理人',
            'auditor' => '审核人',
            'bearer' => '承担方',
            'created_at' => '创建时间',
            'time_handle' => '处理时间',
            'time_audit' => '审核时间',
            'receive_status' => '状态',
            'creator' => '创建人',
            'price' => '单价',
            'note' => '备注',
            'note_handle' => '处理留言',
            'note_audit' => '审核留言',
            
            'is_return' => 'Is Return',
            'currency_code' => 'Currency Code',
        ];
    }
}
