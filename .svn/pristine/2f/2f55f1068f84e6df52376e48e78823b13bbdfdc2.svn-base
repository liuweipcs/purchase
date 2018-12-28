<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_customer_service".
 *
 * @property integer $id
 * @property integer $data_id
 * @property integer $reason_id
 * @property string $platform_code
 * @property string $sku
 * @property string $data_create_time
 * @property string $reason
 * @property string $create_time
 * @property string $update_time
 */
class CustomerService extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_customer_service';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['data_id', 'reason_id', 'sku'], 'required'],
            [['data_id', 'reason_id'], 'integer'],
            [['data_create_time', 'create_time', 'update_time'], 'safe'],
            [['platform_code'], 'string', 'max' => 50],
            [['sku', 'reason'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_id' => 'Data ID',
            'reason_id' => 'Reason ID',
            'platform_code' => 'Platform Code',
            'sku' => 'Sku',
            'data_create_time' => 'Data Create Time',
            'reason' => 'Reason',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
