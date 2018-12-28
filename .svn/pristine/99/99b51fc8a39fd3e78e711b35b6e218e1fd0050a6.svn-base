<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\SupplierGoodsServices;
//$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
$this->title='分类关联采购';
?>

<div class="product-form">

    <?php $form = ActiveForm::begin(); ?>



<!--    <div class="col-md-2"><?/*= $form->field($model, 'category_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入分类 ...'],
            'data'=>BaseServices::getCategory(),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                //'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                //'templateResult' => new JsExpression('function(res) { return res.text; }'),
                //'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ]);
        */?></div>-->

    <div class="col-md-1"><?= $form->field($model, 'category_id')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入产品线 ...'],
            'data' =>BaseServices::getProductLine(),
            'pluginOptions' => [
                'allowClear' => true,
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
        ])->label('产品线')?></div>
    <div class="col-md-2"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data'=>BaseServices::getEveryOne(),
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
        ])->label('采购员');
        ?></div>
      <div class="col-md-2"><?=$form->field($model, 'purchase_type')->dropDownList(['3'=>'FBA','1'=>'国内','2'=>'海外'])?></div>

    <div class="form-group" style="float: left;margin-top: 25px;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '保存') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a('返回列表', ['product/index-cate'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
