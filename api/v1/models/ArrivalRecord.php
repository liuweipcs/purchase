<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "{{%arrival_record}}".
 *
 * @property string $id
 * @property string $purchase_order_no
 * @property string $sku
 * @property string $name
 * @property integer $delivery_qty
 * @property integer $time_receipt
 * @property string $receiver
 * @property integer $cdate
 * @property string $express_no
 * @property string $check_type
 * @property integer $bad_products_qty
 * @property integer $check_time
 * @property string $check_user
 * @property integer $qc_id
 * @property string $note
 */
class ArrivalRecord extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%arrival_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['purchase_order_no', 'sku', 'name', 'delivery_qty', 'delivery_time', 'delivery_user', 'qc_id'], 'required'],
            [['delivery_qty', 'cdate', 'bad_products_qty'], 'integer'],
            [['purchase_order_no'], 'string', 'max' => 100],
            [['sku', 'delivery_user','check_type', 'check_user'], 'string', 'max' => 50],
            [['name', 'note', 'qc_id'], 'string', 'max' => 200],
            [['express_no'], 'string', 'max' => 255],
            [['delivery_time', 'check_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'purchase_order_no' => Yii::t('app', 'Purchase Order No'),
            'sku' => Yii::t('app', 'Sku'),
            'name' => Yii::t('app', 'Name'),
            'delivery_qty' => Yii::t('app', 'Delivery Qty'),
            'delivery_time' => Yii::t('app', 'Time Receipt'),
            'delivery_user' => Yii::t('app', 'Receiver'),
            'cdate' => Yii::t('app', 'Cdate'),
            'express_no' => Yii::t('app', 'Express No'),
            'check_type' => Yii::t('app', 'Check Type'),
            'bad_products_qty' => Yii::t('app', 'Bad Products Qty'),
            'check_time' => Yii::t('app', 'Check Time'),
            'check_user' => Yii::t('app', 'Check User'),
            'qc_id' => Yii::t('app', 'Qc ID'),
            'note' => Yii::t('app', 'Note'),
        ];
    }
}
