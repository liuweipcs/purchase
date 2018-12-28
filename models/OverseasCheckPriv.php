<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_overseas_demand_rule".
 *
 * @property integer $id
 * @property string $min_money
 * @property string $max_money
 * @property integer $status
 * @property integer $create_user_id
 * @property string $create_time
 * @property string $min_money_limit
 * @property integer $transport
 * @property integer $type
 * @property integer $supplier_invoice
 */
class OverseasCheckPriv extends BaseModel
{
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
        ];
    }
    
    
    public static function getOverseasCheckPirce($check_type)
    {
        $_overseas_check_price = [];
        if (!isset($_overseas_check_price[$check_type])) {
            $_overseas_check_price[$check_type] = OverseasCheckPriv::find()->where(['id'=>$check_type])->select('price')->scalar();
        }
        return $_overseas_check_price[$check_type];
    }
}
