<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseReceive */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-receive-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pur_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'buyer')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'delivery_qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'presented_qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'receive_type')->dropDownList([ 'more' => 'More', 'less' => 'Less', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'handle_type')->dropDownList([ 'stop' => 'Stop', 'continue' => 'Continue', 'in' => 'In', 'return' => 'Return', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'handler')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'auditor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bearer')->dropDownList([ 'supplier' => 'Supplier', 'our' => 'Our', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'time_handle')->textInput() ?>

    <?= $form->field($model, 'time_audit')->textInput() ?>

    <?= $form->field($model, 'receive_status')->dropDownList([ 1 => '1', 2 => '2', 3 => '3', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'creator')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'note_handle')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'note_audit')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
