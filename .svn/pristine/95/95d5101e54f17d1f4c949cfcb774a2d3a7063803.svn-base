<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderItems */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-items-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pur_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ctq')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rqy')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cty')->textInput() ?>

    <?= $form->field($model, 'sales_status')->textInput() ?>

    <?= $form->field($model, 'e_ctq')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'e_price')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
