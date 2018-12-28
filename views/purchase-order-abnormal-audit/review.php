<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use app\models\User;
use kartik\tabs\TabsX;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

?>
<div class="stockin-view">


    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrder[purchas_status]" value="" style="display:none">
    <input type="hidden" id="purchaseorder-id" class="form-control" name="PurchaseOrder[id]" value="<?=$model->id?>" style="display:none">
    <h4 class="modal-title">审核采购单</h4>
    <div class="row">
        <div class="col-md-4"><?= $form->field($model, 'pur_number')->textInput(['maxlength' => true, 'disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'warehouse_code')->textInput(['disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'shipping_method')->textInput(['disabled'=>'disabled','value'=>PurchaseOrderServices::getShippingMethod($model->shipping_method)]) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'supplier_code')->textInput(['disabled'=>'disabled','value'=>$model->supplier_name])->label('供应商') ?></div>
        <div class="col-md-4"><?= $form->field($model, 'created_at')->textInput(['disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'date_eta')->textInput(['disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'creator')->textInput(['maxlength' => true,'disabled'=>'disabled']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'buyer')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>!empty($model->buyer)?BaseServices::getEveryOne($model->buyer):'']) ?></div>
        <div class="col-md-4"><?= $form->field($model, 'pur_type')->textInput(['maxlength' => true,'disabled'=>'disabled','value'=>!empty($model->pur_type)?PurchaseOrderServices::getPurType($model->pur_type):'']) ?></div>

        <!--<div class="col-md-4"><?php /*$form->field($model, 'account_type')->textInput(['maxlength' => true,'disabled'=>'disabled'])->label('异常提醒')*/?></div>-->

    </div>
    <?php
    $items = [
        [
            'label'=>'<span class="glyphicon glyphicon-star" aria-hidden="true"></span>采购产品',
            'content'=>$this->render('_product',['purchaseOrderItems'=>$model->purchaseOrderItems,'code'=>$model->warehouse_code,'codes'=>$model->supplier_code]),

        ],
    ];

    echo TabsX::widget([
        'items'=>$items,
        'position'=>TabsX::POS_ABOVE,
        'encodeLabels'=>false
    ]);?>
    <div class="col-md-12"><?= $form->field($model, 'audit_note')->textarea(['cols'=>'5','rows'=>3,'maxlength' => true,'placeholder'=>'审批不通过请填写原因'])->label('备注')?></div>
    <div class="form-group">
        <?= Html::submitButton('审批通过(Ok)',['class' => 'btn btn-success']) ?>
        <?= Html::submitButton('审批不通过(Rollback)', ['class' => 'btn btn-warning']) ?>
        <?php if($name =='audit'){?>
        <?= Html::submitButton('提交复审(Re-examine)', ['class' => 'btn btn-info']) ?>
        <?php }?>

    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php


$js = <<<JS
$(function(){
    $(document).on('click', '.btn-success', function () {
        $('#purchaseorder-purchas_status').attr('value','3');


    });
     $(document).on('click', '.btn-warning', function () {
        $('#purchaseorder-purchas_status').attr('value','4');
    });
     $(document).on('click', '.btn-info', function () {
        $('#purchaseorder-purchas_status').attr('value','5');
    });

});


JS;
$this->registerJs($js);
?>
