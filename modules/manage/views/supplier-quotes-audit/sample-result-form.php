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
        'id'=>'sample-result',
        'enableAjaxValidation' => false,
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => ['labelSpan' => 3, 'deviceSize' => ActiveForm::SIZE_SMALL]
    ]
); ?>
<div class="raw">
    <?= $form->field($model,'id')->hiddenInput()->label(false)?>
    <div class="col-md-12">
        <?= $form->field($model,'sample_result')->radioList([1=>'合格',2=>'不合格'],['required'=>true])->label('样品结果')?>
    </div>
    <div class="col-md-12">
        <?= $form->field($model,'sample_reason')->input('textarea',['placeholder'=>'填写审核原因'])->label('原因') ?>
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
  var sample_result = '';
  var sample_reason = $('[name="SupplierQuotesManage[sample_reason]"]').val();
  $('[name="SupplierQuotesManage[sample_result]"]').each(function() {
    if($(this).is(':checked')){
        sample_result = $(this).val();
    }
  });
  if(sample_result==''){
      layer.msg('拿样结果不能为空');
      return false;
  }
  if(sample_result==2&&sample_reason==''){
      layer.msg('审核不通过需要填写原因');
      return false;
  }
  $('#sample-result').submit();
});
JS;
$this->registerJs($js);
?>





