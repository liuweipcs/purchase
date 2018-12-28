<?php
namespace app\api\v1\models;

class DeclareCustoms extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'pur_declare_customs';
    }

    public static function FindOnes($data)
    {
        $res = [];
        $n = 0;
        foreach ($data as $key => $value) {
            foreach ($value['detail'] as $dk => $dv) {
                $res[$n]['id'] = $value['id'];
                $res[$n]['order_id'] = $value['order_id'];
                $res[$n]['is_clear'] = $value['is_clear'];
                $res[$n]['custom_number'] = $value['custom_number'];
                $res[$n]['clear_time'] = $value['clear_time'];
                $res[$n]['key_id'] = $dv['detail_id'];
                $res[$n]['sku'] = $dv['sku'];
                $res[$n]['pur_number'] = $dv['pur_number'];
                $res[$n]['amounts'] = $dv['amounts'];
                $res[$n]['price'] = $dv['price'];
                $res[$n]['declare_name'] = $dv['declare_name'];
                $res[$n]['declare_unit'] = $dv['declare_unit'];
                $res[$n]['declare'] = $dv['declare'];
                $n++;
            }
        }

        $response = ['success_list'=>[], 'failure_list'=>[]];

        foreach($res as $rk=>$rv) {
            //唯一标识
            $model = self::find()->where(['key_id' => $rv['key_id'], 'sku' => $rv['sku'], 'pur_number' => $rv['pur_number']])->one();
            if($model) {
                $status = self::SaveOne($model, $rv);
            } else {
                $model = new self;
                $status = self::SaveOne($model, $rv);
            }
            if($status) {
                $response['success_list'][] = $rv['id'];
            } else {
                $response['failure_list'][] = $rv['id'];
            }
        }

        $success_list = array_unique($response['success_list']);
        $failure_list = array_unique($response['failure_list']);
        // vd($response);
        return ['success_list'=> $success_list, 'failure_list'=> $failure_list];
    }

    public static function SaveOne($model, $data)
    {
        $model->pur_number= $data['pur_number'] ? $data['pur_number'] : '';
        $model->sku= $data['sku'] ? $data['sku'] : '';
        $model->key_id= $data['key_id'] ? $data['key_id'] : '';
        $model->order_id= $data['order_id'] ? $data['order_id'] : '';
        $model->is_clear= $data['is_clear'] ? $data['is_clear'] : '';
        $model->custom_number= $data['custom_number'] ? $data['custom_number'] : '';
        $model->clear_time= $data['clear_time'] ? $data['clear_time'] : '';
        $model->amounts= $data['amounts'] ? $data['amounts'] : '';
        $model->price= $data['price'] ? $data['price'] : '';
        $model->declare_name= $data['declare_name'] ? $data['declare_name'] : '';
        $model->declare_unit= $data['declare_unit'] ? $data['declare_unit'] : '';
        $model->declare= $data['declare'] ? $data['declare'] : '';
        $model->create_time = date('Y-m-d H:i:s', time());
        $res = $model->save(false);
        if($res) {
            return true;
        } else {
            return false;
        }

    }

}
