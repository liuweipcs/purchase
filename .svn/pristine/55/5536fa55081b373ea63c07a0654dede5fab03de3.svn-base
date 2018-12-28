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
            仓库备注: <?=$val['note']?></br>
            采购备注: <?=$val['note_handle']?></br>
            <?= $val['is_return'] == 1 ? '退款金额: ' . $val['refund_amount'] . '<br>' : '' ?>
        </td>
        <td>
            <?=Html::textarea("PurchaseReceiveAudit[{$val['id']}][note_audit]", $val['note_audit'])?></br>
            <?=Html::input('hidden',"PurchaseReceiveAudit[{$val['id']}][pur_number]", $data[0]['pur_number'])?></br>
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
        $("div.handle_type").find("input").change(function(){
            var handle_type=this.value;
            if(handle_type=="return"){
                $(this).parents("tr").find("div.bearer").hide();
                $(this).parents("tr").find("div.bearer input").attr("disabled","disabled");
            }else{
                $(this).parents("tr").find("div.bearer").show();
                $(this).parents("tr").find("div.bearer input").removeAttr("disabled");
            }
        });
        //审核不通过就置为0
        $("button#not_pass").click(function(){
            $("input#is_pass").val(0);
        });
   });
JS;
$this->registerJs($js);
?>