<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ExchangeGoodsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="exchange-goods-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'pur_number') ?>

    <?= $form->field($model, 'supplier_code') ?>

    <?= $form->field($model, 'supplier_name') ?>

    <?= $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'sku') ?>

    <?php // echo $form->field($model, 'pro_name') ?>

    <?php // echo $form->field($model, 'create_user') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'buyer') ?>

    <?php // echo $form->field($model, 'state') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
