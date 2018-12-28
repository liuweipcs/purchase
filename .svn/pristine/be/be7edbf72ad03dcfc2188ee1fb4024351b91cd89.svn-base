<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTrackingSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="overseas-warehouse-goods-tracking-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'owarehouse_name') ?>

    <?= $form->field($model, 'sku') ?>

    <?= $form->field($model, 'purchase_order_no') ?>

    <?= $form->field($model, 'buyer') ?>

    <?php // echo $form->field($model, 'state') ?>

    <?php // echo $form->field($model, 'countdown_days') ?>

    <?php // echo $form->field($model, 'financial_payment_time') ?>

    <?php // echo $form->field($model, 'product_arrival_time') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
