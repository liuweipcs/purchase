<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%region}}".
 *
 * @property integer $id
 * @property integer $pid
 * @property string $region_name
 * @property integer $region_type
 * @property string $region_code
 * @property integer $region_belong_country
 * @property string $post_code
 * @property string $modify_time
 */
class Region extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%region}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'region_type', 'region_belong_country'], 'integer'],
            [['region_code', 'region_belong_country', 'post_code'], 'required'],
            [['modify_time'], 'safe'],
            [['region_name'], 'string', 'max' => 120],
            [['region_code'], 'string', 'max' => 50],
            [['post_code'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pid' => Yii::t('app', '父id'),
            'region_name' => Yii::t('app', '地区名称'),
            'region_type' => Yii::t('app', '0,国家 1省份 2城市 3区县'),
            'region_code' => Yii::t('app', '地区编码'),
            'region_belong_country' => Yii::t('app', '地区关联国家'),
            'post_code' => Yii::t('app', '邮政编码'),
            'modify_time' => Yii::t('app', '最后修改时间'),
        ];
    }
}
