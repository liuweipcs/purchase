<?php

namespace app\modules\manage\models;

use Yii;

/**
 * This is the model class for table "pur_supplier_quotes_manage_items".
 *
 * @property integer $id
 * @property integer $supplier_quotes_id
 * @property integer $amount_min
 * @property integer $amount_max
 * @property string $supplier_price
 * @property integer $status
 */
class SupplierQuotesManageItems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_quotes_manage_items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['supplier_quotes_id'], 'required'],
            [['supplier_quotes_id', 'amount_min', 'amount_max', 'status'], 'integer'],
            [['supplier_price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_quotes_id' => 'Supplier Quotes ID',
            'amount_min' => 'Amount Min',
            'amount_max' => 'Amount Max',
            'supplier_price' => 'Supplier Price',
            'status' => 'Status',
        ];
    }
}
