<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_ali_order_logistics_info".
 *
 * @property integer $id
 * @property string $order_number
 * @property string $pur_number
 * @property string $address
 * @property string $area
 * @property string $area_code
 * @property string $city
 * @property string $contact_person
 * @property string $fax
 * @property string $mobile
 * @property string $province
 * @property string $telephone
 * @property string $zip
 * @property string $town_code
 * @property string $town
 */
class AliOrderLogisticsInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ali_order_logistics_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_number', 'pur_number'], 'required'],
            [['address'], 'string'],
            [['order_number', 'town'], 'string', 'max' => 200],
            [['pur_number', 'area', 'area_code', 'city', 'mobile'], 'string', 'max' => 50],
            [['contact_person', 'province', 'telephone', 'zip', 'town_code'], 'string', 'max' => 100],
            [['fax'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_number' => 'Order Number',
            'pur_number' => 'Pur Number',
            'address' => 'Address',
            'area' => 'Area',
            'area_code' => 'Area Code',
            'city' => 'City',
            'contact_person' => 'Contact Person',
            'fax' => 'Fax',
            'mobile' => 'Mobile',
            'province' => 'Province',
            'telephone' => 'Telephone',
            'zip' => 'Zip',
            'town_code' => 'Town Code',
            'town' => 'Town',
        ];
    }

    public static function  saveData($pur_number,$order_number,$data){
        $model = self::find()->where(['pur_number'=>$pur_number,'order_number'=>$order_number])->one();
        if(empty($model)){
            $model=new self();
        }
        $model->order_number  = $order_number;
        $model->pur_number    = $pur_number;
        $model->address       = isset($data['address']) ? $data['address'] : '';
        $model->area          = isset($data['area']) ? $data['area'] : '';
        $model->city          = isset($data['city']) ? $data['city'] : '';
        $model->area_code     = isset($data['areaCode']) ? $data['areaCode'] : '';
        $model->contact_person= isset($data['contactPerson']) ? $data['contactPerson'] : '';
        $model->fax           = isset($data['fax']) ? $data['fax'] : '';
        $model->mobile        = isset($data['mobile']) ? $data['mobile'] : '';
        $model->province      = isset($data['province']) ? $data['province'] : '';
        $model->telephone     = isset($data['telephone']) ? $data['telephone'] : '';
        $model->zip           = isset($data['zip']) ? $data['zip'] : '';
        $model->town_code     = isset($data['townCode']) ? $data['townCode'] : '';
        $model->town          = isset($data['town']) ? $data['town'] : '';
        if(isset($data['logisticsItems'])&&!empty($data['logisticsItems'])){
            AliOrderLogisticsItems::saveData($pur_number,$order_number,$data['logisticsItems']);
        }
        return $model->save();
    }
}
