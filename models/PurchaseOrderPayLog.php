<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_pay_log}}".
 *
 * @property integer $id
 * @property string $content
 * @property integer $source_state
 * @property integer $target_state
 * @property integer $operator
 * @property string $create_time
 * @property integer $pay_id
 *
 * @property PurchaseOrderPay $pay
 */
class PurchaseOrderPayLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_pay_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'source_state', 'target_state', 'operator', 'pay_id'], 'integer'],
            [['create_time'], 'safe'],
            [['content'], 'string', 'max' => 100],
            [['pay_id'], 'exist', 'skipOnError' => true, 'targetClass' => PurchaseOrderPay::className(), 'targetAttribute' => ['pay_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'content' => Yii::t('app', 'Content'),
            'source_state' => Yii::t('app', 'Source State'),
            'target_state' => Yii::t('app', 'Target State'),
            'operator' => Yii::t('app', 'Operator'),
            'create_time' => Yii::t('app', 'Create Time'),
            'pay_id' => Yii::t('app', 'Pay ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPay()
    {
        return $this->hasOne(PurchaseOrderPay::className(), ['id' => 'pay_id']);
    }

}
