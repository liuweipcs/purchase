<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\SupplierGoodsServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>



<?php $form = ActiveForm::begin([
        //'id' => 'form-id',
        //'enableAjaxValidation' => true,
        //'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<div class="row">
    <input type="hidden" name="SampleInspect[id]" value=<?= $model->id ?> >
    <div class="col-md-4"><?= $form->field($model, 'sku')->textInput( ['maxlength' => true,'placeholder'=>'必填项','readonly'=>true,'required'=>true]) ?></div>
    <?php if($type == 'quality'){ ?>
    <div class="col-md-4"><?= $form->field($model, 'reason')->textInput( ['maxlength' => true])->label('质检备注') ?></div>
    <?php }?>
    <?php if($type == 'qualityno'){ ?>
        <div class="col-md-4"><?= $form->field($model, 'reason')->textInput( ['maxlength' => true,'placeholder'=>'必填项','required'=>true])->label('质检备注') ?></div>
    <?php }?>
</div>

<div class="form-group">
    <?= Html::submitButton('提交', ['class' =>'btn btn-primary']) ?>
</div>


<?php ActiveForm::end(); ?>



