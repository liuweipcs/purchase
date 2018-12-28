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
    <div class="col-md-12"><?= $form->field($model, 'purchase_note')->textarea(['rows'=>3,'cols'=>10,'required'=>true]) ?></div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '确认', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>