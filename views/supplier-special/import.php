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
        <div class="col-md-4">
            <?= $form->field($model, 'source')->fileInput( ['maxlength' => true,'placeholder'=>'必填项','required'=>"required"])->label('文件上传') ?>
        </div>

        <img src="<?=Yii::$app->request->hostInfo.'/images/tu1.jpg'?>" class="imge"/>
        <a   href="<?=Yii::$app->request->hostInfo.'/files/ImportCrossBorder20181115.xlsx'?>" class="fb">文件格式</a>
        <div class="form-group clearfix">
            <?= Html::submitButton('提交',['class' => 'btn btn-lg btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$js = <<<JS

JS;
$this->registerJs($js);
?>