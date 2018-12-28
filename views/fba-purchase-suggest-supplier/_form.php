<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggest */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-suggest-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'warehouse_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'warehouse_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'supplier_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'buyer')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'replenish_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'qty')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ship_method')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_purchase')->dropDownList([ 'Y' => 'Y', 'N' => 'N', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'creator')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'product_category_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'category_cn_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'on_way_stock')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'available_stock')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'stock')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'left_stock')->textInput() ?>

    <?= $form->field($model, 'days_sales_3')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_7')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_15')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'days_sales_30')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sales_avg')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'type')->dropDownList([ 'last_down' => 'Last down', 'last_up' => 'Last up', 'wave_up' => 'Wave up', 'wave_down' => 'Wave down', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
