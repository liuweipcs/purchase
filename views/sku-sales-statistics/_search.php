<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SkuSalesStatisticsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sku-sales-statistics-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'sku') ?>

    <?= $form->field($model, 'warehouse_code') ?>

    <?= $form->field($model, 'days_sales_3') ?>

    <?= $form->field($model, 'days_sales_7') ?>

    <?php // echo $form->field($model, 'days_sales_15') ?>

    <?php // echo $form->field($model, 'days_sales_30') ?>

    <?php // echo $form->field($model, 'days_sales_60') ?>

    <?php // echo $form->field($model, 'days_sales_90') ?>

    <?php // echo $form->field($model, 'statistics_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
