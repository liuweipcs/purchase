<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use \yii\bootstrap\Modal;
use kartik\date\DatePicker;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>
<style>
    .supplier-table {
        border: 1px solid #ccc;
        border-collapse: collapse;
        background-color: white;
    }
    .supplier-table th,.supplier-table td {
        text-align: left;
        border: 1px solid #ccc;
    }
    .supplier-table .form {
        padding: 0 1% 0 1%
    }
</style>

<div class="row">
<?php $form = ActiveForm::begin(['enableClientValidation'=>false,'id'=>'check_form']); ?>
<table class="supplier-table" width="95%">
    <tbody>
        <tr>
            <td width="10%"></td>
            <td width="10%">类型</td>
            <td width="10%" class="form">
                <?= $form->field($model,'check_type')->radioList([1=>'验厂',2=>'验货'])->label('');?>
                <?= $form->field($model,'check_code')->hiddenInput()->label(false);?>
            </td>
            <td width="5%">部门</td>
            <td width="10%"><?= $form->field($model,'group')->radioList([1=>'国内仓',2=>'海外仓',3=>'FBA'])->label('');?></td>
            <td width="5%">期望时间</td>
            <td width="10%" colspan="2">
                
                <?php 
                $time = !empty($model->expect_time) ? date('Y-m-d',strtotime($model->expect_time)) : date('Y-m-d',time());

                echo DatePicker::widget([
                    'name' => 'SupplierCheck[expect_time]',
                    'options' => ['placeholder' => true,],
                    //注意，该方法更新的时候你需要指定value值
                    'value' => $time,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]);?>

            </td>
        </tr>
        <tr>
            <td></td>
            <td>供应商名称</td>
            <td class="form">
                <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
                    'options' => [],
                    'disabled'=>$model->isNewRecord ? false : true,
                    'pluginOptions' => [
                        'placeholder' => '选择系统内供应商',
                        'allowClear' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                        ],
                        'ajax' => [
                            'url' => $url,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term,status:1}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(res) { return res.text; }'),
                        'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                    ],
                ])->label('');
                ?>
            </td>
            <td colspan="2" class="form">
                <?= $form->field($model, 'supplier_name')->textInput( ['placeholder'=>'非系统内供应商(可选)','readonly'=>$model->isNewRecord ? false : true])->label('')?>
            </td>
            <td>次数</td>
            <td class="form" colspan="2">
                <?= $form->field($model, 'check_times')->textInput( ['readonly'=> true])->label('')?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>联系人</td>
            <td class="form"><?= $form->field($model, 'contact_person')->textInput( ['placeholder'=>'联系人','maxlength' => true])->label('') ?></td>
            <td>联系方式</td>
            <td class="form"><?= $form->field($model, 'phone_number')->textInput(['placeholder'=>'联系电话','maxlength' => true])->label('') ?></td>
            <td>联系地址</td>
            <td class="form" colspan="2"><?= $form->field($model, 'contact_address')->textInput(['placeholder'=>'联系地址','maxlength' => true])->label('') ?></td>
        </tr>
        <tr>
            <td></td>
            <td>是否加急</td>
            <td colspan="6" class="form">
                <?=$form->field($model,'is_urgent')->radioList([1=>'是',0=>'否'],['value'=>0])->label('')?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td>申请备注</td>
            <td colspan="6" class="form"><?=$form->field($model, 'check_reason')->textInput(['placeholder'=>'请输入申请备注','required'=>true])->label('')?></td>
        </tr>
        <tr class="hiddentr" style="display:<?=$model->check_type==2 ? '' : 'none' ?>">
            <td></td>
            <td>订单来源</td>
            <td>
                <?=$form->field($model,'order_type')->radioList([1=>'采购系统',2=>'非采购系统'])->label('')?>
            </td>
            <td>PO号</td>
            <td class="form" colspan="4">
                <?=$form->field($model, 'pur_number')->textInput(['placeholder'=>'多个PO号用多个逗号隔开','id'=>'pur_number','check_id'=>$model->isNewRecord ? '' : $model->id])->label('')?>
            </td>
        </tr>
        <tr class="hiddentr" style="display:<?=$model->check_type==2 ? '' : 'none' ?>" >

            <td>SKU</td>
            <td>采购数量</td>
            <td>执行标准</td>
            <td>抽检数量</td>
            <td>合格质量标准</td>
            <td width="5%">客诉率</td>
            <td>客诉问题点</td>
            <td>操作</td>
        </tr>
    <?php if(!empty($model->checkPur)){ foreach ($model->checkPur as $key=>$sku){?>
        <tr class=<?=$model->order_type==2?'addsku':'append'?>>
            <div class='form-group'><input class="<?=$model->order_type==2?'form-control type':'form-control append-type'?>"  type='hidden' name=<?="SupplierCheck[items][$key][type]"?> value="<?= $sku->type?>"></div>
            <td class='form'>
                <div class='form-group'><label></label>
                    <input class="<?=$model->order_type==2?'form-control sku':'form-control'?>" <?=$model->order_type==1?"readonly='readonly'":''?> name=<?="SupplierCheck[items][$key][sku]"?> type='text' value=<?= $sku->sku?>>
                </div>
            </td>
            <td class='form'>
                <div class='form-group'><label></label>
                    <input class="<?=$model->order_type==2?'form-control purchase_num':'form-control'?>" <?=$model->order_type==1?"readonly='readonly'":''?> name=<?="SupplierCheck[items][$key][purchase_num]"?> type='text' value=<?= $sku->purchase_num?>>
                </div>
            </td>
            <td class='form'>
                <div class='form-group'><label></label>
                    <input class="<?=$model->order_type==2?'form-control check_standard':'form-control'?>"  readonly='readonly' name=<?="SupplierCheck[items][$key][check_standard]"?>  type='text' value="<?= $sku->check_standard?>">
                </div>
            </td>
            <td class='form'>
                <div class='form-group'><label></label>
                    <input class="<?=$model->order_type==2?'form-control check_num':'form-control'?>"  readonly='readonly' name=<?="SupplierCheck[items][$key][check_num]"?> type='number' value="<?=$sku->check_num?>">
                </div>
            </td>
            <td class='form'>
                <div class='form-group'><label></label>
                    <input class="<?=$model->order_type==2?'form-control check_rate':'form-control'?>"  readonly='readonly' name=<?="SupplierCheck[items][$key][check_rate]"?> type='text' value="<?=$sku->check_rate?>">
                </div>
            </td>
            <td class='form'>
                <div class='form-group'><label></label>
                    <input class='form-control' name=<?="SupplierCheck[items][$key][complaint_rate]"?> type='number' step='0.001' value="<?=$sku->complaint_rate?>">
                </div>
            </td>
            <td class='form'>
                <div class='form-group'><label></label>
                    <input class='form-control' name=<?="SupplierCheck[items][$key][complaint_point]"?> type='text' value="<?=$sku->complaint_point?>">
                </div>
            </td>
            <?php if($model->order_type==2){?>
                <td class="form"><a href="#" class="sku_delete">删除</a></td>
            <?php }else{?>
                <td></td>
            <?php } ?>
        </tr>
    <?php }}?>
    </tbody>
</table>

<div>
    <?= Html::button($model->isNewRecord ? Yii::t('app', '添加') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success submit' : 'btn btn-primary submit']) ?>
</div>

<?php ActiveForm::end(); ?>
</div>
<?php
Modal::begin([
    'id' => 'check-form',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();
$checkTimes = Url::toRoute(['check-times']);
$checkPur = Url::toRoute(['check-pur']);
$getinfo = Url::toRoute(['get-supplier-info']);
$checkUrl = Url::toRoute(['check-form']);
$getUrl = Url::toRoute(['get-standard']);
$js = <<<JS

    function checkTimes(data,type,checktype) {
    var check_code = $('[name="SupplierCheck[check_code]"]').val();
      $.ajax({
            url:'{$checkTimes}',
            data:{supplier_code:data,type:type,check_type:checktype,check_code:check_code},
            type:'get',
            dataType:'json',
            success:function(data) {
                if(data.status=='success'){
                    $('#suppliercheck-check_times').val(data.times);
                }
            }
        });  
    }
    function checkInfo(suppliercode) {
      $.ajax({
            url:'{$getinfo}',
            data:{supplier_code:suppliercode},
            type:'get',
            dataType:'json',
            success:function(data) {
                $('#suppliercheck-contact_person').val(data.contact_person);
                $('#suppliercheck-phone_number').val(data.contact_number);
                $('#suppliercheck-contact_address').val(data.chinese_contact_address);
            }
        });
    }
    
    function checkPur() {
      var purNumber = $('#pur_number').val();
      var supplier_code = $('#suppliercheck-supplier_code').val();
      var check_id  = $('#pur_number').attr('check_id');
      $.ajax({
        url:'{$checkPur}',
        data:{pur_number:purNumber,supplier_code:supplier_code,check_id:check_id},
        type:'get',
        dataType:'json',
        success:function(data) {
          if(data.status == 'success'){
              $('.supplier-table .append').remove();
              $('.supplier-table').append(data.data);
          }else {
              layer.msg(data.message);
              $('.supplier-table .append').remove();
          }
           $('.addsku').remove();
        }
      });
    }
    $('#pur_number').bind('blur',function () {
        var ordertype = false;
        $('[name="SupplierCheck[order_type]"]').each(function() {
            if($(this).is(':checked')){
                ordertype = $(this).val();
            }
        });
        if(!ordertype){
            alert('请先选择订单类型!');
            return false;
        }
        if(ordertype==1){
            checkPur();
        }
    });
    $('#suppliercheck-supplier_code').on('select2:select',function(e) {
        var checktype = false;
        $('[name="SupplierCheck[check_type]"]').each(function() {
            if($(this).is(':checked')){
                checktype = $(this).val();
            }
        });
        $('#suppliercheck-supplier_name').prop('readonly',true);
        if(!checktype){
            alert('请先选择类型!');
            return false;
        }
        checkInfo(e.params.data.id);
        checkTimes(e.params.data.id,'code',checktype);
    });


$('#suppliercheck-supplier_code').on('select2:unselect',function(e) {
    $('#suppliercheck-supplier_name').prop('readonly',false);
    checkInfo('');
});

$('#suppliercheck-supplier_name').on('blur',function(e) {
    var name  = $(this).val();
    var checktype = false;
    $('[name="SupplierCheck[check_type]"]').each(function() {
        if($(this).is(':checked')){
            checktype = $(this).val();
        }
    });
    if(!checktype){
            alert('请先选择类型!');
            return false;
        }
    if(name != ''){
        $('#suppliercheck-supplier_code').prop('disabled',true);
        checkTimes(name,'name',checktype);
    }else {
        $('#suppliercheck-supplier_code').prop('disabled',false);
    }
});


$('[name="SupplierCheck[check_type]"]').click(function() {
    var radio = $(this).val()
    if(radio==1){
        $('.hiddentr').css('display','none');
    }else {
        $('.hiddentr').css('display','');
    }
    $('.append').remove();
    $('.append-type').remove();
    $('.type').remove();
    $('.addsku').remove();
    var supplier_code = $('#suppliercheck-supplier_code').val();
    var supplier_name = $('#suppliercheck-supplier_name').val();
    if(supplier_code != ''){
        checkTimes(supplier_code,'code',radio);
    }
    if(supplier_name !=''){
        checkTimes(supplier_name,'name',radio);
    }
});
$(document).on('click','.sku_delete',function() {
   $(this).closest('tr').remove();
});
$(document).on('blur','.addsku input',function() {
    var type = $(this).closest('tr').find('.type').val();
    var num = $(this).closest('tr').find('.purchase-num').val();
    var sku = $(this).closest('tr').find('.sku').val();
    var obj= $(this);
    $.ajax({
        url:'{$getUrl}',
        data:{sku:sku,type:type,num:num},
        type:'get',
        dataType:'json',
        success:function(data) {
            if(data.status=='error'){
                $("#check-form").modal('show');
                $("#check-form .modal-body").html(data.message);
            }else {
                obj.closest('tr').find('.check_standard').val(data.check_standard)
                obj.closest('tr').find('.check_rate').val(data.check_rate)
                obj.closest('tr').find('.check_num').val(data.sample_num)
            }
        }
      });
});
var clickTimes = 1000;
$('[name="SupplierCheck[order_type]"]').on('click',function() {
    $('.append').remove();
    $('.append-type').remove();
  if($(this).val()==2){
      var type = false;
    $('[name="SupplierCheck[group]"]').each(function() {
        if($(this).is(':checked')){
            type = $(this).val();
        }
    });
    if(!type){
          alert('请先选择部门！');
          return false;
      }
     var html = '<tr class="addsku">' +
      '<input type="hidden" class="type" name="SupplierCheck[items]['+clickTimes+'][type]" value='+type+'>' +
       '<td class="form"><div class="form-group"><label></label><input class="form-control sku" type="text" name="SupplierCheck[items]['+clickTimes+'][sku]"></div></td>' +
        '<td class="form"><div class="form-group"><label></label><input  class="form-control purchase-num" type="text" name="SupplierCheck[items]['+clickTimes+'][purchase_num]"></div></td>' +
         '<td class="form"><div class="form-group"><label></label><input readonly="readonly" class="form-control check_standard" type="text" name="SupplierCheck[items]['+clickTimes+'][check_standard]"></div></td>' +
          '<td class="form"><div class="form-group"><label></label><input readonly="readonly" class="form-control check_num" type="text" name="SupplierCheck[items]['+clickTimes+'][check_num]"></div></td>' +
          '<td class="form"><div class="form-group"><label></label><input readonly="readonly" class="form-control check_rate" type="text" name="SupplierCheck[items]['+clickTimes+'][check_rate]"></div></td>' +
          '<td class="form"><div class="form-group"><label></label><input class="form-control" type="text" name="SupplierCheck[items]['+clickTimes+'][complaint_rate]"></div></td>' +
          '<td class="form"><div class="form-group"><label></label><input class="form-control" type="text" name="SupplierCheck[items]['+clickTimes+'][complaint_point]"> </div></td>' +
          '<td class="form"><a href="#" class="sku_delete">删除</a></td>' +
           '</tr>';
     $('.supplier-table').append(html);
     clickTimes++;
  }else {
      var pur_number = $('#pur_number').val();
      if(pur_number!=''){
          checkPur();
      }
      $('.addsku').remove();
  }
});
    
$('[name="SupplierCheck[group]"]').on('change',function() {
    $('.addsku').remove();
});
$('.submit').click(function() {
    $.ajax({
            url:'{$checkUrl}',
            data:$('#check_form').serialize(),
            type:'post',
            success:function(data) {
               var response = $.parseJSON(data);
               if(response.status=='error'){
                   $("#check-form").modal('show');
                   $("#check-form .modal-body").html(response.message);
               }else {
                   $('#check_form').submit();
               }
            }
        });
});

JS;
$this->registerJs($js);
?>



