<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%purchase_order_orders}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $order_number
 * @property integer $is_request
 * @property integer $create_id
 */
class PurchaseOrderOrders extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%purchase_order_orders}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_number'], 'required'],
            [['is_request', 'create_id'], 'integer'],
            [['pur_number'], 'string', 'max' => 100],
            [['order_number'], 'string', 'max' => 200],
            [['order_number'], 'trim'],
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
            'order_number' => Yii::t('app', '订单号'),
            'is_request' => Yii::t('app', '是否已请求过1688(0没有1已请求)'),
            'create_id' => Yii::t('app', '当前账户'),
        ];
    }
    /**
     * 更新备注
     * @param $data
     * @return bool
     */
    public function saveOrders($data)
    {
        foreach($data as $v)
        {
            $pur = self::findOne(['pur_number'=>$v['pur_number']]);
            if($pur)
            {
                if(!empty($v['order_number']) && $pur->order_number != $v['order_number']){
                    $pur->e_order_number= $v['order_number'];
                }
                $pur->order_number = trim($v['order_number']);
                $status    = $pur->save();
            } else{
                $status=$this->saveOrder($v);
            }
        }

        return $status;
    }
    /**
     * 增加备注
     * @param $data
     * @return bool
     */
    public function saveOrder($data)
    {
        $model               = new self;
        $model->pur_number   = $data['pur_number'];
        $model->order_number = trim($data['order_number']);
        $model->create_id    = Yii::$app->user->id;
        $model->is_request   = $data['is_request']==1?1:0;

        if(!empty($data['order_number'])){
            $model->e_order_number= $data['order_number'];
        }

        $status            = $model->save();
        return $status;
    }
}
