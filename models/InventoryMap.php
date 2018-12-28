<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%inventory_map}}".
 *
 * @property integer $id
 * @property string $tableName
 * @property string $modelClassName
 * @property string $comment
 */
class InventoryMap extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%inventory_map}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tableName'], 'string', 'max' => 50],
            [['modelClassName'], 'string', 'max' => 30],
            [['comment'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tableName' => Yii::t('app', '表名'),
            'modelClassName' => Yii::t('app', '模型类名'),
            'comment' => Yii::t('app', '备注'),
        ];
    }
}
