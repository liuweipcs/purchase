<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_ali_order_contact".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $order_number
 * @property string $phone
 * @property string $fax
 * @property string $email
 * @property string $im_in_platform
 * @property string $name
 * @property string $mobile
 * @property string $company_name
 * @property integer $type
 */
class AliOrderContact extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_ali_order_contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type'], 'integer'],
            [['pur_number', 'order_number'], 'string', 'max' => 50],
            [['phone', 'fax', 'email', 'im_in_platform', 'name', 'mobile', 'company_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'order_number' => 'Order Number',
            'phone' => 'Phone',
            'fax' => 'Fax',
            'email' => 'Email',
            'im_in_platform' => 'Im In Platform',
            'name' => 'Name',
            'mobile' => 'Mobile',
            'company_name' => 'Company Name',
            'type' => 'Type',
        ];
    }

    public static function saveData($pur_number,$order_number,$data,$type){
        $model = self::find()->where(['pur_number'=>$pur_number,'order_number'=>$order_number,'type'=>$type])->one();
        if(empty($model)){
            $model = new  self();
        }
        $model->pur_number      = $pur_number;
        $model->order_number    = $order_number;
        $model->phone           = isset($data['phone']) ? $data['phone'] : '';
        $model->fax             = isset($data['fax'])   ? $data['fax'] : '';
        $model->email           = isset($data['email']) ? $data['email'] : '';
        $model->im_in_platform  = isset($data['imInPlatform']) ? $data['imInPlatform'] : '';
        $model->name            = isset($data['name'])   ? $data['name'] : '';
        $model->mobile          = isset($data['mobile']) ? $data['mobile'] : '';
        $model->company_name    = isset($data['companyName']) ? $data['companyName'] : '';
        $model->type    =$type;
        $model->save();
    }
}
