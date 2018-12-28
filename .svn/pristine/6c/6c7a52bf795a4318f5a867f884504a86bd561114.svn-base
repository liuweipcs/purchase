<?php
use yii\widgets\ActiveForm;
?>
<style type="text/css">
    .cc {
        display: inline-block; margin-right: 10px;
    }
    em {
        color: red;
        font-weight: bold;
        font-style: normal;
    }
    .fr {
        float: right;
    }
</style>
<?php $form = ActiveForm::begin(['id' => 'fm']); ?>

<h5>批量申请付款</h5>

<?php

    $i = 0;

    foreach($orders as $key => $item):
        $freight = !empty($item['purchaseOrderPayType']) ? (float)$item['purchaseOrderPayType']['freight'] : 0;
        $discount = !empty($item['purchaseOrderPayType']) ? (float)$item['purchaseOrderPayType']['discount'] : 0;
        $order_money = 0;
?>

<?php if($item['pay_type'] == 2): $i++; ?>

<table class="table table-bordered">
    <thead>
        <tr>

            <th>采购单号</th>
            <th>SKU</th>
            <th style="width: 375px;">名称</th>
            <th>单价</th>
            <th>数量</th>
            <th>小计</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $n = count($item['purchaseOrderItems']);
        foreach($item['purchaseOrderItems'] as $k=>$v):
            $num = $v['ctq'] ? $v['ctq'] : 0;
            $price = $v['price'] ? $v['price'] : 0;
            $total_price = $price*$num;
            $order_money += $total_price;

            ?>
            <tr>

                <?php if($k == 0): ?>
                <td rowspan="<?= $n ?>" style="vertical-align: middle;text-align: center;"><?= $item['pur_number'] ?></td>
                <?php endif; ?>



                <td><?= $v['sku'] ?></td>
                <td><?= $v['name'] ?></td>
                <td><?= $price ?></td>
                <td><?= $num ?></td>
                <td><?= $total_price ?></td>

            </tr>
        <?php endforeach; ?>
    </tbody>

    <tfoot>
        <tr>
            <td colspan="8" style="text-align: right;">
                <span class="cc">总额：<em><?= $order_money ?></em></span>
                <span class="cc">运费：<em><?= $freight ?></em></span>
                <span class="cc">优惠：<em><?= $discount ?></em></span>
                <span class="cc">实付：<em><?= $order_money+$freight-$discount ?></em></span>
            </td>
        </tr>
    </tfoot>

</table>

<input type="hidden" name="AllPayment[<?= $key ?>][pur_number]" value="<?= $item['pur_number'] ?>">
<input type="hidden" name="AllPayment[<?= $key ?>][freight]" value="<?= $freight ?>">
<input type="hidden" name="AllPayment[<?= $key ?>][discount]" value="<?= $discount ?>">
<input type="hidden" name="AllPayment[<?= $key ?>][pay_price]" value="<?= $order_money ?>">


<?php else: ?>

<p class="bg-success" style="padding: 10px;margin-bottom: 5px;"><?= $item['pur_number'] ?>：支付方式不是支付宝，不能走批量付款</p>

<?php endif; ?>


<?php endforeach; ?>


<?php if($i > 0): ?>

<div class="my-box" style="height: 50px;">
    <button type="button" class="btn btn-primary fr">立即申请</button>
</div>

<?php endif; ?>


<?php
ActiveForm::end();
$js = <<<JS
$(function() {
    $('.fr').click(function() {
        var index = layer.load(2, {shade: [0.8, '#141617bd;']});
        $(this).hide();
        $('#fm').submit();
    });
});
JS;
$this->registerJs($js);
?>
