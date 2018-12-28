<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseReceiveSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-receive-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'supplier_code') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'supplier_name') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'buyer') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'receive_status')->dropDownList(['2'=>'未审核','3'=>'已审核']) ?></div>

    <?php // echo $form->field($model, 'sku') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'delivery_qty') ?>

    <?php // echo $form->field($model, 'presented_qty') ?>

    <?php // echo $form->field($model, 'receive_type') ?>

    <?php // echo $form->field($model, 'handle_type') ?>

    <?php // echo $form->field($model, 'handler') ?>

    <?php // echo $form->field($model, 'auditor') ?>

    <?php // echo $form->field($model, 'bearer') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'time_handle') ?>

    <?php // echo $form->field($model, 'time_audit') ?>

    <?php // echo $form->field($model, 'receive_status') ?>

    <?php // echo $form->field($model, 'creator') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'note') ?>

    <?php // echo $form->field($model, 'note_handle') ?>

    <?php // echo $form->field($model, 'note_audit') ?>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
