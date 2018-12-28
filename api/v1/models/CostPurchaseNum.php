<?php

namespace app\api\v1\models;

use Yii;

/**
 * This is the model class for table "pur_cost_purchase_num".
 *
 * @property integer $id
 * @property integer $apply_id
 * @property string $date
 * @property integer $purchase_num
 * @property string $create_time
 * @property string $update_time
 * @property string $sku
 */
class CostPurchaseNum extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_cost_purchase_num';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'purchase_num'], 'integer'],
            [['date', 'create_time', 'update_time'], 'safe'],
            [['sku'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => 'Apply ID',
            'date' => 'Date',
            'purchase_num' => 'Purchase Num',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'sku' => 'Sku',
        ];
    }

    public static function  savePurNum($data,$date,$num){
        if(empty($date)){
            return;
        }
        $time = date('Y-m-01 00:00:00',strtotime($date['begin_time']));
        $model = self::find()->andFilterWhere(['apply_id'=>$data->id,'sku'=>$data->sku,'date'=>$time])->one();
        if(empty($model)){
            $model = new self();
        }
        $model->apply_id = $data->id;
        $model->date = $time;
        $model->purchase_num = $num;
        $model->sku = $data->sku;
        $model->status = 1;
        if($model->isNewRecord){
            $model->create_time = date('Y-m-d H:i:s',time());
        }else{
            $model->update_time = date('Y-m-d H:i:s',time());
        }
        $model->save(false);
    }
}
