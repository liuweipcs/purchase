<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_data_control_config".
 *
 * @property string $id
 * @property string $type
 * @property string $values
 * @property string $remark
 */
class DataControlConfig extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_data_control_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'values'], 'required'],
            [['type'], 'string', 'max' => 100],
            [['values', 'remark'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'values' => 'Values',
            'remark' => 'Remark',
        ];
    }
}
