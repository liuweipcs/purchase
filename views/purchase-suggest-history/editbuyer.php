<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$this->title='修改采购员';
?>

<?php $form = ActiveForm::begin([
    'action' => ['editbuyer'],
    'method' => 'post',

]); ?>
<div class="row">
    <div class="col-md-3"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne(),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?></div>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?= $form->field($model, 'id')->textInput()->hiddenInput(['value'=>$ids])->label(false); ?>

</div>
<?php ActiveForm::end(); ?>
