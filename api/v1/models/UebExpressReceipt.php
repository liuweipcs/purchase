<?php
namespace app\api\v1\models;

class UebExpressReceipt extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'ueb_express_receipt';
    }

    public static function FindOnes($datass)
    {
        $response = [];
        foreach($datass as $k=>$v) {
            $model = self::find()->where(['pid' => $v['id']])->one();
            if($model) {
                $res = self::SaveOne($model, $v);
            } else {
                $model = new self;
                $res = self::SaveOne($model, $v);
            }
            if($res) {
                $response['success_list'][] = $v['id'];
            } else {
                $response['failure_list'][] = $v['id'];
            }
        }
        return $response;
    }

    public static function SaveOne($model, $data)
    {
        $model->pid                  = $data['id'] ? $data['id'] : '';
        $model->express_merchant_id  = $data['express_merchant_id'] ? $data['express_merchant_id'] : 0;
        $model->express_single       = $data['express_single'] ? $data['express_single'] : '';
        $model->add_time             = $data['add_time'] ? $data['add_time'] : '';
        $model->box_number           = $data['box_number'] ? $data['box_number'] : '';
        $model->pat_fee              = $data['pat_fee'] ? $data['pat_fee'] : '';
        $model->status               = $data['status'] ? $data['status'] : '';
        $model->is_urgent            = $data['is_urgent'] ? $data['is_urgent'] : '';
        $model->is_relation          = $data['is_relation'] ? $data['is_relation'] : '';
        $model->relation_order_no    = $data['relation_order_no'] ? $data['relation_order_no'] : '';
        $model->is_quality           = $data['is_quality'] ? $data['is_quality'] : '';
        $model->quality_username     = $data['quality_username'] ? $data['quality_username'] : '';
        $model->quality_time         = $data['quality_time'] ? $data['quality_time'] : '';
        $model->delete_username      = $data['delete_username'] ? $data['delete_username'] : '';
        $model->delete_time          = $data['delete_time'] ? $data['delete_time'] : '';
        $model->remarks              = $data['remarks'] ? $data['remarks'] : '';
        $model->weight              =  isset($data['weight']) ? $data['weight'] : '';

        $res = $model->save(false);

        if($res) {
            return true;
        } else {
            return false;
        }

    }

}
