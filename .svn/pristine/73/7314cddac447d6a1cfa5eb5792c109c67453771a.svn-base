<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Warehouse */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="warehouse-form clearfix">

    <?php $form = ActiveForm::begin(); ?>
    <div class="col-md-4">
        <?= $form->field($model, 'warehouse_name')->textInput() ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'warehouse_type')->dropDownList([1 => '本地仓', 2 => '海外仓',3=>'第三方仓库'],['prompt' => '请选仓库']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'is_custody')->dropDownList([0 => '否', 1 => '是']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'use_status')->dropDownList([0 => '否', 1 => '是']) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="form-group col-md-12">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
