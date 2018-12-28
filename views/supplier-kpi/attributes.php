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
        'enableAjaxValidation' => true,
        'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<h3 class="">修改供应商属性</h3>
<div class="row">

    <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
    <div class="col-md-4"><?= $form->field($model, 'main_category')->dropDownList(\app\services\BaseServices::getCategory(),['prompt' => '--请选择--',]) ?></div>

    <div class="col-md-4"><?= $form->field($model, 'supplier_level')->dropDownList(\app\services\SupplierServices::getSupplierLevel(),['prompt' => '--请选择--',])  ?></div>
    <div class="col-md-4"><?= $form->field($model, 'cooperation_type')->dropDownList(\app\services\SupplierServices::getCooperation(),['prompt' => '--请选择--',])  ?></div>

    <div class="col-md-4"><?= $form->field($model, 'supplier_type')->dropDownList(\app\services\SupplierServices::getSupplierType(),['prompt' => '--请选择--',]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'buyer')->dropDownList(\app\services\SupplierServices::getEveryOne(),['prompt' => '--请选择--',]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'merchandiser')->dropDownList(\app\services\SupplierServices::getEveryOne(),['prompt' => '--请选择--',]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'supplier_settlement')->dropDownList(\app\services\SupplierServices::getSettlementMethod(),['prompt' => '--请选择--',]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'payment_cycle')->dropDownList(\app\services\SupplierServices::getPaymentCycle() , ['prompt' => '--请选择--',]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'purchase_amount')->textInput(['placeholder'=>'RMB']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'commission_ratio')->textInput(['placeholder'=>'%'])?></div>
    <div class="col-md-4"><?= $form->field($model, 'contract_notice')->textarea(['cols'=>40,'rows'=>5])->label('备注：')?></div>


</div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>


    <?php ActiveForm::end(); ?>



