<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="purchase-order-form">
    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" name="page" value="<?=$page?>" />

    <h4><?=Yii::t('app','付款申请')?></h4>
    <table class="table table-bordered ">
        <thead>
        <tr>
            <th>序号</th>
            <th>采购单号</th>
            <th>SKU</th>
            <th>产品名称</th>
            <th>单价</th>
            <th>确认数量</th>
            <th>运费</th>
            <th>应付</th>
        </tr>
        </thead>

        <tbody>
        <?php
        if(is_array($models)){
            $i=1;
            $total_price=0;
            foreach($models as $k=>$c) {
                foreach ($c['purchaseOrderItems'] as $b=>$v) {
                    $total_price +=$v['price'] * $v['ctq'];
                    ?>
                    <tr>
                        <th scope="row"><?=$i?></th>
                        <td><?= $v['pur_number']; ?></td>
                        <td><?= $v['sku'] ?></td>
                        <td><?= $v['name'] ?></td>
                        <td><?= $v['price'] ?></td>
                        <td><?= $v['ctq'] ?></td>
                        <td><?= $c['orderShip']['freight'] ?></td>
                        <td><?= $v['items_totalprice']. ' ' . $c['currency_code'] ?></td>
                    </tr>
                    <input type="hidden" id="pnum_pto" name="pnum_pto[]"
                           value="<?= $v['pur_number']?>"/>
                    <?php

                    $i++;
                }
            }?>
            <tr class="table-module-b1">
                <td class="ec-center" colspan="8" style="text-align: left;">
                    <b>申请付款总额：</b>
                    <b style="color: red"><?=sprintf("%.2f", $total_price)?></b>
                </td>
            </tr>
        <?php }?>

        </tbody>
    </table>

    <div class="form-group">
        <input type="hidden" name="allpaytoken" value="<?=Yii::$app->session->get('allpaytoken')?>">
        <?= Html::submitButton( '立即申请', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>