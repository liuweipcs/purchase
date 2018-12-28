<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderPay */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-pay-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <?= $form->field($model, 'pay_status')->textInput() ?>

    <?= $form->field($model, 'pur_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'requisition_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'settlement_method')->textInput() ?>

    <?= $form->field($model, 'pay_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'pay_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'notice')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'applicant')->textInput() ?>

    <?= $form->field($model, 'auditor')->textInput() ?>

    <?= $form->field($model, 'approver')->textInput() ?>

    <?= $form->field($model, 'application_time')->textInput() ?>

    <?= $form->field($model, 'review_time')->textInput() ?>

    <?= $form->field($model, 'processing_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
