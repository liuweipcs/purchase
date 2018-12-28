<?php

namespace app\api\v1\models;
use app\config\Vhelper;
use app\models\PurchaseAbnormals;
use Yii;

/**
 * This is the model class for table "pur_purchase_receive".
 *
 * @property string $id
 * @property string $pur_number
 * @property string $supplier_code
 * @property string $supplier_name
 * @property string $buyer
 * @property string $sku
 * @property string $name
 * @property string $qty
 * @property string $delivery_qty
 * @property string $presented_qty
 * @property string $receive_type
 * @property string $handle_type
 * @property string $handler
 * @property string $auditor
 * @property string $bearer
 * @property string $created_at
 * @property string $time_handle
 * @property string $time_audit
 * @property string $receive_status
 * @property string $creator
 * @property string $price
 * @property string $note
 */
class PurchaseReceive extends \yii\db\ActiveRecord
{
    public $total_qty;//条目总数量
    public $total_delivery_qty;
    public $total_presented_qty;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_receive';
    }

    public static function FindOnes($datass)
    {


        foreach ($datass as $k=>$v)
        {
            //到货异常
            $datap= PurchaseOrder::getFiled($v['purchase_order_no'],'receiving_exception_status,qc_abnormal_status');
            if ($v['type']==1)
            {
                $model = self::find()->where(['qc_id' => $v['qc_id']])->one();

                if ($model) {
                    self::SaveOne($model, $v);

                    $data['success_list'][$k]['qc_id']      = $model->attributes['qc_id'];
                    $data['success_list'][$k]['type']       = $v['type'];
                    $data['failure_list'][]                 = '';
                } else {
                    $model = new self;
                    self::SaveOne($model, $v);
                    $data['success_list'][$k]['qc_id']      = $model->attributes['qc_id'];
                    $data['success_list'][$k]['type']       = $v['type'];
                    $data['failure_list'][]                 = '';
                }
                //有异常,qc无异常
                $re_status= !empty($datap['receiving_exception_status'])?$datap['receiving_exception_status']:2;
                $qc_status= !empty($datap['qc_abnormal_status'])?$datap['qc_abnormal_status']:1;
                PurchaseOrder::UpdateStatus($v['purchase_qty'],$v['delivery_qty'],$v['purchase_order_no'],$re_status,$qc_status);
                $datab = [
                    'title'=>'采购单'.$v['purchase_order_no'].'数量异常了,请及时处理',
                    'content'=>'采购单'.$v['purchase_order_no'].'数量异常了,请及时处理',
                    'pur_number'=>$v['purchase_order_no'],
                    'type'=>'1',
                ];
                PurchaseAbnormals::Saves($datab);
            } else {
                //qc异常
                $model = PurchaseQc::find()->where(['qc_id' => $v['qc_id']])->one();

                if ($model)
                {
                    PurchaseQc::SaveOne($model, $v);

                    $data['success_list'][$k]['qc_id']      = $model->attributes['qc_id'];
                    $data['success_list'][$k]['type']       = $v['type'];
                    $data['failure_list'][]                 = '';
                } else {
                    $model = new PurchaseQc();
                    PurchaseQc::SaveOne($model, $v);
                    $data['success_list'][$k]['qc_id']      = $model->attributes['qc_id'];
                    $data['success_list'][$k]['type']       = $v['type'];
                    $data['failure_list'][]                 = '';
                }
                //qc有异常,
                $re_status= !empty($datap['receiving_exception_status'])?$datap['receiving_exception_status']:1;
                $qc_status= !empty($datap['qc_abnormal_status'])?$datap['qc_abnormal_status']:2;
                PurchaseOrder::UpdateStatus($v['purchase_qty'],$v['delivery_qty'],$v['purchase_order_no'],$re_status,$qc_status);
                $datab = [
                    'title'=>'采购单'.$v['purchase_order_no'].'数量异常了,请及时处理',
                    'content'=>'采购单'.$v['purchase_order_no'].'数量异常了,请及时处理',
                    'pur_number'=>$v['purchase_order_no'],
                    'type'=>'2',
                ];
                PurchaseAbnormals::Saves($datab);

            }
        }

        return $data;


    }
    /**
     * 新增数据
     * @param $model
     * @param $datass
     * @return mixed
     */
    public  static function SaveOne($model,$datass)
    {
        //快递单号
        $model->express_no                      = $datass['express_no'];
        //采购单
        $model->pur_number                      = $datass['purchase_order_no'];
        //供应商名
        $model->supplier_name                   = $datass['provider_name'];
        //sku
        $model->sku                             = $datass['sku'];
        //产品名
        $model->name                            = $datass['name'];
        //采购单数
        $model->qty                             = $datass['purchase_qty'];
        //到货数量
        $model->delivery_qty                    = $datass['delivery_qty'];
        //状态
        $model->receive_status                  = !empty($model->receive_status) && $model->receive_status>=2?$model->receive_status:1;
        $model->type                            = 1;
        $model->shipping_method                 = 1;
        //备注
        $model->note                            = $datass['note'];
        $model->qc_id                           = $datass['qc_id'];
        //异常创建人
        $model->creator                         = $datass['create_user'];
        //采购员
        $model->buyer                           = !empty($datass['creator'])?$datass['creator']:'';
        //异常创建时间
        $model->created_at                      = $datass['create_time'];
        //仓库图片
        $model->img                             = !empty($datass['img'])?$datass['img']:'';
        //供货商CODE
        $model->supplier_code                   = $datass['provider_code'];
        //仓库code
        $model->warehouse_code                  = $datass['warehouse_code'];
        $model->handle_type                     = $model->handle_type>0?$model->handle_type:0;
        //单价
        $model->price                           = PurchaseOrderItems::getPrice($datass['purchase_order_no'],$datass['sku']);
        //赠送数量
        $model->presented_qty                   = ($datass['delivery_qty'] - $datass['purchase_qty']) < 0 ?0:$datass['delivery_qty'] - $datass['purchase_qty'];
        $types= ($datass['delivery_qty'] - $datass['purchase_qty']) < 0 ?2:1;
        //异常类型
        $model->receive_type                    = isset($datass['abnormal_category'])?$datass['abnormal_category']:$types;

        $status =$model->save();
        return $status;
    }

}
