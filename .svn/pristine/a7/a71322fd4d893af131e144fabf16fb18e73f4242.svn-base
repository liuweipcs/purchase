<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;

$users = \app\services\BaseServices::getEveryOne();

$users[0] = '@所有人';

ksort($users, SORT_NUMERIC);

?>

<div class="bulletin-board-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'account')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'app_key')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'secret_key')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'redirect_uri')->textInput(['maxlength' => true,'value'=>Yii::$app->request->absoluteUrl]) ?>

    <?= $form->field($model, 'bind_account')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入名字 ...'],
            'data' => $users,
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ]);
        ?>
    <?= $form->field($model, 'status')->dropDownList(['1' => '启用', '2' => '停用']); ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '提交') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
