<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


?>
<div class="product-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-2"><?= $form->field($model, 'supplier_name')->textInput(['placeholder'=>'','style' => 'width:230px;']) ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
