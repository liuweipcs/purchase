<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_refunds}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $refunds_amount
 * @property string $create_time
 * @property integer $create_id
 */
class PurchaseRefunds extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_refunds}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['refunds_amount'], 'required'],
            [['id', 'create_id'], 'integer'],
            [['refunds_amount'], 'number'],
            [['create_time'], 'safe'],
            [['pur_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pur_number' => Yii::t('app', '采购单号'),
            'refunds_amount' => Yii::t('app', 'Refunds Amount'),
            'create_time' => Yii::t('app', '创建时间'),
            'create_id' => Yii::t('app', '创建人'),
        ];
    }

    /**
     * 保存一条数据
     * @param $data
     */
    public static function  SaveOne($data)
    {
        $model                 = new self;
        $model->pur_number     = $data['pur_number'];
        $model->refunds_amount = $data['refunds_amount'];
        $model->create_time    = date('Y-m-d H:i:s');
        $model->create_id      = Yii::$app->user->id;
        $model->save(false);
    }

    /**
     * 获取退款金额
     */
    public static function getRefundsAmount($pur_number)
    {
        $model = self::find()->where(['pur_number'=>$pur_number])->one();
        if (!empty($model)) {
            return $model->refunds_amount;
        } else {
            return false;
        }
    }
}
