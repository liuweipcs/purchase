<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%sku_tota_page}}".
 *
 * @property integer $id
 * @property integer $total
 * @property integer $num
 */
class SkuTotaPage extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sku_tota_page}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['total', 'num'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'total' => Yii::t('app', 'Total'),
            'num' => Yii::t('app', 'Num'),
        ];
    }
}
