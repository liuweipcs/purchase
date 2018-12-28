<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_purchase_warning_status".
 *
 * @property integer $id
 * @property string $sku
 * @property string $pur_number
 * @property integer $warn_status
 */
class PurchaseWarningStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_warning_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'pur_number'], 'required'],
            [['warn_status'], 'integer'],
            [['sku'], 'string', 'max' => 50],
            [['pur_number'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'pur_number' => 'Pur Number',
            'warn_status' => 'Warn Status',
        ];
    }
}
