<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4
 * Time: 10:27
 */

namespace app\api\v1\models;


class PurchaseSuggestQuantity extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'pur_purchase_suggest_quantity';
    }
    public static function saveOne($model,$datass)
    {
        $model->sku                 = isset($datass['sku'])?$datass['sku'] : ''; //sku
        $model->platform_number     = isset($datass['platform_number'])?$datass['platform_number'] : ''; //平台号
        $model->purchase_quantity   = isset($datass['purchase_quantity'])?$datass['purchase_quantity'] : ''; //采购数量
        $model->purchase_warehouse  = isset($datass['purchase_warehouse'])?$datass['purchase_warehouse'] : ''; //采购仓
        $model->create_id           = isset($datass['create_id'])? $datass['create_id']: ''; //创建人
        $model->create_time         = isset($datass['create_time'])? $datass['create_time']: ''; //创建时间
        $model->suggest_status      = 1; //采购建议状态：默认1未使用过，2使用过
        $model->sales_note          = isset($datass['sales_note'])? $datass['sales_note']: '';        //备注
        $model->purchase_type       = isset($datass['purchase_type'])? $datass['purchase_type']: ''; //采购类型

        $status =$model->save();
        return $status;
    }
}