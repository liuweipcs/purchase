<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
?>
    <style type="text/css">
        .modal-lg{width: 600px; !important;}
    </style>
    <div class="purchase-order-form" >
        <?php $form = ActiveForm::begin([
                'id' => 'audit-form-id',
            ]
        ); ?>
        <div class="col-md-12">温馨提示：审核不通过需要填写原因</div>
        <div class="col-md-12"><?= $form->field($model, 'contract_notice')->textarea(['rows'=>3,'cols'=>8,'placeholder' => "审核不通过需要填写"])->label(false) ?></div>

        <div class="col-md-12"><?= $form->field($model,'id')->input('text',['style' => 'display:none','value' => $id])->label(false) ?></div>
        <div class="col-md-12"><?= $form->field($model,'status')->input('text',['style' => 'display:none','value' => 1])->label(false) ?></div>

        <div class="col-md-12"><br/></div>
        <div class="form-group clearfix" style="text-align: center">
            <?= Html::button('审核通过',['class' => 'btn btn-success','id' => 'audit_success']) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::button('审核不通过',['class' => 'btn btn-warning','id' => 'audit_fail']) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php

$js = <<<JS
$(function() {
    $(document).on('click', '#audit_success', function () {
        $("#audit-form-id").submit();       
    });
    
    $(document).on('click', '#audit_fail', function () {
        var contract_notice = $("#supplier-contract_notice").val();
        if(contract_notice == ''){
            layer.alert('请填写驳回原因');
            return false;
        }
        $("#supplier-status").val(5);
        
        $("#audit-form-id").submit(); 
    });    
    
    $(document).on('click', '#closes-show', function () {
        $(".closes").click();
    });
    
});
JS;
$this->registerJs($js);
?>