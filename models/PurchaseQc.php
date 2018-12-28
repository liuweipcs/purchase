<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_purchase_qc".
 *
 * @property string $id
 * @property string $express_no
 * @property string $pur_number
 * @property string $warehouse_code
 * @property string $supplier_code
 * @property string $supplier_name
 * @property string $sku
 * @property string $name
 * @property string $buyer
 * @property string $qc_status
 * @property string $handle_type
 * @property string $price
 * @property string $qty
 * @property string $delivery_qty
 * @property string $presented_qty
 * @property string $check_qty
 * @property string $good_products_qty
 * @property string $bad_products_qty
 * @property integer $check_type
 * @property string $note
 * @property string $created_at
 * @property string $creator
 * @property string $time_handle
 * @property string $handler
 * @property string $time_audit
 * @property string $auditor
 * @property string $note_audit
 */
class PurchaseQc extends BaseModel
{
    public $total_qty;
    public $total_delivery_qty;
    public $total_presented_qty;
    public $total_check_qty;
    public $total_good_products_qty;
    public $total_bad_products_qty;
    public $total_refund_amount;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_qc';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['express_no', 'pur_number', 'warehouse_code', 'supplier_code', 'supplier_name', 'sku', 'name', 'buyer', 'qty', 'delivery_qty', 'presented_qty', 'check_qty', 'good_products_qty', 'bad_products_qty', 'check_type', 'created_at', 'creator'], 'required'],
            [['qc_status', 'handle_type'], 'string'],
            [['price'], 'number'],
            [['qty', 'delivery_qty', 'presented_qty', 'check_qty', 'good_products_qty', 'bad_products_qty', 'check_type'], 'integer'],
            [['created_at', 'time_handle', 'time_audit'], 'safe'],
            [['express_no', 'warehouse_code', 'supplier_code', 'supplier_name'], 'string', 'max' => 30],
            [['pur_number', 'sku', 'buyer', 'creator', 'handler', 'auditor'], 'string', 'max' => 20],
            [['name', 'note', 'note_audit'], 'string', 'max' => 300],
            [['express_no', 'pur_number', 'sku'], 'unique', 'targetAttribute' => ['express_no', 'pur_number', 'sku'], 'message' => 'The combination of Express No, Pur Number and Sku has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'express_no' => '快递单号',
            'pur_number' => '采购单号',
            'warehouse_code' => '仓库编码',
            'supplier_code' => '供应商编码',
            'supplier_name' => '供应商',
            'sku' => 'Sku',
            'name' => '产品名称',
            'buyer' => '采购员',
            'qc_status' => 'QC状态',
            'handle_type' => '处理方式',
            'price' => '单价',
            'total_qty' => '预期数量',
            'total_delivery_qty' => '到货数量',
            'total_presented_qty' => '赠送数量',
            'total_check_qty' => '检查数量',
            'total_good_products_qty' => '良品数量',
            'total_bad_products_qty' => '不良品数量',
            'check_type' => '品检方式',
            'note' => '备注',
            'created_at' => '创建时间',
            'creator' => '创建人',
            'time_handle' => '处理时间',
            'handler' => '处理人',
            'time_audit' => '审核时间',
            'auditor' => '审核人',
            'note_audit' => '审核备注',
        ];
    }
}
