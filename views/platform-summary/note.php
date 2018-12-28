<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(); ?>


    <h4><?=Yii::t('app','请填写驳回理由')?></h4>
    <?= $form->field($model, 'id')->textInput()->hiddenInput(['value'=>$id])->label(false); ?>
    <input type="hidden"  class="form-control" name="PlatformSummary[page]" value="<?=$page?>">
    <div class="col-md-12"><?= $form->field($model, 'audit_note')->textarea(['rows'=>3,'cols'=>10,'required'=>true]) ?></div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '确认' : '确认', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
