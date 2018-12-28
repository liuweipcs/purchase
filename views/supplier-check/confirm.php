<?php
use yii\widgets\ActiveForm;
use app\services\BaseServices;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/22
 * Time: 10:09
 */
?>
<?php $form = ActiveForm::begin([
        'id' => 'confirm-date-user',
    ]
); ?>
<div class="col-md-12">
    <label class="control-label">确认时间</label>
    <input id='confirm_time_confirm' class="form-control"  name='SupplierCheck[confirm_time]' value="<?=$model->confirm_time?>" required>
</div>
<div class="col-md-12">
<?= $form->field($model, 'check_user')->widget(\kartik\select2\Select2::classname(), [
    'options' => ['placeholder' => '请输入采购员 ...','required'=>true,'value'=>$check_user],
    'data' =>BaseServices::getEveryOne(),
    'pluginOptions' => [
        'multiple' => true,
        'allowClear' => true,
        'language' => [
            'errorLoading' => new \yii\web\JsExpression("function () { return 'Waiting...'; }"),],
    ],])->label('检验员')?>
</div>
<div class="form-group">
    <?= \yii\helpers\Html::submitButton(Yii::t('app', '确认'), ['class' => 'btn btn-primary']) ?>
    <a href="#" class="btn btn-primary closes" data-dismiss="modal">取消</a>
</div>


<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
layui.use('laydate', function(){
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
            elem: '#confirm_time_confirm' //指定元素
        });
        });
JS;
$this->registerJs($js);
?>
