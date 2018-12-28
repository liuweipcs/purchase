<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseUserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'pur_user_id') ?>

    <?= $form->field($model, 'pur_user_name') ?>

    <?= $form->field($model, 'group_id') ?>

    <?= $form->field($model, 'grade') ?>

    <?php // echo $form->field($model, 'crate_time') ?>

    <?php // echo $form->field($model, 'edit_time') ?>

    <?php // echo $form->field($model, 'creator') ?>

    <?php // echo $form->field($model, 'editor') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
