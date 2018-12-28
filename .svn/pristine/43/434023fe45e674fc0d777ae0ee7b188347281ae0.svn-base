<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="row">
<table class="table table-bordered">

    <thead>
    <tr>
        <th>供应商</th>
        <th>供应商单价</th>
        <th>币种</th>
      <!--  <th>最低采购量</th>
        <th>采购交期</th>-->
        <th>添加时间</th>
        <th>操作人</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($model as $v){?>
    <tr>
        <td><?=BaseServices::getSupplierName($v->suppliercode)?></td>
        <td><?=$v->supplierprice?></td>
        <td><?=$v->currency?></td>
       <!-- <td><?php /*$v->minimum_purchase_amount*/?></td>
        <td><?php /*$v->purchase_delivery*/?></td>-->
        <td><?=date('Y-m-d H:i:s',$v->add_time)?></td>
        <td><?=app\models\User::findIdentity($v->add_user)->username?></td>
    </tr>
    <?php }?>

    </tbody>

</table>
    </div>


