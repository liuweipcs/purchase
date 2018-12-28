<?php

use yii\helpers\Html;



$this->title = '生成收款单';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::beginForm(['receipt-save'], 'post', ['enctype' => 'multipart/form-data']) ?>
<table class='table table-hover table-bordered table-striped'>
    <tr>
        <td>采购单 : <?=$data[0]['pur_number']?></td>
        <td>供应商 : <?=$data[0]['supplier_code']?></td>
        <td>
            <span class="label label-danger"><?=Yii::$app->params['receive_type'][$data[0]['receive_type']]?></span>
            <span class="label label-danger"><?=Yii::$app->params['handle_type'][$data[0]['handle_type']]?></span>
        </td>
    </tr>
</table>
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <th>No.</th>
        <th>产品</th>
        <th>数量</th>
        <th>单价</th>
        <th>收款金额</th>
        <th>备注</th>
    </tr>
    <?php $due_money=0;?>
    <?php foreach ($data as $key=>$val):?>
    <tr>
        <td><?=$key+1?></td>
        <td>
            SKU:<?=$val['sku']?></br>
            名称:<?=$val['name']?>
        </td>
        <td>
            预期: <?=$val['qty']?></br>
            到货: <?=$val['delivery_qty']?></br>
            赠送: <?=$val['presented_qty']?>
        </td>
        <td><?=$val['price']?></td>
        <td class="text-danger">
            <?php 
                $row_total=($val['qty']-$val['delivery_qty']-$val['presented_qty'])*$val['price'];
                $due_money+=$row_total;
            ?>
            <?=$row_total?>
        </td>
        <td>
            单据: <?=$val['note']?></br>
            处理: <?=$val['note_handle']?></br>
            审核: <?=$val['note_audit']?></br>
            <?=Html::input('hidden', 'PurchaseReceiveAudit[id][]', $val['id'])?>
        </td>
    </tr>
    <?php endforeach;?>
    <?=Html::input('hidden', 'PurchaseReceiveAudit[due_money]', $due_money)?>
    <?=Html::input('hidden', 'PurchaseReceiveAudit[express_no]', $val['express_no'])?>
    <?=Html::input('hidden', 'PurchaseReceiveAudit[pur_number]', $val['pur_number'])?>
    <?=Html::input('hidden', 'PurchaseReceiveAudit[currency_code]', $val['currency_code'])?>
    <?=Html::input('hidden', 'PurchaseReceiveAudit[supplier_code]', $val['supplier_code'])?>
    <?=Html::input('hidden', 'PurchaseReceiveAudit[supplier_name]', $val['supplier_name'])?>
</table>
<p style="text-align:right"> <?= Html::submitButton('确定', ['class' => 'btn btn-primary']) ?> </p>
<?= Html::endForm() ?>