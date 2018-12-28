<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<h3 class="fa-hourglass-3">导入产品</h3>
<div class="row">
    <div class="col-md-4">
        <?= $form->field($model, 'file_execl')->fileInput( ['maxlength' => true,'placeholder'=>'必填项'])->label('文件上传') ?>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>