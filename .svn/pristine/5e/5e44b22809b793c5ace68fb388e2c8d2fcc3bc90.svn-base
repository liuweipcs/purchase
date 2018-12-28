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
        'action' => ['index'],
        'method' => 'get',

    ]); ?>
    <div class="col-md-1"><?= $form->field($model, 'items.sku')->label('sku筛选') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_number')->label('PO号') ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'warn_status')->dropDownList([
            0=>'全部',
            1=>'申请付款超时',
            2=>'付款超时',
            3=>'获取物流超时',
            4=>'签收超时',
            5=>'上架超时',
        ])->label('预警状态') ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'settlement')->dropDownList(SupplierServices::getSettlementMethod(),['prompt'=>'请选择']
        )->label('结算方式') ?></div>
    <div class="col-md-2">
    <?= $form->field($model, 'grade')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选择'],
        'data'=>\app\models\PurchaseUser::getBuyerAndGroup(),
        'pluginOptions' => ['width'=>'130px'],
    ])->label('采购员') ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'supplier_name')->label('供应商') ?></div>

    <!--<div class="col-md-1"><?/*= $form->field($model, 'express_no')->label('物流编码') */?></div>-->
    <!--<div class="col-md-1"><?/*= $form->field($model, 'ship.express_no')->label('物流编码：未完成') */?></div>-->

    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">PO生成日期：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseOrderSearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseOrderSearch[start_time]',
                'endAttribute' => 'PurchaseOrderSearch[end_time]',
//            'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-6 month"))],
//            'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
//            'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d',strtotime("-6 month"))],
                'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',strtotime("-6 month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
