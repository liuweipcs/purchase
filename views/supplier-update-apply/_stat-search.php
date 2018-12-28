<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action'=>['integrat-stat'],
        'method'=>'get'
    ]); ?>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">时间：</label><?php
        $add = <<< HTML
        <span class="input-group-addon">
            <i class="glyphicon glyphicon-calendar"></i>
        </span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'SupplierUpdateApplySearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'SupplierUpdateApplySearch[check_start_time]',
                'endAttribute' => 'SupplierUpdateApplySearch[check_end_time]',
                'startInputOptions' => ['value' => !empty($model->check_start_time) ? $model->check_start_time : date('Y-m-d 00:00:00',time())],
                'endInputOptions' => ['value' => !empty($model->check_end_time) ? $model->check_end_time : date('Y-m-d 23:59:59',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$add ;
        echo '</div>';
        ?></div>
    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置',['integrat-stat'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
