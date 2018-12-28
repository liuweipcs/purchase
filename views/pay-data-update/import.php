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
            <?= $form->field($model, 'buyer_id')->textInput( ['maxlength' => true,'placeholder'=>'必填项','required'=>"required"])->label('付款人编号') ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', '提交') , ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?><?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30
 * Time: 11:42
 */