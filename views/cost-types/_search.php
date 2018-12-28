<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CostTypesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cost-types-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <div class="col-md-1"><?= $form->field($model, 'cost_code') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'cost_en') ?></div>



    <?php // echo $form->field($model, 'create_id') ?>

    <?php // echo $form->field($model, 'update_id') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'update_time') ?>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
