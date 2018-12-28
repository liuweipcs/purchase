<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SupervisorGroupBindSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="supervisor-group-bind-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'supervisor_id') ?>

    <?= $form->field($model, 'supervisor_name') ?>

    <?= $form->field($model, 'group_id') ?>

    <?= $form->field($model, 'creator_id') ?>

    <?php // echo $form->field($model, 'creator_name') ?>

    <?php // echo $form->field($model, 'editor_name') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'edit_time') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
