<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\PurchaseUser;
use kartik\select2\Select2;
use app\services\BaseServices;
use yii\web\JsExpression;

$this->title = $model->isNewRecord ? '添加' : '更新';
?>

<div class="purchase-grade-audit-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-1">
    	<?= $form->field($model, 'audit_user')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'allowClear' => true,
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?>
    </div>


    <?=$form->field($model, 'type')->dropDownList(PurchaseUser::getUserType(),['style' => 'width:200px']) ?>

    <?= $form->field($model, 'audit_price')->textInput(['style'=>'width:200px','value'=> !empty($model->audit_price) ? $model->audit_price : '1000']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '添加' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
