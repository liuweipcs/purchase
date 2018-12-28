<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = '退货物流';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= Html::beginForm(['logisticsave'], 'post', ['enctype' => 'multipart/form-data']) ?>
<?= Html::input('hidden', 'id', $data['pur_number'])?>
<div>地&nbsp;&nbsp;&nbsp;&nbsp;址 : <?=Html::textarea('note','',['cols'=>10,'rows'=>6,'style'=>'width:100%','required'=>true,'placeholder'=>'地址+联系人+联系电话'])?></div><br/>
<p style="text-align: right">
    <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Close', '#', ['class' => 'btn btn-primary closes','data-dismiss'=>'modal']) ?>
</p>
<?= Html::endForm() ?>
<script>
    $("[name='cargo_company_id']").on('change', function () {
        var cargo_company = $(this).children(':selected').html();
        $(this).prev().val(cargo_company);
    })
</script>
