<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_bank_info".
 *
 * @property integer $id
 * @property string $bank_union_code
 * @property string $bank_code
 * @property string $master_bank_name
 * @property string $city_code
 * @property string $city_name
 * @property string $branch_bank_name
 */
class BankInfo extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_bank_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_union_code', 'bank_code', 'master_bank_name', 'city_code', 'city_name', 'branch_bank_name'], 'required'],
            [['bank_union_code', 'bank_code', 'master_bank_name', 'city_code', 'city_name', 'branch_bank_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_union_code' => 'Bank Union Code',
            'bank_code' => 'Bank Code',
            'master_bank_name' => 'Master Bank Name',
            'city_code' => 'City Code',
            'city_name' => 'City Name',
            'branch_bank_name' => 'Branch Bank Name',
        ];
    }
}
