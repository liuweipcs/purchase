<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%product_category}}".
 *
 * @property integer $id
 * @property integer $category_parent_id
 * @property string $category_cn_name
 * @property string $category_en_name
 * @property string $category_code
 * @property string $category_description
 * @property integer $category_order
 * @property integer $category_level
 * @property integer $category_status
 * @property integer $modify_user_id
 * @property string $modify_time
 * @property string $create_time
 * @property integer $code_increase_num
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_category}}';
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
            $model= self::find()->where(['id'=>$v['category_id']])->one();

            if ($model)
            {
                self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['id'];
                $data['failure_list'][]         = '';
            } else {
                $model =new self;
                self::SaveOne($model,$v);
                $data['success_list'][]         = $model->attributes['id'];
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

        $model->id                      = $datass['category_id'];
        $model->category_parent_id      = $datass['category_parent_id'];
        $model->category_cn_name        = $datass['category_cn_name'];
        $model->category_en_name        = $datass['category_en_name'];
        $model->category_code           = $datass['category_code'];
        $model->category_description    = $datass['category_description'];
        $model->category_order          = $datass['category_order'];
        $model->category_level          = $datass['category_level'];
        $model->category_status         = $datass['category_status'];
        $model->modify_user_id          = $datass['modify_user_id'];
        $model->modify_time             = $datass['modify_time'];
        $model->create_time             = $datass['create_time'];
        $model->code_increase_num       = $datass['code_increase_num'];
        $status =$model->save();

        return $status;
    }
}
