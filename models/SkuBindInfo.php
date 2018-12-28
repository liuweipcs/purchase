<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_sku_bind_info".
 *
 * @property integer $id
 * @property string $child_sku
 * @property string $father_sku
 * @property string $update_time
 */
class SkuBindInfo extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sku_bind_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['update_time'], 'required'],
            [['update_time'], 'safe'],
            [['child_sku', 'father_sku'], 'string', 'max' => 150],
            [['child_sku'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'child_sku' => 'Child Sku',
            'father_sku' => 'Father Sku',
            'update_time' => 'Update Time',
        ];
    }
}
