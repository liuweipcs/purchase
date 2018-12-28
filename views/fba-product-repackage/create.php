<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
?>
    <div class="purchase-order-form" >
        <?php $form = ActiveForm::begin([
                'id' => 'form-id',
            ]
        ); ?>
        <div class="col-md-12"><?= $form->field($model, 'sku')->textarea(['rows'=>3,'cols'=>10,'placeholder' => "多个请用逗号,空格,换行隔开"])->label('SKU') ?></div>

        <div class="form-group clearfix">
            <?= Html::submitButton('提交',['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$js = <<<JS

JS;
$this->registerJs($js);
?>