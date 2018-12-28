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
                'id' => 'audit-form-id',
            ]
        ); ?>
        <div class="col-md-12">温馨提示：驳回需要填写原因</div>
        <div class="col-md-12"><?= $form->field($model, 'apply_remark')->textarea(['rows'=>3,'cols'=>8,'placeholder' => "驳回需要填写"])->label(false) ?></div>

        <div class="col-md-12"><?= $form->field($model,'sku')->input('text',['style' => 'display:none','value' => $sku])->label(false) ?></div>
        <div class="col-md-12"><?= $form->field($model,'status')->input('text',['style' => 'display:none','value' => 20])->label(false) ?></div>

        <div class="col-md-12"><br/></div>
        <div class="form-group clearfix" style="text-align: center">
            <?= Html::button('审核通过',['class' => 'btn btn-success','id' => 'audit_success']) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::button('驳回',['class' => 'btn btn-warning','id' => 'audit_fail']) ?>
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
        var apply_remark = $("#productsupplierchangesearch-apply_remark").val();
        if(apply_remark == ''){
            layer.alert('请填写驳回原因');
            return false;
        }
        $("#productsupplierchangesearch-status").val(70);
        
        $("#audit-form-id").submit(); 
    });    
    
    $(document).on('click', '#closes-show', function () {
        $(".closes").click();
    });
    
});
JS;
$this->registerJs($js);
?>