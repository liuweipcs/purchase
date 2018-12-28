<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(); ?>
    <?php foreach ($data as $k=>$v){?>
        <?= Html::hiddenInput("changeBuyer[$k][supplier]",$v[0])?>
        <?= Html::hiddenInput("changeBuyer[$k][warehouse_code]",$v[1])?>
        <?= Html::hiddenInput("changeBuyer[$k][num]",$v[2])?>
        <?= Html::hiddenInput("changeBuyer[$k][buyer]",$v[3])?>
    <?php }?>
    
    <div class="col-md-12"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>\app\services\BaseServices::getBuyer('name'),
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
    <div class="form-group">
        <?= Html::submitButton( '确认', ['class' =>'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
