<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\ProductSupplierChangeSearch;

/* @var $this yii\web\View */
?>
    <style type="text/css">
        .modal-lg{width: 600px; !important;}
    </style>

    <div class="purchase-order-form" >
        <?php $form = ActiveForm::begin([
                'id' => 'affirm-form-id',
            ]
        ); ?>
        <div class="col-md-12" style="text-align: center;font-size: 25px;margin-bottom: 20px;"><span style="text-align: center;font-size: 25px;">确认替换供应商</span></div>
        <div class="col-md-12" style="text-align: center;">
            <span><?= $form->field($model, 'apply_remark')->textarea(['rows' => 3,'cols' => 5,'placeholder' => "采购驳回必填备注原因",'style' => 'font-size:20px;'])->label(false) ?></span>
        </div>

        <div class="col-md-12"><?= $form->field($model,'sku')->input('text',['style' => 'display:none','value' => $sku])->label(false) ?></div>
        <div class="col-md-12"><?= $form->field($model,'status')->input('text',['style' => 'display:none','value' => 60])->label(false) ?></div>

        <div class="col-md-12"><br/></div>
        <div class="form-group clearfix" style="text-align: center">
            <?= Html::button('&nbsp;&nbsp;&nbsp;确认&nbsp;&nbsp;&nbsp;',['class' => 'btn btn-danger btn-lg','id' => 'audit_success']) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::button('&nbsp;&nbsp;&nbsp;驳回&nbsp;&nbsp;&nbsp;',['class' => 'btn btn-default btn-lg','id' => 'audit_fail']) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php

$js = <<<JS
$(function() {
    $(document).on('click', '#audit_success', function () {
        $("#affirm-form-id").submit();       
    });
    
    $(document).on('click', '#audit_fail', function () {
        var apply_remark = $("#productsupplierchangesearch-apply_remark").val();
        if(apply_remark == ''){
            layer.alert('请填写驳回原因');
            return false;
        }
        $("#productsupplierchangesearch-status").val(70);
        
        $("#affirm-form-id").submit(); 
    });
    
    $(document).on('click', '#closes-show', function () {
        $(".closes").click();
    });
    
});
JS;
$this->registerJs($js);
?>