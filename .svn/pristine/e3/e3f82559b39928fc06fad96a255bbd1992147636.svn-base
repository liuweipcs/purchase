<?php

namespace app\models;

use app\models\base\BaseModel;

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
class ArrivalRecord extends BaseModel
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
            [['delivery_qty', 'cdate', 'bad_products_qty', 'qc_id'], 'integer'],
            [['purchase_order_no'], 'string', 'max' => 100],
            [['sku', 'delivery_user', 'express_no', 'check_type', 'check_user'], 'string', 'max' => 50],
            [['name', 'note'], 'string', 'max' => 200],
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
    /**获取收货时间
     * @param $pur_number
     * @param $sku
     */
    public static function getDeliveryTime($pur_number,$sku,$type='<br/>')
    {
        $info = self::find()->where(['purchase_order_no'=>$pur_number])
            ->select('delivery_time')
            ->andWhere(['sku'=>$sku])
            ->asArray()
            ->all();

        $data = '';
        if (!empty($info)) {
            foreach ($info as $k) {
                $data .= $k['delivery_time'] . $type;
            }
        }
        return $data;
    }
    /**
     * 获取收货信息
     */
    public static function getArrivalInfo($pur_number,$sku)
    {
        $info = self::find()
            ->where(['purchase_order_no'=>$pur_number, 'sku'=>$sku])
            ->asArray()->all();
        return $info ? : [];
    }
}
