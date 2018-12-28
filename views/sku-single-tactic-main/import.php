<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

    <style>
        .imge{

            position:absolute;
            left: 230px;
            bottom:5px;
        }
        .fb{
            font-size: 16px;
            position:absolute;
            top: 10px;
            left: 360px;

        }
    </style>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <h3 class="fa-hourglass-3">文件上传</h3>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'file_execl')->fileInput( ['maxlength' => true,'placeholder'=>'必填项','required'=>"required"])->label('文件上传') ?>
        </div>
    </div>
    <img src="<?=Yii::$app->request->hostInfo.'/images/tu1.jpg'?>" class="imge"/>
    <a   href="<?=Yii::$app->request->hostInfo.'/files/sku_replenishment_strategy.csv'?>" class="fb">文件格式</a>
    <span style="color: red">只接受cvs格式的文件</span>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>