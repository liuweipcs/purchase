<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BulletinBoard */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bulletin-board-form">

    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?=$form->field($model,'content')->widget('kucha\ueditor\UEditor',[]); ?>
    <?=$form->field($model,'bulletin_board_type')->dropDownList($model::bulletinBoardType(),['options' => [$model->bulletin_board_type=> ['selected' => 'selected']]])->label('公告类型'); ?>




    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '提交') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
