<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\services\SupplierServices;

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
    <div class="col-md-1"><?=$form->field($model, "status")->dropDownList(SupplierServices::getSupplierStatus(''),['class' => 'form-control','prompt'=>'请选择'])?></div>
    <div class="col-md-1"><?=$form->field($model, "supplier_settlement")->dropDownList(SupplierServices::getSettlementMethod(),['class' => 'form-control pay_method','prompt' => '请选择'])?></div>
    <div class="col-md-1"><?= $form->field($model, 'supplier_code')->textInput(['placeholder'=>'如A001']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'supplier_name')->textInput(['placeholder'=>'支持模糊查询']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'first_line')->dropDownList(BaseServices::getProductLineList(0),['onclick'=>
                                '$.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                                $(".second").html(data);
                                });'])->label('一级产品线') ?></div>
    <div class="col-md-2"><?= $form->field($model, 'second_line')->dropDownList(BaseServices::getProductLineList($model->first_line),['class'=>'form-control second','onclick'=>
                                '$.post("'.yii::$app->urlManager->createUrl('supplier/line').'?pid="+$(this).val(),function(data){
                                $(".third").html(data);
                                });'])->label('二级产品线') ?></div>
    <div class="col-md-2"><?= $form->field($model, 'third_line')->dropDownList(BaseServices::getProductLineList($model->second_line),['class'=>'form-control third'])->label('三级级产品线') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'default_buyer')->widget(Select2::classname(), [
    'options' => ['placeholder' => '请输入采购员 ...'],
    'data' =>BaseServices::getBuyer('name'),
    'pluginOptions' => [
        'language' => [
            'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
    ],])->label('采购员')?></div>
    <div class="col-md-1"><?= $form->field($model, 'create_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '创建人 ...'],
            'data' =>BaseServices::getEveryOne(),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),],
            ],])->label('创建人')?></div>
    <div class="col-md-2">
        <?=$form->field($model, "sul.audit_status")->dropDownList(SupplierServices::getAuditStatus(),['class' => 'form-control pay_method','prompt' => '请选择'])->label('审核状态')?>
    </div>
    <div class="col-md-2">
        <?=$form->field($model, "source")->dropDownList([1=>'erp',2=>'采购系统',3=>'通途系统'],['class' => 'form-control ','prompt' => '请选择'])->label('来源')?>
    </div>
    <div class="col-md-1"><!--供应商搜索-->
        <?= $form->field($model, 'order_type')->dropDownList(['1'=>'FBA'], ['prompt' => '请选择'])->label('供应商搜索')?>
    </div>
    <div class="col-md-1">
        <?php
            $suppliers = SupplierServices::getSupplierLevel();
            $suppliers[0] = '请选择';
        ?>
        <?=$form->field($model, "supplier_level")->dropDownList($suppliers)->label('供应商等级')?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝') ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
