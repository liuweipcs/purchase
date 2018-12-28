<?php
namespace app\api\v1\models;
use app\models\PurchaseAbnormals;
class PurchaseWarehouseAbnormal extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'pur_purchase_warehouse_abnormal';
    }

    public static function FindOnes($datass)
    {
        try {
            $response = [];
            foreach($datass as $k=>$v) {

                if(!isset($v['defective_id'])) {
                    continue;
                }

                $model = self::find()->where(['defective_id' => $v['defective_id']])->one();
                if($model) {
                    $res = self::SaveOne($model, $v);
                } else {
                    $model = new self;
                    $res = self::SaveOne($model, $v);
                }
                $datas = [
                    'title' => '快递单 '.$v['express_code'].' 出现了PO异常了，请及时处理',
                    'content' => '快递单 '.$v['express_code'].' 出现了PO异常了，请及时处理',
                    'pur_number' => $v['express_code'],
                    'type' => '3',
                ];
                PurchaseAbnormals::Saves($datas);
                if($res) {
                    $response['success_list'][] = $v['defective_id'];
                } else {
                    $response['failure_list'][] = $v['defective_id'];
                }
            }

            return $response;

        } catch(\Exception $e) {
            \app\config\Vhelper::dump($e);
        }
    }

    public static function SaveOne($model, $data)
    {
        $model->sku                  = isset($data['sku']) ? $data['sku'] : '';
        $model->num                  = isset($data['num']) ? $data['num'] : '';
        $model->position             = isset($data['position']) ? $data['position'] : '';
        $model->defective_type       = isset($data['defective_type']) ? $data['defective_type'] : '';
        $model->defective_id         = isset($data['defective_id']) ? $data['defective_id'] : '';
        $model->purchase_order_no    = isset($data['purchase_order_no']) ? $data['purchase_order_no'] : '';
        $model->abnormal_type        = isset($data['abnormal_type']) ? $data['abnormal_type'] : '';
        $model->express_code         = isset($data['express_code']) ? $data['express_code'] : '';
        $model->abnormal_depict      = isset($data['abnormal_depict']) ? $data['abnormal_depict'] : '';
        $model->buyer                = isset($data['purchase_order_username']) ? $data['purchase_order_username'] : '';
        $model->img_path_data        = isset($data['img_path_data']) ? json_encode($data['img_path_data']) : '';
        $model->can_handle_type_data = isset($data['can_handle_type_data']) ? json_encode($data['can_handle_type_data']) : '';
        $model->add_username         = isset($data['add_username']) ? $data['add_username'] : '';
        $model->pull_time            = date('Y-m-d H:i:s', time());
        $model->is_handler           = 0;
        $res = $model->save(false);
        if($res) {
            return true;
        } else {
            return false;
        }
    }

}
