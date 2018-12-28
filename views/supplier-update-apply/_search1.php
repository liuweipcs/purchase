<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\BaseServices;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['supplier-sample-inspect'],
        'method' => 'get',

    ]); ?>
    <div class="col-md-1"><?= $form->field($model,'sku')->textInput(['placeholder'=>''])->label('SKU') ?></div>
    <div class="col-md-1"><?= $form->field($model,'qc_result')->dropDownList(['all'=>'全部',1=>'待确认',2=>'合格',3=>'不合格'])->label('质检结果') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'apply_user')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入整合人 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('整合人员');
        ?>
    </div>
    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['supplier-sample-inspect'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
