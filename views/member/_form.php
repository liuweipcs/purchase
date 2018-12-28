<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */


?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'user_number'); ?>
    <?php if(Yii::$app->controller->action->id == 'create'){?>
    <?= $form->field($model,'username')->textInput(['maxlength'=>true,'value'=>' ']);?>
    <?php }else{?>
    <?= $form->field($model,'username')->textInput(['maxlength'=>true,'value'=>$model->username]);?>
    <?php }?>
    <?= $form->field($model,'alias_name')->textInput(['maxlength'=>true]);?>
    <?php
    if($view=='create') {
       echo  $form->field($model, 'password_hash')->passwordInput();
    }
    ?>
    <?= $form->field($model, 'email'); ?>
    <?= $form->field($model, 'telephone'); ?>
    <?= $form->field($model, 'status')->dropDownList($model::Status());?>


    <div class="form-group">
        <?= Html::submitButton(Yii::$app->controller->action->id == 'create'? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
