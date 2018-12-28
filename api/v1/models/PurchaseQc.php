<?php

namespace app\api\v1\models;

use app\config\Vhelper;
use Yii;

/**
 * This is the model class for table "pur_purchase_qc".
 *
 * @property string $id
 * @property string $express_no
 * @property string $pur_number
 * @property string $warehouse_code
 * @property string $supplier_code
 * @property string $supplier_name
 * @property string $sku
 * @property string $name
 * @property string $buyer
 * @property string $qc_status
 * @property string $handle_type
 * @property string $price
 * @property string $qty
 * @property string $delivery_qty
 * @property string $presented_qty
 * @property string $check_qty
 * @property string $good_products_qty
 * @property string $bad_products_qty
 * @property integer $check_type
 * @property string $note
 * @property string $created_at
 * @property string $creator
 * @property string $time_handle
 * @property string $handler
 * @property string $time_audit
 * @property string $auditor
 * @property string $note_audit
 */
class PurchaseQc extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pur_purchase_qc';
    }

    /**
     *
     * @param mixed $datass
     * @return array|null|\yii\db\ActiveRecord
     */
   /* public static function FindOnes($datass)
    {


        //qc异常
        $model = PurchaseQc::find()->where(['express_no' => $datass['express_no'], 'pur_number' => $datass['pur_number'], 'sku' => $datass['sku']])->one();

                if ($model)
                {
                    $datass['qc_status'] = $model->attributes['qc_status'];
                    PurchaseQc::SaveOne($model, $datass);

                } else {
                    $model = new PurchaseQc();
                    $datass['qc_status'] = 1;
                    PurchaseQc::SaveOne($model, $datass);

                }
                PurchaseOrder::UpdateStatus($datass['purchase_quantity'],$datass['arrival_quantity'],$datass['pur_number'],1,2);






    }*/
    /**
     * 新增数据
     * @param $model
     * @param $datass
     * @return mixed
     */
   /* public  static function SaveOne($model,$datass)
    {

        $pur = PurchaseOrder::getFiled($datass['pur_number'],'supplier_name,buyer,supplier_code,warehouse_code');
        //快递单号
        $model->express_no                      = $datass['express_no'];
        //采购单
        $model->pur_number                      = $datass['pur_number'];
        //供应商名
        $model->supplier_name                   = $pur['supplier_name'];
        //供货商CODE
        $model->supplier_code                   = $pur['supplier_code'];
        //sku
        $model->sku                             = $datass['sku'];
        //产品名
        $model->name                            = PurchaseOrderItems::getProductName($datass['pur_number'],$datass['sku']);
        //采购单数
        $model->qty                             = $datass['purchase_quantity'];
        //到货数量
        $model->delivery_qty                    = $datass['arrival_quantity'];
        //状态
        $model->qc_status                       = $datass['qc_status'];
        $model->type                            = 2;
        //备注
        $model->note                            = '';
        //检查数量
        $model->check_qty                       = !empty($datass['check_qty'])?$datass['check_qty']:'1'; //无
        //良品数量
        $model->good_products_qty               = !empty($datass['check_qty'])?$datass['check_qty'] - $datass['nogoods']:'1';
        //不良品数量
        $model->bad_products_qty                = $datass['nogoods'];
        //品检类型
        $model->check_type                      = !empty($datass['check_type'])?$datass['check_type']:'1';  //无
        //异常创建人
        $model->creator                         = 'admin';
        //采购员
        $model->buyer                           = $pur['buyer'];
        //异常创建时间
        $model->created_at                      = date('Y-m-d H:i:s');
        $model->price                           = PurchaseOrderItems::getPrice($datass['pur_number'],$datass['sku']);
        //仓库code
        $model->warehouse_code                  = $pur['warehouse_code'];
        //赠送数量
        $model->presented_qty                   = ($datass['arrival_quantity'] - $datass['purchase_quantity']) < 0 ?0:$datass['arrival_quantity'] - $datass['purchase_quantity'];
        $status =$model->save();
        return $status;
    }*/
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
        //供货商CODE
        $model->supplier_code                   = $datass['provider_code'];
        //sku
        $model->sku                             = $datass['sku'];
        //产品名
        $model->name                            = $datass['name'];
        //采购单数
        $model->qty                             = $datass['purchase_qty'];
        //到货数量
        $model->delivery_qty                    = $datass['delivery_qty'];
        //状态
        $model->qc_status                       = !empty($model->qc_status) && $model->qc_status>=2?$model->qc_status:1;
        $model->type                            = 2;
        $model->qc_id                           = $datass['qc_id'];
        //备注
        $model->note                            = $datass['note'];
        //检查数量
        $model->check_qty                       = $datass['check_qty'];
        //良品数量
        $model->good_products_qty               = $datass['check_qty'] - $datass['bad_products_qty'];
        //不良品数量
        $model->bad_products_qty                = $datass['bad_products_qty'];
        //品检类型
        $model->check_type                      = $datass['check_type'];
        //异常创建人
        $model->creator                         = $datass['create_user'];
        //仓库图片
        $model->img                             = !empty($datass['img'])?$datass['img']:'';
        //采购员
        $model->buyer                           = !empty($datass['creator'])?$datass['creator']:'';
        //异常创建时间
        $model->created_at                      = $datass['create_time'];
        $model->price                           = PurchaseOrderItems::getPrice($datass['purchase_order_no'],$datass['sku']);
        //仓库code
        $model->warehouse_code                  = $datass['warehouse_code'];
        //赠送数量
        $model->presented_qty                   = ($datass['delivery_qty'] - $datass['purchase_qty']) < 0 ?0:$datass['delivery_qty'] - $datass['purchase_qty'];;
        $status =$model->save();
        return $status;
    }

}
