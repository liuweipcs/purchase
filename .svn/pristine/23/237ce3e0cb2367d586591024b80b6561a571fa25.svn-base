<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $form = ActiveForm::begin([
]);?>
<div class="col-md-12">
    <?=$form->field($model,'note')->textarea(['readonly'=>true])->label('已有备注')?>
    <?=$form->field($model,'addnote')->textarea(['required'=>true])->label('新增备注')?>
</div>
<div class="form-group">
    <?= Html::submitButton( Yii::t('app', '确认新增备注'), ['class' =>  'btn btn-primary',]) ?>
</div>
<?php ActiveForm::end(); ?>





