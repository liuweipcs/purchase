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
        <div class="col-md-12">

            <?= $form->field($model, 'file_execl')->fileInput( ['maxlength' => true,'placeholder'=>'必填项','required'=>"required"])->label('文件上传') ?>
        </div>

        <p style="color: blue">*从第二行开始写入数据，
            <br/>格式为：A列  SKU
        </p>
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