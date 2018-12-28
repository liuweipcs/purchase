<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_sample_code".
 *
 * @property integer $id
 * @property string $sample_code
 * @property integer $sample_num
 * @property integer $type
 * @property integer $status
 */
class SampleCode extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sample_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sample_num', 'type', 'status'], 'integer'],
            [['sample_code'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sample_code' => 'Sample Code',
            'sample_num' => 'Sample Num',
            'type' => 'Type',
            'status' => 'Status',
        ];
    }
}
