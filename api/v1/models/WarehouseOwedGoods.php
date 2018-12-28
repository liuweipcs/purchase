<?php

namespace app\api\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "{{%warehouse_owed_goods}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $warehouse_code
 * @property integer $quantity_goods
 * @property string $name
 * @property string $order_pay_time
 * @property integer $create_id
 * @property string $create_time
 * @property integer $update_id
 * @property string $update_time
 * @property string $platform_code
 * @property integer $platform_order_id
 */
class WarehouseOwedGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%warehouse_owed_goods}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    //\yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],

        ];
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sku', 'warehouse_code', 'quantity_goods', 'name',  'create_time', 'platform_code'], 'required'],
            [['id', 'quantity_goods',  'platform_order_id'], 'integer'],
            [['order_pay_time', 'create_time', 'update_time'], 'safe'],
            [['sku', 'warehouse_code'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 200],
            [['platform_code'], 'string', 'max' => 100],
        ];
    }

    /**
     *
     * @param mixed $datass
     * @return array|null|\yii\db\ActiveRecord
     */
    public static function FindOnes($datass)
    {
        foreach ($datass as $v)
        {
            $model= self::find()->where(['wms_id'=>$v['wms_id']])->one();

            if ($model)
            {
                self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['wms_id'];
                $data['failure_list'][]         = '';
            } else {
                $model =new self;
                self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['wms_id'];
                $data['failure_list'][]         = '';
            }
        }

        return $data;


    }
    /**
     * 新增数据
     * @param $model
     * @param $datass
     * @return mixed
     */
    public  static function SaveOne($model,$datass)
    {

        $model->sku               = $datass['sku'];
        $model->wms_id            = $datass['wms_id'];
        $model->warehouse_code    = $datass['warehouse_code'];
        $model->quantity_goods    = $datass['lack_qty'];
        $model->name              = $datass['name'];
        $model->order_pay_time    = $datass['paytime'];
        $model->order_id          = $datass['order_id'];
        $model->platform_code     = $datass['platform_code'];
        $model->platform_order_id = $datass['platform_order_id'];
        $model->is_purchase       = 0;
        $status                   = $model->save(false);

        return $status;
    }
}
