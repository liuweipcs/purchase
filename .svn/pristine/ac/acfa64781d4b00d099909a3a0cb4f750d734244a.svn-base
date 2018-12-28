<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SkuSingleTacticMainSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sku-single-tactic-main-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <div class="col-md-1"><?= $form->field($model, 'sku') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'warehouse')->dropDownList(\app\services\BaseServices::getWarehouseCode(),['prompt' => '请选择']) ?></div>



    <?php // echo $form->field($model, 'user') ?>

    <?php // echo $form->field($model, 'create_date') ?>

    <?php // echo $form->field($model, 'status') ?>

    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
