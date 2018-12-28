<?php

use yii\helpers\Html;



$this->title = '仓库补货策略';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::beginForm(['audit-save'], 'post', ['enctype' => 'multipart/form-data']) ?>
<table class='table table-hover table-bordered table-striped'>
    <tr>
        <td>采购单 : <?=$data[0]['pur_number']?></td>
        <td>供应商 : <?=$data[0]['supplier_code']?></td>
        <td>
            <span class="label label-danger"><?=Yii::$app->params['handle_type_qc'][$data[0]['handle_type']]?></span>
        </td>
    </tr>
</table>
<table class='table table-hover table-bordered table-striped' >
    <tr>
        <th>No.</th>
        <th>产品</th>
        <th>数量</th>
        <th>单价</th>
        <th>备注</th>
        <th>审核备注</th>
    </tr>
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
        <td>
            单据: <?=$val['note']?></br>
        </td>
        <td>
            <?=Html::textarea("PurchaseQcPutaway[{$val['id']}][note_audit]", $val['note_audit'])?></br>
            <?=Html::input('hidden', 'is_pass', 1,['id'=>'is_pass'])?>
        </td>
    </tr>
    <?php endforeach;?>
</table>
<p style="text-align:right"><?= Html::submitButton('审核不通过', ['class' => 'btn btn-primary','id'=>'not_pass']) ?> <?= Html::submitButton('审核通过', ['class' => 'btn btn-primary']) ?> </p>
<?= Html::endForm() ?>
<?php
$js=<<<JS
   $(function(){
        //审核不通过就置为0
        $("button#not_pass").click(function(){
            $("input#is_pass").val(0);
        });
   });
JS;
$this->registerJs($js);
?>