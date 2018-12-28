<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\bootstrap\Modal;
use app\services\BaseServices;
?>
<div class="my-box">
    <a href="/purchase-compact/view?cpn=<?= $model->pur_number ?>" target="_blank" class="btn btn-info">查看采购订单合同</a>
    <a href="/purchase-compact/show-form?id=<?= $model->id ?>" id="sf" data-toggle = 'modal' data-target = '#created-modal' class="btn btn-info">查看付款申请书</a>
</div>

<table class="my-table" style="margin-bottom: 10px;">
    <tr>
        <td><strong>供应商名称</strong></td>
        <td colspan="3"><?= BaseServices::getSupplierName($model->supplier_code); ?></td>
    </tr>
    <tr>
        <td><strong>本次请款比例</strong></td>
        <td><?= $model->pay_ratio ?></td>
        <td><strong>结算比例</strong></td>
        <td><?= $model->js_ratio ?></td>
    </tr>
    <tr>
        <td><strong>结算方式</strong></td>
        <td></td>
        <td><strong>支付方式</strong></td>
        <td></td>
    </tr>
    <tr>
        <td><strong>请款人</strong></td>
        <td><?= BaseServices::getEveryOne($model->applicant) ?></td>
        <td><strong>请款备注</strong></td>
        <td><?= $model->create_notice ?></td>
    </tr>
    <td><strong>财务审批人</strong></td>
    <td><?= $model->approver ?></td>
    <td><strong>财务审批备注</strong></td>
    <td><?= $model->payment_notice ?></td>
    </tr>
</table>

<!-- 该部分可作为公共模板 -->
<table class="my-table">
    <thead>
    <tr>
        <th>采购单号</th>
        <th>SKU</th>
        <th>图片</th>
        <th>品名</th>
        <th>单价</th>
        <th>数量</th>
        <th>小计</th>
    </tr>
    </thead>

    <?php foreach($data as $kk => $order): ?>

        <?php
        foreach($order['purchaseOrderItems'] as $k => $v):
            $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'img-thumbnail']);
            $price = $v['price'];
            $num = $v['ctq'] ? $v['ctq'] : 0; // 采购数量
            $oneSkuMOney = $price*$num;
            ?>
            <tr>
                <?php if($k == 0): ?>
                    <td rowspan="<?= count($order['purchaseOrderItems']) ?>" style="vertical-align: middle;text-align: center;"><?= $order['pur_number'] ?></td>
                <?php endif; ?>
                <td><?= $v['sku'] ?></td>
                <td><?= $img ?></td>
                <td style="width: 400px;"><?= $v['name'] ?></td>
                <td><?= $price ?></td>
                <td><?= $num ?></td>
                <td><?= $oneSkuMOney ?></td>
            </tr>

        <?php endforeach; ?>

    <?php endforeach; ?>

</table>

<h4>本次请款金额：<strong style="color: red;"><?= $model->pay_price ?></strong></h4>

<?php

Modal::begin([
    'id' => 'created-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1'
    ],
]);
Modal::end();
$js = <<<JS
$(function() {
    $('#sf').click(function () {
        $.get($(this).attr('href'),
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
});
JS;
$this->registerJs($js);
?>




