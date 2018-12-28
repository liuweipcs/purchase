<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_freight_update".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $avg_freight
 */
class FreightUpdate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_freight_update';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number', 'avg_freight'], 'required'],
            [['avg_freight'], 'number'],
            [['pur_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'avg_freight' => 'Avg Freight',
        ];
    }
}
