<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_product_source_status".
 *
 * @property integer $id
 * @property string $sku
 * @property integer $sourcing_status
 * @property string $create_time
 * @property string $create_user_name
 * @property string $update_time
 * @property string $update_user_name
 * @property integer $status
 * @property integer $is_push_to_erp
 */
class ProductSourceStatus extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_product_source_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'create_time', 'create_user_name'], 'required'],
            [['sourcing_status', 'status', 'is_push_to_erp'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['sku', 'create_user_name', 'update_user_name'], 'string', 'max' => 150],
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
            'sourcing_status' => 'Sourcing Status',
            'create_time' => 'Create Time',
            'create_user_name' => 'Create User Name',
            'update_time' => 'Update Time',
            'update_user_name' => 'Update User Name',
            'status' => 'Status',
            'is_push_to_erp' => 'Is Push To Erp',
        ];
    }
}
