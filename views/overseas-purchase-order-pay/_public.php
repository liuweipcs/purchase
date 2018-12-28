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
    }
    .ftx-02 {
        color: #090;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <table class="table table-bordered table-condensed">
            <tr>
                <th>供应商</th>
                <td colspan="3"><?= $orderInfo['supplier_name'] ?></td>
            </tr>
            <tr>
                <th>采购单号</th>
                <td><?= $orderInfo['pur_number'] ?></td>
                <th>订单创建人</th>
                <td><?= $orderInfo['creator'] ?></td>
            </tr>
            <tr>
                <th>结算方式</th>
                <td><?= $orderInfo['account_type']?SupplierServices::getSettlementMethod($orderInfo['account_type']):''; ?></td>
                <th>支付方式</th>
                <td><?= $orderInfo['pay_type']?SupplierServices::getDefaultPaymentMethod($orderInfo['pay_type']):''; ?></td>
            </tr>
            <tr>
                <th>申请时间</th>
                <td colspan="3"><?= $payInfo['application_time']; ?></td>
            </tr>
            <?php
            if($payInfo['auditor']):
                if($payInfo['pay_status'] == 11) {
                    $bg = '#abcdef';
                } else {
                    $bg = '#eee';
                }
                ?>
                <tr style="background-color: <?= $bg ?>">
                    <th>审核人(经理)</th>
                    <td><?= $payInfo['auditor']?BaseServices::getEveryOne($payInfo['auditor']):''; ?></td>
                    <th>审核备注</th>
                    <td><?= $payInfo['review_notice'] ?></td>
                </tr>
            <?php endif; ?>
            <?php
            if($payInfo['approver']):
                if($payInfo['pay_status'] == 3) {
                    $bg = '#abcdef';
                } else {
                    $bg = '#eee';
                }
                ?>
                <tr style="background-color: <?= $bg ?>">
                    <th>审批人(财务)</th>
                    <td><?= $payInfo['approver']?BaseServices::getEveryOne($payInfo['approver']):''; ?></td>
                    <th>审批备注</th>
                    <td><?= $payInfo['processing_notice'] ?></td>
                </tr>
            <?php endif; ?>
            <?php
            if($payInfo['payer']):
                if($payInfo['pay_status'] == 12) {
                    $bg = '#abcdef';
                } else {
                    $bg = '#eee';
                }
                ?>
                <tr style="background-color: <?= $bg ?>">
                    <th>付款人(出纳)</th>
                    <td><?= $payInfo['payer']?BaseServices::getEveryOne($payInfo['payer']):''; ?></td>
                    <th>付款备注</th>
                    <td><?= $payInfo['payment_notice'] ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>
<div class="row" id="paystate">
    <div clsss="col-md-12">
        <div class="mt">
            <strong>
                <div class="fl">请款单号： <?= $payInfo['requisition_number'] ?>&nbsp;&nbsp;&nbsp;&nbsp;</div>
                <div class="fl">状态：<span class="ftx-02"><?= PurchaseOrderServices::getPayStatus($payInfo['pay_status']); ?></span></div>
            </strong>
        </div>
        <div class="mc">本次请款方式：<?= PurchaseOrderServices::getRequestPayoutType($orderInfo['rpt']); ?></div>
    </div>
</div>
<div class="row" style="background-color: #f3f3f3;">
    <div class="col-md-2">图片</div>
    <div class="col-md-4">产品</div>
    <div class="col-md-3">数量</div>
    <div class="col-md-3">入库时间</div>
</div>
<?php
foreach($orderInfo['purchaseOrderItems'] as $k=>$v):
    $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'img-thumbnail']);
    if($v['yizhifu_num']>0):
        ?>
        <div class="row">
            <div class="col-md-2">
                <?=$img ?>
            </div>
            <div class="col-md-4">
                <p>SKU：<em><?= $v['sku'] ?></em></p>
                <p>单价：<em><?= $v['price'] ?></em></p>
                <p>本次采购价：<em><?= $v['price'] ?></em></p>
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
                    <li>本次请款数量：<strong><?= $v['yizhifu_num'] ?></strong></li>
                </ul>
            </div>
            <div class="col-md-3">
                <?= $v['instock_date'] ?>
            </div>
        </div>
    <?php endif; endforeach; ?>
<div class="row" style="background-color: #f3f3f3;">
    <div class="col-md-12">
        <p>本次请款金额：<em style="font-size:23px;"><?= $payInfo['pay_price'] ?></em></p>
        <?php if(isset($payInfo['freight']) && $payInfo['freight'] > 0): ?>
            <p>其中含运费：<em><?= $payInfo['freight'] ?></em></p>
        <?php endif; ?>
        <?php if(isset($payInfo['discount']) && $payInfo['discount']>0): ?>
            <p>使用优惠额：<em><?= $payInfo['discount'] ?></em></p>
        <?php endif; ?>
    </div>
</div>
