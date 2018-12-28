<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderPayType;
?>
<style>
    .container-fluid {
        border: 1px solid #ccc;
    }
    .row {
        border-top: 1px solid #ccc;
        padding: 10px;
    }
    ins {
        padding-left: 10px;
        color: red;
    }
    em {
        color: #e4393c;
        font-weight: 700;
        font-style: normal;
    }
    h5 {
        font-weight: bold;
    }
    p {
        margin: 0;
    }
    #paystate {
        border: 1px solid #EED97C;
        padding: 0 5px;
        background: #FFFCEB;
        margin-bottom: 10px;
    }
    #paystate .mt {
        padding: 4px 8px;
        border-bottom: 1px dotted #EED97C;
        height: 35px;
        line-height: 30px;
    }
    #paystate .mt strong {
        float: left;
        font-size: 14px;
    }
    #paystate .mt .fl {
        font-size: 14px;
        font-weight: 700;
        float: left;
    }
    #paystate .mc {
        padding: 10px 8px;
        color: red;
    }
    .ftx-02 {
        color: #090;
    }
</style>
<div class="row">
    <div class="col-md-12">

        <h5>采购单信息</h5>
        <table class="table table-bordered table-condensed">
            <tr>
                <th class="col-md-2">采购单号</th>
                <td colspan="3"><?= $orderInfo['pur_number'] ?></td>
                <th class="col-md-2">状态</th>
                <td colspan="3"><?= PurchaseOrderServices::getPurchaseStatus($orderInfo['purchas_status']); ?></td>
                <th class="col-md-2">供应商</th>
                <td colspan="3"><?= $orderInfo['supplier_name'] ?></td>
            </tr>
            <tr>
                <th class="col-md-2">订单总额</th>
                <td colspan="3"><?=PurchaseOrder::getOrderTotalPrice($orderInfo['pur_number']);?></td>
                <?php $price_info = PurchaseOrderPayType::getDiscountPrice($orderInfo['pur_number']); ?>
                <th class="col-md-2">运费</th>
                <td colspan="3"><?=!empty($price_info['freight']) ?$price_info['freight'] : 0; ?></td>
                <th class="col-md-2">优惠</th>
                <td colspan="3"><?=!empty($price_info['discount']) ?$price_info['discount'] : 0; ?></td>
            </tr>
            <tr>
                <th class="col-md-2">已付款金额</th>
                <td colspan="3"><?=PurchaseOrderPay::getOrderPaidMoney($orderInfo['pur_number']); ?></td>
                <th class="col-md-2">已取消金额</th>
                <td colspan="3"><?=PurchaseOrderCancelSub::getCancelPriceOrder($orderInfo['pur_number']); ?></td>
            </tr>
        </table>
    </div>
</div>