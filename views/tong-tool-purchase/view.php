<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>

<h4 class="modal-title">查看采购申请单日志</h4>
<div class="row">

    <table class="table table-bordered">

        <thead>
        <tr>
            <th>id</th>
            <th>内容</th>
            <th>源状态</th>
            <th>目标状态</th>
            <th>操作人</th>
            <th>时间</th>

        </tr>
        </thead>
        <tbody>
        <?php
        if($model){
            foreach($model as $v){
                ?>
                <tr>
                    <td><?=$v->id?></td>
                    <td><?=$v->content?></td>
                    <td><?=PurchaseOrderServices::getPayStatus($v->source_state)?></td>
                    <td><?=PurchaseOrderServices::getPayStatus($v->target_state)?></td>
                    <td><?=BaseServices::getEveryOne($v->operator)?></td>
                    <td><?=$v->create_time?></td>
                </tr>
            <?php }?>
        <?php }?>
        </tbody>

    </table>
</div>


