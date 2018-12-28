<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
?>

<?php $form = ActiveForm::begin([
    'action' => ['edit-buyer'],
    'method' => 'post',

]); ?>
<div class="row">
    <div class="col-md-3">
        <?= $form->field($model, 'buyer')->widget(\kartik\select2\Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new \yii\web\JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
                'templateResult' => new \yii\web\JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new \yii\web\JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?>
    </div>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?= $form->field($model, 'id')->textInput()->hiddenInput(['value'=>$ids])->label(false); ?>

</div>
<?php ActiveForm::end(); ?>
