<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseAbnomalSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-abnomal-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1"><?= $form->field($model, 'express_no') ?></div>

    <div class="col-md-1"><?=$form->field($model, 'status')->dropDownList(Yii::$app->params['status'], ['prompt'=>'请选择']) ?></div>

    <?php // echo $form->field($model, 'is_del') ?>

    <?php // echo $form->field($model, 'note') ?>

    <?php // echo $form->field($model, 'create_user') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'update_time') ?>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
