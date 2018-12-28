<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_supplier_deliver_adress".
 *
 * @property integer $id
 * @property string $supplier_code
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $adress
 * @property integer $is_visible
 * @property integer $is_check
 * @property string $change_reason
 * @property string $pur_number
 * @property string $order_number
 * @property string $create_time
 * @property integer $items_id
 */
class SupplierDeliverAdress extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_supplier_deliver_adress';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_visible', 'is_check', 'items_id'], 'integer'],
            [['create_time'], 'safe'],
            [['supplier_code', 'province', 'city', 'area', 'order_number'], 'string', 'max' => 100],
            [['adress', 'change_reason'], 'string', 'max' => 255],
            [['pur_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supplier_code' => 'Supplier Code',
            'province' => 'Province',
            'city' => 'City',
            'area' => 'Area',
            'adress' => 'Adress',
            'is_visible' => 'Is Visible',
            'is_check' => 'Is Check',
            'change_reason' => 'Change Reason',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
            'create_time' => 'Create Time',
            'items_id' => 'Items ID',
        ];
    }

    public static function saveDatas($datas){
        foreach ($datas as $data){
            $supplierAdressExist = self::find()
                ->where(['supplier_code'=>$data['supplier_code'],
                        'province'=>$data['from_province'],
                        'city'=>$data['from_city'],
                        'area'=>$data['from_area'],
                        'adress'=>$data['from_address']
                        ])
                ->one();
            if($supplierAdressExist){
                $supplierAdressExist->pur_number    = $data['pur_number'];
                $supplierAdressExist->order_number  = $data['order_number'];
                $supplierAdressExist->create_time   = date('Y-m-d H:i:s',time());
                $supplierAdressExist->items_id      = $data['id'];
                $supplierAdressExist->save();
            }else{
                $model = new self();
                $model->supplier_code   = $data['supplier_code'];
                $model->province        = $data['from_province'];
                $model->city            = $data['from_city'];
                $model->area            = $data['from_area'];
                $model->adress          = $data['from_address'];
                $model->pur_number      = $data['pur_number'];
                $model->order_number    = $data['order_number'];
                $model->create_time     = date('Y-m-d H:i:s',time());
                $model->items_id        = $data['id'];
                $model->save();
            }
            $count = self::find()->where(['supplier_code'=>$data['supplier_code'],'is_check'=>0])->count('id');
            if($count>1){
                self::updateAll(['is_visible'=>1,'is_check'=>1],['supplier_code'=>$data['supplier_code'],'is_visible'=>0,'is_check'=>0]);
            }
            AliOrderLogisticsItems::updateAll(['is_check'=>1],['id'=>$data['id']]);
        }
    }
}
