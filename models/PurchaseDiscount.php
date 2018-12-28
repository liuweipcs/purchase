<?php

namespace app\models;

use app\models\base\BaseModel;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "{{%purchase_discount}}".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $buyer
 * @property string $discount_price
 * @property string $total_price
 * @property string $create_time
 * @property integer $purchase_type
 */
class PurchaseDiscount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pur_number'], 'required'],
            [['discount_price', 'total_price'], 'number'],
            [['create_time'], 'safe'],
            [['purchase_type'], 'integer'],
            [['pur_number'], 'string', 'max' => 20],
            [['buyer'], 'string', 'max' => 30],
            [['pur_number'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => '采购单',
            'buyer' => '采购员',
            'discount_price' => '优惠金额',
            'total_price' => '优惠后总金额',
            'created_time' => '创建时间',
            'purchase_type' => '采购类型',
        ];
    }
    /**
     * 增加优惠
     * @param $data
     * @return bool
     */
    public function saveDiscount($data,$purchase_type=1)
    {
        if(!empty($data))
        {
            foreach($data as $v)
            {
                $models  = self::find()->where(['pur_number'=>$v['pur_number']])->one();
                if($models)
                {
                    $models->pur_number     = $v['pur_number'];
                    $models->buyer          = $v['buyer'];
                    $models->discount_price = $v['discount_price'];
                    $models->total_price    = $v['total_price'];
                    $models->create_time    = date('Y-m-d H:i:s', time());
                    $models->purchase_type    = !empty($v['purchase_type'])? $v['purchase_type'] : $purchase_type;
                    $status                 = $models->save(false);
                } else {
                    $model                  = new self;
                    $model->pur_number     = $v['pur_number'];
                    $model->buyer          = $v['buyer'];
                    $model->discount_price = $v['discount_price'];
                    $model->total_price    = $v['total_price'];
                    $model->create_time    = date('Y-m-d H:i:s', time());
                    $model->purchase_type    = !empty($v['purchase_type'])? $v['purchase_type'] : $purchase_type;
                    $status                 = $model->save(false);
                }
            }
            return $status;
        }
    }
    /**
     * 获取优惠信息
     */
    public static function getDiscountPrice($pur_number)
    {
        $discount_info = self::find()->where(['pur_number'=>$pur_number])->asArray()->one();
        if (!empty($discount_info)) {
            return $discount_info;
        } else {
            return false;
        }
    }
}
