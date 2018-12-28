<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\services\SupplierServices;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(); ?>


    <h4><?=Yii::t('app','付款审批')?></h4>
    <input type="hidden"  class="form-control" name="PurchaseOrderPay[pur_number]" value="<?=$pur_number?>" />
    <div class="col-md-6"><?= $form->field($model, 'settlement_method')->dropDownList(SupplierServices::getDefaultPaymentMethod(),['prompt' => Yii::t('app','默认')]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'pay_type')->dropDownList(SupplierServices::getSettlementMethod(),['prompt' => Yii::t('app','默认')]) ?></div>


    <div class="col-md-12"><?= $form->field($model, 'review_notice')->textarea(['rows'=>3,'cols'=>10,'placeholder'=>'请填写备注']) ?></div>
    <p style="color:red;">备注：支付方式、结算方式，选择“默认”时，将不会修改。</p>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '确认' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
