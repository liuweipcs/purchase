<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderPaySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-pay-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-1"><?= $form->field($model, 'pay_status')->dropDownList(PurchaseOrderServices::getPayStatus(),['prompt' => 'please choose']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>



    <div class="col-md-1"><?= $form->field($model, 'requisition_number') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'applicant')->dropDownList(BaseServices::getEveryOne(),['prompt' => 'please choose']) ?></div>
    <div class="col-md-1"><label class="control-label" for="purchaseorderpaysearch-applicant">申请时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseOrderSearch[created_at1]',
                'useWithAddon'=>true,
                'convertFormat'=>true,

                'startAttribute' => 'PurchaseOrderSearch[start_time]',
                'endAttribute' => 'PurchaseOrderSearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <div class="col-md-1"><?= $form->field($model, 'supplier_code')->dropDownList(BaseServices::getSupplier(),['prompt' => 'please choose']) ?></div>
    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
