<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "pur_sample_stock_log".
 *
 * @property integer $id
 * @property string $sku
 * @property string $on_way_change
 * @property string $available_change
 * @property string $change_user_name
 * @property integer $change_user_id
 * @property string $change_pur_number
 * @property integer $type
 */
class SampleStockLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_sample_stock_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'required'],
            [['change_user_id', 'type'], 'integer'],
            [['sku'], 'string', 'max' => 100],
            [['on_way_change', 'available_change', 'change_user_name', 'change_pur_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'Sku',
            'on_way_change' => 'On Way Change',
            'available_change' => 'Available Change',
            'change_user_name' => 'Change User Name',
            'change_user_id' => 'Change User ID',
            'change_pur_number' => 'Change Pur Number',
            'type' => 'Type',
        ];
    }

    public static function saveStockLog($sku,$onWay=0,$available=0,$pur_number,$type,$warehouseCode,$stock=0){
        return Yii::$app->db->createCommand()->insert(self::tableName(),[
                'sku'=>$sku,
                'on_way_change'=>$onWay,
                'available_change'=>$available,
                'change_user_name'=>Yii::$app->user->identity->username,
                'change_user_id'=>Yii::$app->user->id,
                'change_pur_number'=>$pur_number,
                'type'=>$type,
                'stock_change'=>$stock,
                'warehouse_code'=>$warehouseCode,
                'create_date'=>date('Y-m-d H:i:s',time())
        ])->execute();
    }
}
