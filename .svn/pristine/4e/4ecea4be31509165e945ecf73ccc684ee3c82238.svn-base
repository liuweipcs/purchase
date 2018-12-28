<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\StockinSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    .col-md-1{
        padding-left: 0px;
    }
</style>
<div class="stockin-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1"><?=$form->field($model, "product_status")->dropDownList(\app\services\SupplierGoodsServices::getProductStatus(),['class' => 'form-control','prompt' => '请选择'])?></div>


    <div class="col-md-1"><?= $form->field($model, 'sku')->textInput(['placeholder'=>'']) ?></div>
<!--    <div class="col-md-1">--><?php //$form->field($model, 'quotes.suppliercode')->dropDownList(\app\services\BaseServices::getSupplier(), ['prompt' => 'please choose'])->label('供应商代码') ?><!--</div>-->
  <!--  <div class="col-md-1"><?/*= $form->field($model, 'buyer')->dropDownList(\app\services\BaseServices::getEveryOne(), ['prompt' => 'please choose'])->label('默认采购员') */?></div>-->




    <div class="col-md-1"><?= $form->field($model, 'product_category_id')->dropDownList(\app\services\BaseServices::getCategory(), ['prompt' => '请选择']) ?></div>


    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
