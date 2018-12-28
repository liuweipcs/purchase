<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\WarehouseSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="warehouse-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',

    ]); ?>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_name') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_type')->dropDownList(Yii::$app->params['warehouse'],['prompt' => 'please choose']) ?></div>

        <div class="col-md-1"><?= $form->field($model, 'warehouse_code') ?></div>




    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('查询', ['class' => 'btn btn-success']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>