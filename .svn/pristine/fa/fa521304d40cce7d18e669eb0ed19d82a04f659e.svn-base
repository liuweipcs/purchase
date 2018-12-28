<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
?>

<?php $form = ActiveForm::begin([
    'action' => ['editbuyer'],
    'method' => 'post',

]); ?>
<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'buyer')->dropDownList(BaseServices::getEveryOne(), ['prompt' => '请选择采购员'])->label('采购员') ?>
    </div>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?= $form->field($model, 'id')->textInput()->hiddenInput(['value'=>$ids])->label(false); ?>

</div>
<?php ActiveForm::end(); ?>
