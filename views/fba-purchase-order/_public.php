<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
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

        <h5>订单信息</h5>
        <table class="table table-bordered table-condensed">
            <tr>
                <th class="col-md-2">供应商</th>
                <td colspan="3"><?= $orderInfo['supplier_name'] ?></td>
            </tr>
            <tr>
                <th class="col-md-2">订单创建人</th>
                <td><?= $orderInfo['creator'] ?></td>
                <th>创建时间</th>
                <td><?= $orderInfo['created_at'] ?></td>
            </tr>
            <tr>
                <th>结算方式</th>
                <td><?= $orderInfo['account_type'] ? SupplierServices::getSettlementMethod($orderInfo['account_type']) : ''; ?></td>
                <th>支付方式</th>
                <td><?= $orderInfo['pay_type'] ? SupplierServices::getDefaultPaymentMethod($orderInfo['pay_type']) : ''; ?></td>
            </tr>
        </table>

    </div>
</div>


<div class="row" id="paystate">
    <div clsss="col-md-12">
        <div class="mt">
            <strong>
                <div class="fl">采购单号： <?= $orderInfo['pur_number'] ?>&nbsp;&nbsp;&nbsp;&nbsp</div>
                <div class="fl">状态：<span class="ftx-02"><?= PurchaseOrderServices::getPurchaseStatus($orderInfo['purchas_status']); ?></span></div>
            </strong>
        </div>
        <div class="mc">注：财务付过款的订单，不可以作废</div>
    </div>
</div>


<div class="row" style="background-color: #f3f3f3;">
    <div class="col-md-2">图片</div>
    <div class="col-md-4">产品</div>
    <div class="col-md-3">数量</div>
    <div class="col-md-3">收货时间</div>
</div>

<?php
foreach($orderInfo['purchaseOrderItems'] as $k => $v):
    $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'img-thumbnail']);
?>
<div class="row">
    <div class="col-md-2">
        <?=$img ?>
    </div>
    <div class="col-md-4">
        <p>SKU：<em><?= $v['sku'] ?></em></p>
        <p>单价：<em><?= $v['price'] ?></em></p>
        <p><?= $v['name'] ?></p>
    </div>
    <div class="col-md-3">
        <ul class="list-unstyled">
            <li>订单数量：<strong><?= $v['ctq'] ?></strong></li>
            <li>取消数量：<strong><?= $v['quxiao_num'] ?></strong></li>
            <li>收货数量：<strong><?= $v['shouhuo_num'] ?></strong></li>
            <li>未到货数量：<strong><?= $v['weidaohuo_num'] ?></strong></li>
            <li>入库数量：<strong><?= $v['ruku_num'] ?></strong></li>
            <li>不良品数量：<strong><?= $v['nogoods'] ?></strong></li>
        </ul>
    </div>
    <div class="col-md-3">
        <?php if($v['instock_date']): ?>
            <em><?= $v['instock_date'] ?></em>
        <?php else: ?>
            <b style="color: red;">未设置</b>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>