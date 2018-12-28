<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\services\BaseServices;


/* @var $this yii\web\View */
/* @var $model app\models\SupervisorGroupBind */
/* @var $form yii\widgets\ActiveForm */
?>

<style type="text/css">
    .select2-container--krajee .select2-selection--single {
        padding: 10px 24px 10px 12px;
    }
</style>

<div class="supervisor-group-bind-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <!--<div class="col-md-2">
            <?/*= $form->field($model, 'supervisor_id')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请选择'],
                'data'=>BaseServices::getEveryOne(),
            ])->label('主管名称') */?>
        </div>-->
        <div class="col-md-2">
            <?= $form->field($model, 'supervisor_name')->input('text')->label('销售名字')?>
        </div>
        <div class="col-md-2">
            <?=$form->field($model, 'group_id')->input('text')->label('组别')?>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-2">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
