<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\GroupAuditConfigSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="group-audit-config-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'group') ?>

    <?= $form->field($model, 'values') ?>

    <?= $form->field($model, 'remark') ?>

    <?php // echo $form->field($model, 'uid') ?>

    <?php // echo $form->field($model, 'cdate') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
