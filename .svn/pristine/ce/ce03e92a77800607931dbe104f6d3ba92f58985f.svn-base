<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="row">
<table class="table table-bordered">

    <thead>
    <tr>
        <th>采购单价</th>
        <th>最新报价</th>
        <th>采购数量</th>
        <th>采购员</th>
        <th>采购时间</th>

    </tr>
    </thead>
    <tbody>
    <?php foreach($model as $v){?>
    <tr>
        <td><?=$v->purchase_price?></td>
        <td><?=$v->latest_offer?></td>
        <td><?=$v->purchase_quantity?></td>
        <td><?=$v->buyer?></td>
        <td><?=$v->purchase_time?></td>

    </tr>
    <?php }?>

    </tbody>

</table>
    </div>


