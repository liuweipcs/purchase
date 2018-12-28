<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\OverseasWarehouseGoodsTracking */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="overseas-warehouse-goods-tracking-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'owarehouse_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'purchase_order_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'buyer')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'state')->textInput() ?>

    <?= $form->field($model, 'countdown_days')->textInput() ?>

    <?= $form->field($model, 'financial_payment_time')->textInput() ?>

    <?= $form->field($model, 'product_arrival_time')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
