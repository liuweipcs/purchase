<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ProductSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    .col-md-1{
        padding-left: 0px;
    }
</style>
<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-1"><?= $form->field($model, 'sku')->label('SKU')->textInput(['placeholder'=>'如:JM00002']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->label('仓库编码')->textInput(['placeholder'=>'如:SZ_AA']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_name')->label('仓库名称')->textInput(['placeholder'=>'如:深圳仓']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pattern')->dropDownList(['min'=>'最小','def'=>'默认'], ['prompt' => 'Choose'])->label('补货模式') ?></div>
    
    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
