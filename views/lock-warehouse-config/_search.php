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

    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('查询', ['class' => 'btn btn-success']) ?>
        <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?php
        //echo Html::button('新增', ['class' => 'btn btn-success create']);
        echo Html::a('新增', ['create'], ['class' => 'btn btn-success create', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
        ?>
        <?php
        echo Html::a('导入', ['import'], ['class' => 'btn btn-success import', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
        ?>
        <?php
        echo Html::a('删除', '#', ['class' => 'btn btn-danger delete']);
        ?>

    </div>

    <?php ActiveForm::end(); ?>

</div>