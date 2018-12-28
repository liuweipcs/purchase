<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_bank_city_info".
 *
 * @property integer $id
 * @property string $prov_code
 * @property string $prov_name
 * @property string $city_code
 * @property string $city_name
 */
class BankCityInfo extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_bank_city_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prov_code', 'prov_name', 'city_code', 'city_name'], 'required'],
            [['prov_code', 'prov_name', 'city_code', 'city_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prov_code' => 'Prov Code',
            'prov_name' => 'Prov Name',
            'city_code' => 'City Code',
            'city_name' => 'City Name',
        ];
    }
}
