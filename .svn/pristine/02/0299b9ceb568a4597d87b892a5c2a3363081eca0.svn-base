<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;

/* @var $this yii\web\View */
/* @var $model app\models\BankCardManagementSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bank-card-management-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>



    <div class="col-md-1"><?= $form->field($model, 'branch') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'account_holder') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'account_abbreviation') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'payment_types')->dropDownList(PurchaseOrderServices::getPaymentTypes(),['prompt'=>'请选择']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'account_sign')->dropDownList(PurchaseOrderServices::getAccountSign(),['prompt'=>'请选择']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'status')->dropDownList(['1'=>'可用','2'=>'不可用'],['prompt'=>'请选择']) ?></div>


    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
