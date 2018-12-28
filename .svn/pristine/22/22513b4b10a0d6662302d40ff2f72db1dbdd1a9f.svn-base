<?php
namespace app\api\v1\controllers;
use app\api\v1\models\AliOrderLogisticsItems;
use app\api\v1\models\PurchaseOrder;
use app\api\v1\models\SupplierDeliverAdress;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/9
 * Time: 10:19
 */

class AliOrderCheckController extends BaseController{
    //通过订单信息获取供应商发货地址
    public function actionGetSupplierAdress(){
        $begin = microtime(true);
        $adress = AliOrderLogisticsItems::find()
            ->alias('t')
            ->select('t.id,t.order_number,t.from_province,t.from_city,t.from_area,t.from_address,o.supplier_code,t.pur_number')
            ->leftJoin(PurchaseOrder::tableName().' o','o.pur_number=t.pur_number')
            ->where(['t.is_check'=>0,'t.items_status'=>1])
            ->limit(500)
            ->orderBy('id ASC')
            ->asArray()
            ->all();
        SupplierDeliverAdress::saveDatas($adress);
        echo microtime(true)-$begin;
    }
}