<?php
use yii\helpers\Html;
use kartik\form\ActiveForm;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/14
 * Time: 14:22
 */


/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>

<?php $form = ActiveForm::begin([
        'id'=>'quotes-result',
        'enableAjaxValidation' => false,
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]
    ]
); ?>
<div class="raw">
    <?= $form->field($model,'id')->hiddenInput()->label(false)?>
    <div class="col-md-12">
        <?= $form->field($model,'check_result')->radioList([1=>'通过',2=>'不通过'],['required'=>true])->label('审核结果')?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'is_sample')->radioList([1=>'是',2=>'否'],['required'=>true])->label('是否拿样')?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'check_reason')->input('textarea',['placeholder'=>'填写审核原因'])->label('原因') ?>
    </div>
</div>
<div class="form-group">
    <div class=" col-md-12 col-md-offset-8">
        <button class="btn btn-success submit " type="button">立即提交</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">关闭</button>
    </div>
</div>
<div style="clear: both"></div>
<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document).on('click','.submit',function() {
  var check_result = '';
  var is_sample    = '';
  var check_reason = $('[name="SupplierQuotesManage[check_reason]"]').val();
  $('[name="SupplierQuotesManage[check_result]"]').each(function() {
    if($(this).is(':checked')){
        check_result = $(this).val();
    }
  });
  $('[name="SupplierQuotesManage[is_sample]"]').each(function() {
    if($(this).is(':checked')){
        is_sample = $(this).val();
    }
  });
  if(check_result==''){
      layer.alert('审核结果不能为空');
      return false;
  }
  if(is_sample==''){
      layer.alert('是否拿样不能为空');
      return false;
  }
  if(check_result==2&&check_reason==''){
      layer.alert('审核不通过需要填写原因');
      return false;
  }
  $('#quotes-result').submit();
});
JS;
$this->registerJs($js);
?>





