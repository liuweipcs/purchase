<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SkuSalesStatistics */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sku-sales-statistics-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_3')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_7')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_15')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_30')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_60')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_90')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'statistics_date')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
