<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CostTypes */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cost-types-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-3"><?= $form->field($model, 'cost_code')->textInput(['maxlength' => true]) ?></div>

    <div class="col-md-3"> <?= $form->field($model, 'cost_en')->textInput(['maxlength' => true]) ?></div>

    <div class="col-md-3"><?= $form->field($model, 'cost_cn')->textInput(['maxlength' => true]) ?></div>

    <div class="col-md-3"><?= $form->field($model, 'notice')->textInput(['maxlength' => true]) ?></div>



    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
