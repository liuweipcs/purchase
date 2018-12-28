<?php

namespace app\models;

use app\models\base\BaseModel;

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
class ProductCategory extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_parent_id', 'category_order', 'category_level', 'category_status', 'modify_user_id', 'code_increase_num'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['category_cn_name', 'category_en_name'], 'string', 'max' => 50],
            [['category_code'], 'string', 'max' => 30],
            [['category_description'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_parent_id' => Yii::t('app', 'Category Parent ID'),
            'category_cn_name' => Yii::t('app', 'Category Cn Name'),
            'category_en_name' => Yii::t('app', 'Category En Name'),
            'category_code' => Yii::t('app', 'Category Code'),
            'category_description' => Yii::t('app', 'Category Description'),
            'category_order' => Yii::t('app', 'Category Order'),
            'category_level' => Yii::t('app', 'Category Level'),
            'category_status' => Yii::t('app', 'Category Status'),
            'modify_user_id' => Yii::t('app', 'Modify User ID'),
            'modify_time' => Yii::t('app', 'Modify Time'),
            'create_time' => Yii::t('app', 'Create Time'),
            'code_increase_num' => Yii::t('app', 'Code Increase Num'),
        ];
    }

    /**
     * 所以分类
     * @return mixed
     */
    public static function getCategory(){
        $category=self::find()->all();
        foreach ($category as $k=>$v){
            $data[$v['id']]=$v['category_cn_name'];
        }
        return $data;
    }
}
