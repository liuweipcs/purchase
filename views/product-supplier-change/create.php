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
                'id' => 'form-id',
            ]
        ); ?>
        <div class="col-md-12">温馨提示：请录入sku，以空格隔开</div>
        <div class="col-md-12"><?= $form->field($model, 'sku')->textarea(['rows'=>3,'cols'=>10,'placeholder' => "多个 SKU 请用空格隔开或换行",'style' => 'font-size:15px;'])->label(false) ?></div>

        <div class="col-md-3">请选择申请原因：</div>
        <div class="col-md-9"><?= $form->field($model,'apply_remark')->dropDownList(ProductSupplierChangeSearch::getApplyReasonList(),['style'=>"width:200px;"])->label(false) ?></div>

        <div class="col-md-12"><?= $form->field($model, 'other_apply_reason')->textarea(['rows'=>3,'cols'=>10,'placeholder' => "填写其他原因，不能超过10个汉字（请在上方列表里面选择其他）",'style' => 'font-size:15px;'])->label(false) ?></div>

        <div class="form-group clearfix" style="float: right">
            <?= Html::button('取消',['class' => 'btn btn-warning','id' => 'closes-show']) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?= Html::button('提交',['class' => 'btn btn-success','id' => 'do_save']) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php

$js = <<<JS
$(function() {
        
    $("#do_save").click(function(){        
        var sku = $("#productsupplierchangesearch-sku").val();
        var apply_remark = $("#productsupplierchangesearch-apply_remark").val();
        var other_apply_reason = $("#productsupplierchangesearch-other_apply_reason").val();
        
        
        if(sku == ''){
            layer.alert('请填写SKU');
            return false;
        }
        if(apply_remark == '100' && other_apply_reason == '' ){
            layer.alert('您选择申请原因为其他，请填写原因明细，<br/>不能超过10个汉字');
            return false;
        }
        if(getLength(other_apply_reason) > 20){
            layer.alert('其他原因不能超过10个汉字');
            return false;
        }
        
        $("#form-id").submit();        
    });
    
    
    $(document).on('click', '#closes-show', function () {
        $(".closes").click();
    });
    
    function getLength(str){
        var jmz = {};
        jmz.GetLength = function(str) {
          return str.replace(/[\u0391-\uFFE5]/g,"aa").length;  //先把中文替换成两个字节的英文，在计算长度
        };
        return jmz.GetLength(str);
    } 
    
});
JS;
$this->registerJs($js);
?>