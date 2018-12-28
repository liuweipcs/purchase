<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use app\services\SupplierGoodsServices;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="col-md-2"><?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-2"><?= $form->field($model_desc, 'title')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'product_category_id')->dropDownList(BaseServices::getCategory()) ?></div>

    <div class="col-md-2"><?= $form->field($model, 'product_status')->dropDownList(SupplierGoodsServices::getProductStatus()) ?></div>

    <div class="col-md-2"><?= $form->field($model, 'product_cn_link')->textInput(['maxlength' => true]) ?></div>

    <div class="col-md-2"><?= $form->field($model, 'product_en_link')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'create_id')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'product_linelist_id')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'last_price')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'product_is_multi')->dropDownList(['0'=>'单品','1'=>'多属性单品','2'=>'多属性组合产品'],['maxlength' => true]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'product_type')->dropDownList(['1'=>'不是','2'=>'是'],['maxlength' => true]) ?></div>


    <div class="col-md-2"><?= $form->field($model, 'product_cost')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'supply_status')->dropDownList(SupplierGoodsServices::getSupplyStatus()) ?></div>
    <div class="col-md-2"><?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?></div>



    <div class="form-group"  style="clear: both;">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
