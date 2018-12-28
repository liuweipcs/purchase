<?php
use yii\widgets\ActiveForm;
use app\services\BaseServices;
?>
<style type="text/css">
    .row {
        padding: 8px;
    }
</style>
<?php $form = ActiveForm::begin(['id' => 'refund-handler-form']); ?>
<h5>编辑退款信息</h5>

<?php foreach($models as $mod):?>

<div class="container-fluid" style="border: 1px solid #ccc;">

    <input type="hidden" name="RefundHandler[id][]" value="<?= $mod->id ?>">

    <div class="row">

        <div class="col-md-2">
            <label>采购单号</label>
            <input type="text" class="form-control" value="<?= $mod->pur_number ?>" name="RefundHandler[pur_number][]" readonly>
        </div>

        <div class="col-md-2">
            <label>退款金额</label>
            <input type="text" class="form-control pay_price" name="RefundHandler[pay_price][]" value="<?= $mod->pay_price ?>">
        </div>

        <div class="col-md-2">
            <label>收款人</label>
            <input type="text" class="form-control" value="<?= BaseServices::getEveryOne($mod->payer) ?>" disabled>
        </div>

        <div class="col-md-3">
            <label>收款时间</label>
            <input type="text" class="form-control" value="<?= $mod->payer_time ?>" disabled>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <label>收款备注</label>
            <textarea class="form-control" rows="3" disabled><?= $mod->payer_notice ?></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <label>退款备注</label>
            <textarea class="form-control" rows="3" name="RefundHandler[review_notice][]"><?= $mod->review_notice ?></textarea>
        </div>
    </div>

<?php endforeach; ?>

<div class="row">
    <div class="col-md-12">
        <span id="btn-submit" class="btn btn-primary">提交</span>
    </div>
</div>

<?php
ActiveForm::end();
?>

<?php
$js = <<<JS
$(function() {
    
   $('#btn-submit').click(function() {
       var flag = true;
       $('.pay_price').each(function() {
           if($(this).val() == '') {
               layer.tips('请输入金额', $(this));
               flag = false;
               return false;
           }
       });
       if(flag) {
           $('#refund-handler-form').submit();
       }
   });
   
});
JS;
$this->registerJs($js);
?>
