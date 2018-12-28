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
            <?= Html::input('hidden', 'purchasetacticssearch-tactics_id',$tactics_id) ?>
            <?= $form->field($model, 'file_execl')->fileInput( ['maxlength' => true,'placeholder'=>'必填项','required'=>"required"])->label('文件上传') ?>
        </div>

        <img src="<?=Yii::$app->request->hostInfo.'/images/tu1.jpg'?>" class="imge"/>
        <a   href="<?=Yii::$app->request->hostInfo.'/files/skutacticsimport.xls'?>" class="fb">文件格式</a>
        <div class="form-group clearfix">
            <?= Html::submitButton('提交',['class' => 'btn btn-sm btn-success']) ?>
            <span style="margin-left: 50px;">&nbsp;</span>
            <?= Html::a('回到首页', ["index?PurchaseTacticsSearch[tactics_type]=2"], ['class' => 'btn btn-sm btn-warning']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$js = <<<JS

JS;
$this->registerJs($js);
?>