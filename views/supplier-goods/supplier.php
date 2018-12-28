<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
$model->id=$id;
?>



<?php $form = ActiveForm::begin([
        'id' => 'form-id',
        //'enableAjaxValidation' => true,
        'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<h3 class="">修改默认供应商</h3>
<div class="row">

    <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
    <div class="col-md-4"><?= $form->field($model, 'suppliercode')->textInput(['required'=>true,'placeholder'=>'必填项']) ?></div>

    <div class="col-md-4"><?= $form->field($model, 'supplierprice')->textInput(['placeholder'=>'不修改,请不要填写'])  ?></div>
    <div class="col-md-4"><?= $form->field($model, 'currency')->dropDownList(\app\services\SupplierGoodsServices::getCurrency())  ?></div>

    <div class="col-md-4"><?= $form->field($model, 'purchase_delivery')->textInput(['placeholder'=>'不修改,请不要填写']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'minimum_purchase_amount')->textInput(['placeholder'=>'不修改,请不要填写']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'default_buyer')->dropDownList(\app\services\SupplierServices::getEveryOne(),['prompt' => '--请选择--',]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'supplier_product_address')->textInput(['placeholder'=>'不修改,请不要填写']) ?></div>

</div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>


    <?php ActiveForm::end(); ?>



