<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="group-audit-config-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'group')->dropDownList(Yii::$app->params['re_group'],['style'=>'width:200px']) ?>

    <?= $form->field($model, 'values')->dropDownList(Yii::$app->params['num_range'],['style' => 'width:200px']) ?>

    <?= $form->field($model, 'remark')->textarea(['style' => 'width:200px']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
