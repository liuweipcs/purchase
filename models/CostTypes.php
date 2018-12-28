<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%cost_types}}".
 *
 * @property integer $id
 * @property string $cost_code
 * @property string $cost_en
 * @property string $cost_cn
 * @property string $notice
 * @property integer $create_id
 * @property integer $update_id
 * @property string $create_time
 * @property string $update_time
 */
class CostTypes extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cost_types}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cost_code', 'cost_en','cost_cn',], 'required'],
            [['create_id', 'update_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['cost_code'], 'string', 'max' => 20],
            [['cost_en', 'cost_cn'], 'string', 'max' => 30],
            [['notice'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cost_code' => Yii::t('app', '费用代码'),
            'cost_en' => Yii::t('app', '费用中文名'),
            'cost_cn' => Yii::t('app', '费用英文名'),
            'notice' => Yii::t('app', '备注'),
            'create_id' => Yii::t('app', '创建人'),
            'update_id' => Yii::t('app', '更新人'),
            'create_time' => Yii::t('app', '创建时间'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
