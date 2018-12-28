<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pur_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pur_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'shipping_method')->dropDownList([ 1 => '1', 2 => '2', 3 => '3', 4 => '4', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'operation_type')->dropDownList([ 1 => '1', 2 => '2', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'creator')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'account_type')->dropDownList([ 1 => '1', 2 => '2', 3 => '3', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'pay_type')->dropDownList([ 1 => '1', 2 => '2', 3 => '3', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'currency_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pay_ship_amount')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_eta')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
