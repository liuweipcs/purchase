<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
$this->title='添加物流跟踪备注';
?>

<div class="purchase-order-form">

    <?php $form = ActiveForm::begin([]
    ); ?>


    <h4><?=Yii::t('app','添加物流跟踪备注')?></h4>
    <input type="hidden"  class="form-control" name="PurchaseOrderShip[pur_number]" value="<?=$pur_number?>">

    <div class="col-md-2"><?= $form->field($model, 'cargo_company_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入快递公司 ...'],
            'data' =>\app\services\BaseServices::getLogisticsCarrier(),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('快递公司');
        ?></div>

    <div class="col-md-2"><?= $form->field($model, 'express_no')->textInput() ?></div>

    <div class="col-md-2"><?= $form->field($model, 'purchase_type')->dropDownList(['3'=>'FBA','1'=>'国内','2'=>'海外']) ?></div>


    <div class="col-md-6"><?= $form->field($model, 'note')->textarea(['rows'=>3,'cols'=>3]) ?></div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '提交' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('返回',['index'],['class'=>'btn btn-primary'])?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
