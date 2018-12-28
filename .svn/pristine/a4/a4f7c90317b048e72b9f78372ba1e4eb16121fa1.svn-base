<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%product_description_copy}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $language_code
 * @property string $title
 * @property integer $create_user_id
 * @property string $create_time
 */
class ProductDescriptionCopy extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_description_copy}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku', 'language_code'], 'required'],
            [['create_user_id'], 'integer'],
            [['create_time'], 'safe'],
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
            'sku' => Yii::t('app', '产品sku'),
            'language_code' => Yii::t('app', '所属语言code'),
            'title' => Yii::t('app', '标题'),
            'create_user_id' => Yii::t('app', '创建人'),
            'create_time' => Yii::t('app', '创建时间'),
        ];
    }
}
