<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_receipt_log}}".
 *
 * @property integer $id
 * @property string $content
 * @property integer $source_state
 * @property integer $target_state
 * @property integer $operator
 * @property string $create_time
 * @property integer $pay_id
 */
class PurchaseOrderReceiptLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_receipt_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source_state', 'target_state', 'operator', 'pay_id'], 'integer'],
            [['create_time'], 'safe'],
            [['content'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content' => Yii::t('app', '内容'),
            'source_state' => Yii::t('app', '源状态'),
            'target_state' => Yii::t('app', '目标状态'),
            'operator' => Yii::t('app', '操作人'),
            'create_time' => Yii::t('app', '创建时间'),
            'pay_id' => Yii::t('app', '支付ID'),
        ];
    }
}
