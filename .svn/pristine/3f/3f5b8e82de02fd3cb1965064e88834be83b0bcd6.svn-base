<?php

namespace app\models;

use app\models\base\BaseModel;

use Yii;

/**
 * This is the model class for table "{{%stock_log}}".
 *
 * @property integer $id
 * @property string $pur_number
 * @property string $sku
 * @property string $warehouse_code
 * @property integer $change_qty
 * @property integer $after_qty
 * @property string $key_id
 * @property string $message
 * @property string $operate_type
 * @property integer $w_log_id
 * @property string $operator
 * @property string $operate_time
 * @property string $create_time
 * @property integer $delivery_left_stock
 */
class StockLog extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%stock_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['change_qty', 'after_qty', 'w_log_id', 'delivery_left_stock'], 'integer'],
            [['operate_time', 'create_time'], 'safe'],
            [['pur_number', 'sku', 'warehouse_code', 'key_id', 'message', 'operate_type', 'operator'], 'string', 'max' => 255],
            [['w_log_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pur_number' => 'Pur Number',
            'sku' => 'Sku',
            'warehouse_code' => 'Warehouse Code',
            'change_qty' => 'Change Qty',
            'after_qty' => 'After Qty',
            'key_id' => 'Key ID',
            'message' => 'Message',
            'operate_type' => 'Operate Type',
            'w_log_id' => 'W Log ID',
            'operator' => 'Operator',
            'operate_time' => 'Operate Time',
            'create_time' => 'Create Time',
            'delivery_left_stock' => 'Delivery Left Stock',
        ];
    }
    /**
     * 发货详情
     */
    public static function getDeliveryInfo($pur_number,$sku)
    {
        $res = self::find()->where(['pur_number'=>$pur_number, 'sku'=>$sku, 'operate_type'=> 'pack'])->asArray()->all();
        return $res;
    }
    /**
     * 入库详情
     */
    public static function getInstockInfo($pur_number,$sku)
    {
        $data = self::find()->where(['pur_number'=>$pur_number, 'sku'=>$sku, 'operate_type'=> 'delivery'])->orderBy('id DESC')->asArray()->all();

        foreach ($data as $key => $value) {
            if( strtotime($value['stock_clear_time']) < 1420041600 ){
                $ku_age = round(( time()-strtotime($value['operate_time']) ) / 86400, 0);
            } else {
                $ku_age = round((strtotime($value['stock_clear_time']) - strtotime($value['operate_time']) ) / 86400, 0);
            }
            $data[$key]['ku_age'] = $ku_age;
        }
        return $data;
    }
    /**
     * 获取库龄信息
     */
    public function getReservoirInfo($pur_number=null, $sku=null,$status=null)
    {
        if (!empty($pur_number)) {
            $where['pur_number'] = $pur_number;
        }
        if (!empty($sku)) {
            $where['sku']= $sku;
        }
        if (!empty($status)) {
            $where['status'] = $status;
        }
        $res = self::find()->where($where)->orderBy('id DESC')->asArray()->all();
        vd($res);
        return $res;
    }
}
