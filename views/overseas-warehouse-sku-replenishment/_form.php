<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\datetime\DateTimePicker;
/* @var $this yii\web\View */
/* @var $model app\models\SkuSingleTacticMain */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sku-single-tactic-main-form">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'warehouse')->dropDownList(BaseServices::getWarehouseCode(),['prompt' => '请选择仓库']) ?>
    <?= $form->field($model, 'date_start')->widget(DateTimePicker::className(),[
        'options' => ['placeholder' => '','readonly'=>true],
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd hh:ii:ss',
        ]
    ]); ?>
    <?= $form->field($model, 'date_end')->widget(DateTimePicker::className(),[
        'options' => ['placeholder' => '','readonly'=>true],
        'pluginOptions' => [
            'autoclose' => true,
            'todayHighlight' => true,
            'format' => 'yyyy-mm-dd hh:ii:ss',
        ]
    ]); ?>
    <?= $form->field($model, 'supply_days')->textInput(['maxlength' => true]) ?>
    <?php if($model->isNewRecord){$model->status='1';} echo $form->field($model, 'status')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
