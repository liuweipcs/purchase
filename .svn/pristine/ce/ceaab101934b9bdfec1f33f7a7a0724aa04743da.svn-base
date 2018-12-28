<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseUser */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
        if($model->pur_user_id){
           echo $form->field($model, 'pur_user_name')->textInput(['style'=>'width:200px','readonly'=>true])->label('用户名');
        }else{
           echo $form->field($model, 'pur_user_id')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请选择'],
                'data'=>BaseServices::getEveryOne(),
                'pluginOptions' => ['width'=>'200px'],
            ])->label('用户名');
        }
    ?>

    <?= $form->field($model, 'group_id')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选择'],
        'data'=>PurchaseOrderServices::getPurchaseGroup(),
        'pluginOptions' => ['width'=>'200px'],
    ]) ?>

    <?= $form->field($model, 'grade')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选择'],
        'data'=>Yii::$app->params['grade'],
        'pluginOptions' => ['width'=>'200px'],
    ]) ?>

    <?= $form->field($model, 'type')->widget(Select2::classname(), [
        //'options' => ['placeholder' => '请选择'],
        'data'=>\app\models\PurchaseUser::getUserType(),
        'pluginOptions' => ['width'=>'200px'],
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
