<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_master_bank_info".
 *
 * @property integer $id
 * @property string $master_bank_name
 * @property string $bank_code
 */
class MasterBankInfo extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_master_bank_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['master_bank_name', 'bank_code'], 'required'],
            [['master_bank_name', 'bank_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'master_bank_name' => 'Master Bank Name',
            'bank_code' => 'Bank Code',
        ];
    }
}
