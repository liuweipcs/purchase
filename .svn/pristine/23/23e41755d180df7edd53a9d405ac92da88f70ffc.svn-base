<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\SupplierGoodsServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>



<?php $form = ActiveForm::begin([
        //'id' => 'form-id',
        //'enableAjaxValidation' => true,
        //'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<div class="row">

    <?php

   // $model->default_buyer=Yii::$app->user->id;
    $model->currency='RMB';
    //$model->default_Merchandiser=Yii::$app->user->id;
    ?>
   <!--<div class="col-md-4"><?/*= $form->field($model, 'suppliercode')->dropDownList(BaseServices::getSupplier(),['maxlength' => true,'readonly'=>$model->isNewRecord?false:true])->label('供应商') */?></div>-->
    <div class="col-md-4"><?= $form->field($model, 'suppliercode')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...'],
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?></div>

    <div class="col-md-4"><?= $form->field($model, 'product_sku')->textInput( ['maxlength' => true,'placeholder'=>'必填项','readonly'=>$model->isNewRecord?false:true,'required'=>true]) ?></div>
   <!-- <div class="col-md-4"><?/*= $form->field($model, 'product_number')->textInput(['maxlength' => true,]) */?></div>-->
    <div class="col-md-4"><?= $form->field($model, 'supplierprice')->textInput(['placeholder'=>'必填项','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'currency')->dropDownList(SupplierGoodsServices::getCurrency(),['prompt' => '--请选择--',]) ?></div>
   <!-- <div class="col-md-4"><?/*= $form->field($model, 'minimum_purchase_amount')->textInput(['maxlength' => true,'placeholder'=>'必填项','required'=>true]) */?></div>-->
    <!--<div class="col-md-4"><?/*= $form->field($model, 'purchase_delivery')->textInput(['maxlength' => true,'placeholder'=>'必填项','required'=>true]) */?></div>-->
    <!--<div class="col-md-4"><?/*= $form->field($model, 'purchasing_units')->dropDownList(['1'=>'散件','2'=>'整件']) */?></div>-->

    <div class="col-md-4"><?= $form->field($model, 'default_buyer')->dropDownList(SupplierServices::getEveryOne()) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'default_Merchandiser')->dropDownList(SupplierServices::getEveryOne()) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'default_vendor')->dropDownList(['1'=>'Y','0'=>'N'], ['prompt' => '--请选择--','required'=>true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'supplier_product_address')->textInput(['maxlength' => true]) ?></div>
</div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>


    <?php ActiveForm::end(); ?>



