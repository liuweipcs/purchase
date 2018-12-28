<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%product_description}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $sku
 * @property string $language_code
 * @property string $title
 * @property string $customs_name
 * @property string $amazon_keyword1
 * @property string $amazon_keyword2
 * @property string $amazon_keyword3
 * @property string $amazon_keyword4
 * @property string $amazon_keyword5
 * @property string $picking_name
 * @property string $description
 * @property string $category_note
 * @property string $included
 * @property integer $create_user_id
 * @property string $create_time
 * @property integer $modify_user_id
 * @property string $modify_time
 */
class ProductDescription extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_description}}';
    }

    /**
     * 保存一条数据
     * @param $data
     * @return bool
     */
    public static  function  SaveOne($data)
    {

        //Vhelper::dump($data);
        foreach ($data as $v)
        {
            if(isset($v['sku']))
            {
                $models = self::find()->where(['sku'=>$v['sku']])->one();
                //Vhelper::dump($model);
                if (!empty($models)) {

                    //$model->sku = $v['sku'];
                    $models->language_code  = $v['language_code'];
                    $models->title          = $v['title'];
                    $models->create_user_id = $v['create_user_id'];
                    $models->create_time    = !empty($v['create_time']) ? $v['create_time'] : date('Y-m-d H:i:s');
                    return $models->save(false);


                } else {

                    $modelb                 = new self;
                    $modelb->sku            = $v['sku'];
                    $modelb->language_code  = $v['language_code'];
                    $modelb->title          = $v['title'];
                    $modelb->create_user_id = $v['create_user_id'];
                    $modelb->create_time    = !empty($v['create_time']) ? $v['create_time'] : date('Y-m-d H:i:s');
                    return $modelb->save(false);
                }
            }
        }

    }

}
