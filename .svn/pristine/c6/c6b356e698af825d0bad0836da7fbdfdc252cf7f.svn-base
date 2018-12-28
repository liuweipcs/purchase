<?php

namespace app\models;

use app\models\base\BaseModel;

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
class ProductDescription extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_description}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'create_user_id',], 'integer'],
            [['sku', 'language_code'], 'required'],
            [['create_time', 'modify_time','is_p'], 'safe'],
            [['sku'], 'string', 'max' => 50],
            [['language_code'], 'string', 'max' => 100],
            [['title'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'sku' => Yii::t('app', 'SKU'),
            'language_code' => Yii::t('app', 'Language Code'),
            'title' => Yii::t('app', '产品名称'),
            'customs_name' => Yii::t('app', 'Customs Name'),
            'amazon_keyword1' => Yii::t('app', 'Amazon Keyword1'),
            'amazon_keyword2' => Yii::t('app', 'Amazon Keyword2'),
            'amazon_keyword3' => Yii::t('app', 'Amazon Keyword3'),
            'amazon_keyword4' => Yii::t('app', 'Amazon Keyword4'),
            'amazon_keyword5' => Yii::t('app', 'Amazon Keyword5'),
            'picking_name' => Yii::t('app', 'Picking Name'),
            'description' => Yii::t('app', 'Description'),
            'category_note' => Yii::t('app', 'Category Note'),
            'included' => Yii::t('app', 'Included'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'create_time' => Yii::t('app', 'Create Time'),
            'modify_user_id' => Yii::t('app', 'Modify User ID'),
            'modify_time' => Yii::t('app', 'Modify Time'),
        ];
    }


    public static function  getFiled($sku,$filed='*')
    {
        return self::find()->select($filed)->where(['sku'=>$sku])->scalar();
    }
}
