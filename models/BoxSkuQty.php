<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%box_sku_qty}}".
 *
 * @property integer $id
 * @property string $sku
 * @property string $box_amount
 * @property integer $create_time
 * @property string $create_user_ip
 * @property integer $create_user_id
 * @property string $is_push
 */
class BoxSkuQty extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%box_sku_qty}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'create_user_id'], 'integer'],
            [['is_push'], 'string'],
            [['sku', 'create_user_ip'], 'string', 'max' => 64],
            [['box_amount'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'sku' => Yii::t('app', 'Sku'),
            'box_amount' => Yii::t('app', 'Box Amount'),
            'create_time' => Yii::t('app', 'Create Time'),
            'create_user_ip' => Yii::t('app', 'Create User Ip'),
            'create_user_id' => Yii::t('app', 'Create User ID'),
            'is_push' => Yii::t('app', 'Is Push'),
        ];
    }

    /**
     * 获取sku一箱的数量
     * @param $sku
     * @return false|int|null|string
     */
    public static function getBoxQty($sku){
        if($sku){
            $qty=self::find()->select('box_amount')->where(['sku'=>trim($sku)])->scalar();
        }
        return $qty ? $qty : 0;
    }
}
