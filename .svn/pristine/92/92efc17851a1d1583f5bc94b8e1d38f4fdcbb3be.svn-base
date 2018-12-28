<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseQc */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-qc-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'express_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pur_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'buyer')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'handle_type')->dropDownList([ 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'delivery_qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'presented_qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'good_products_qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bad_products_qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'check_type')->textInput() ?>

    <?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'creator')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'time_handle')->textInput() ?>

    <?= $form->field($model, 'handler')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'time_audit')->textInput() ?>

    <?= $form->field($model, 'auditor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'note_audit')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
