<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-suggest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


    <div class="col-md-1"><?= $form->field($model, 'sku') ?></div>



    <?php // echo $form->field($model, 'supplier_code') ?>

    <div class="col-md-1"><?php echo $form->field($model, 'supplier_name')->label('供应商') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'buyer')->dropDownList(\app\services\BaseServices::getEveryOne(),['prompt'=> '请选择采购员'])->label('采购员') ?></div>

    <?php // echo $form->field($model, 'supplier_code') ?>

    <?php // echo $form->field($model, 'supplier_name') ?>

    <?php // echo $form->field($model, 'buyer') ?>

    <?php // echo $form->field($model, 'replenish_type') ?>

    <?php // echo $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'ship_method') ?>

    <?php // echo $form->field($model, 'is_purchase') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'creator') ?>

    <?php // echo $form->field($model, 'product_category_id') ?>

    <?php // echo $form->field($model, 'category_cn_name') ?>

    <?php // echo $form->field($model, 'on_way_stock') ?>

    <?php // echo $form->field($model, 'available_stock') ?>

    <?php // echo $form->field($model, 'stock') ?>

    <?php // echo $form->field($model, 'left_stock') ?>

    <?php // echo $form->field($model, 'days_sales_3') ?>

    <?php // echo $form->field($model, 'days_sales_7') ?>

    <?php // echo $form->field($model, 'days_sales_15') ?>

    <?php // echo $form->field($model, 'days_sales_30') ?>

    <?php // echo $form->field($model, 'sales_avg') ?>

    <?php // echo $form->field($model, 'type') ?>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
