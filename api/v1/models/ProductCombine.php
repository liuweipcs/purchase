<?php

namespace app\api\v1\models;

use Yii;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "{{%product_combine}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $product_qty
 * @property integer $product_combine_id
 * @property string $sku
 * @property string $product_combine_sku
 */
class ProductCombine extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_combine}}';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                    \yii\db\ActiveRecord::EVENT_BEFORE_UPDATE => ['update_time'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => date('Y-m-d H:i:s',time()),
            ],
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
            $model= self::find()->where(['combine_id'=>$v['combine_id']])->one();

            if ($model)
            {
                self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['combine_id'];
                $data['failure_list'][]         = '';
            } else {
                $model =new self;
                self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['combine_id'];
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

        $model->product_id          = $datass['product_id'];
        $model->product_qty         = $datass['product_qty'];
        $model->product_combine_id  = $datass['product_combine_id'];
        $model->sku                 = $datass['sku'];
        $model->product_combine_sku = $datass['product_combine_sku'];
        $model->combine_id          = $datass['combine_id'];
        $status                     = $model->save();

        return $status;
    }
}
