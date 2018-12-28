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
    <div class="col-md-2"><?= $form->field($model, 'sku') ?></div>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('查询', ['class' => 'btn btn-success']) ?>
        <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>