<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_sample_rule".
 *
 * @property integer $id
 * @property integer $min_num
 * @property integer $max_num
 * @property string $quality_random
 * @property string $sample_code
 * @property integer $type
 * @property integer $status
 */
class SampleRule extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sample_rule';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['min_num', 'max_num', 'type', 'status'], 'integer'],
            [['quality_random'], 'string', 'max' => 30],
            [['sample_code'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'min_num' => 'Min Num',
            'max_num' => 'Max Num',
            'quality_random' => 'Quality Random',
            'sample_code' => 'Sample Code',
            'type' => 'Type',
            'status' => 'Status',
        ];
    }
}
