<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;
use app\models\OverseasDemandRule;
use app\services\PlatformSummaryServices;
?>
<style>
    input::-webkit-inner-spin-button{
        -webkit-appearance: none !important;
        margin: 0;
    }
    th{
        text-align: center;
    }
    td{
        padding-right: 1px;
    }
</style>
    <h4>编辑拦截规则</h4>
<?=  Html::a('添加规则',"#", ['class' => 'btn btn-primary add-rule']); ?>
<?=  Html::a('添加不拦截规则',"#", ['class' => 'btn btn-primary add-pass-rule']); ?>
    <div class="demand-rule-form">
        <?php $form = ActiveForm::begin(); ?>
        <?php if(!empty($model)){?>

    <table >
    <thead>
    <th>物流类型</th>
    <th>供应商开票方式</th>
    <th>金额范围（最小金额<）</th>
    <th>金额范围（<=最大金额）</th>
    <th>范围内最小金额限制</th>
    <th>操作</th>
    </thead>
    <tbody class="form-table">
            <?php
            $transport_list = \app\services\PurchaseOrderServices::getTransport();
            $transport_list = [0=>'全部','1' =>'空运','2' =>'海运','3' =>'铁路'] + $transport_list;
            foreach ($model as $key=>$value){
                ?>
        <tr>
            <td>
                <?= $form->field($value, 'transport[]')->dropDownList($transport_list,['required'=>'required','options' => [$value->transport => ['selected' => 'selected']]])->label('');?>
            </td>
            <td>
                <?= $form->field($value, 'supplier_invoice[]')->dropDownList([0=>'全部',1=>'否',2=>'增值税发票',3=>'普票'],['required'=>'required','options' => [$value->supplier_invoice => ['selected' => 'selected']]])->label('');?>
            </td>
            <td>
                <?= $form->field($value, 'min_money[]')->input('number',['required'=>'required','step'=>"0.001",'min'=>0,'value'=>$value->min_money])->label('');?>
            </td>
            <td>
                <?= $form->field($value, 'max_money[]')->input('number',['step'=>"0.001",'min'=>0,'value'=>$value->max_money])->label('');?>
            </td>
            <td>
                <?= $form->field($value, 'min_money_limit[]')->input('number',['step'=>"0.001",'min'=>0,'value'=>$value->min_money_limit])->label('');?>
            </td>
            <td>
                <?= Html::a('删除','#',['class'=>'delete-rule'])?>
            </td>
        </tr>
            <?php }?>
    </tbody>
    </table>
        <?php }else{ $value= new OverseasDemandRule();?>
<table >
    <thead>
    <th>物流类型</th>
    <th>供应商开票方式</th>
    <th>金额范围（最小金额<）</th>
    <th>金额范围（<=最大金额）</th>
    <th>范围内最小金额限制</th>
    <th>操作</th>
    </thead>
    <tbody class="form-table">
    <tr>
        <td>
            <?= $form->field($value, 'transport[]')->dropDownList([0=>'全部',1=>'空运',2=>'海运',3=>'铁路'],['required'=>'required'])->label('');?>
        </td>
        <td>
            <?= $form->field($value, 'supplier_invoice[]')->dropDownList([0=>'全部',1=>'否',2=>'增值税发票',3=>'普票'],['required'=>'required'])->label('');?>
        </td>
        <td>
            <?= $form->field($value, 'min_money[]')->input('number',['required'=>'required','step'=>"0.001",'min'=>0])->label('');?>
        </td>
        <td>
            <?= $form->field($value, 'max_money[]')->input('number',['step'=>"0.001",'min'=>0])->label('');?>
        </td>
        <td>
            <?= $form->field($value, 'min_money_limit[]')->input('number',['step'=>"0.001",'min'=>0])->label('');?>
        </td>
        <td>
            <?= Html::a('删除','#',['class'=>'delete-rule'])?>
        </td>
    </tr>
    </tbody>
</table>
            <div>
        <?php }?>
<h4>
    编辑不拦截规则
</h4>
<?php if(!empty($passModel)){?>

    <table >
        <thead>
        <th>物流类型</th>
        <th>采购仓</th>
        <th>操作</th>
        </thead>
        <tbody class="form-pass-table">
        <?php foreach ($passModel as $passKey=>$passValue){?>
            <tr>
                <td>
                    <?= $form->field($passValue, 'transport[]')->dropDownList([0=>'全部',1=>'空运',2=>'海运',3=>'铁路'],['required'=>'required','options' => [$passValue->transport => ['selected' => 'selected']]])->label('');?>
                </td>
                <td>
                    <?= $form->field($passValue,'warehouse_code[]')->dropDownList(\yii\helpers\ArrayHelper::merge(['all'=>'全部'],BaseServices::getWarehouseCode()),['required'=>'required','options' => [$passValue->warehouse_code => ['selected' => 'selected']]])->label('');?>
                </td>
                <td>
                    <?= Html::a('删除','#',['class'=>'delete-pass-rule'])?>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
<?php }else{ $passValue= new \app\models\OverseasDemandPassRule();?>
    <table >
        <thead>
        <th>物流类型</th>
        <th>采购仓</th>
        <th>操作</th>
        </thead>
        <tbody class="form-pass-table">
            <tr>
                <td>
                    <?= $form->field($passValue, 'transport[]')->dropDownList([0=>'全部',1=>'空运',2=>'海运',3=>'铁路'],['required'=>'required'])->label('');?>
                </td>
                <td>
                    <?= $form->field($passValue,'warehouse_code[]')->dropDownList(\yii\helpers\ArrayHelper::merge(['all'=>'全部'],BaseServices::getWarehouseCode()),['required'=>'required'])->label('');?>
                </td>
                <td>
                    <?= Html::a('删除','#',['class'=>'delete-pass-rule'])?>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
<?php }?>

        <div class="form-group" style="clear: both">
            <?= Html::submitButton( Yii::t('app', '提交'), ['class' =>'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php
$js=<<<JS
var click = 1000;
$(".add-rule").unbind("click").bind('click',function() {
  var clone = $('.form-table').find("tr").first().clone(true);
  clone.find('input').each(function() {
    $(this).val('');
  });
  clone.find('select').each(function() {
    $(this).val(0);
  });
  clone.appendTo("tbody.form-table");  
});
$(".add-pass-rule").unbind("click").bind('click',function() {
  var clone = $('.form-pass-table').find("tr").first().clone(true);
  clone.appendTo("tbody.form-pass-table");  
});

$('.delete-rule').unbind("click").bind('click',function() {
    var count =0;
    $('tbody.form-table').find('tr').each(function() {
      count++;
    });
    if(count<=1){
        alert('已是最后一条规则无法删除!');
    }else{ 
        $(this).closest('tr').remove();
    }
});
$('.delete-pass-rule').unbind("click").bind('click',function() {
    var count =0;
    $('tbody.form-pass-table').find('tr').each(function() {
      count++;
    });
    if(count<=1){
        alert('已是最后一条规则无法删除!');
    }else{ 
        $(this).closest('tr').remove();
    }
});

JS;
$this->registerJs($js);
?>