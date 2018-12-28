<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_inland_avg_delivery_log".
 *
 * @property integer $id
 * @property string $sku
 * @property string $pur_number
 * @property string $audit_time
 * @property string $instock_time
 * @property string $create_time
 * @property integer $is_calc
 */
class InlandAvgDeliveryLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_inland_avg_delivery_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'pur_number', 'audit_time', 'instock_time', 'create_time'], 'required'],
            [['audit_time', 'instock_time', 'create_time'], 'safe'],
            [['is_calc'], 'integer'],
            [['sku', 'pur_number'], 'string', 'max' => 150],
            [['sku', 'pur_number'], 'unique', 'targetAttribute' => ['sku', 'pur_number'], 'message' => 'The combination of Sku and Pur Number has already been taken.'],
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
            'audit_time' => 'Audit Time',
            'instock_time' => 'Instock Time',
            'create_time' => 'Create Time',
            'is_calc' => 'Is Calc',
        ];
    }
}
