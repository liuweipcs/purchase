<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\ProductCategory;
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['addproduct'],
        'method' => 'get',

    ]); ?>

    <div class="col-md-2"><?= $form->field($model, 'product_category_id')->dropDownList(ProductCategory::getCategory(),['prompt' => '选择分类'])->label('',['style'=>'margin-top:13px']) ?></div>


    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
