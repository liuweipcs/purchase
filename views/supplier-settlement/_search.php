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
        'action' => ['log'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1">
        <?=$form->field($model, "group")->dropDownList(['0'=>'全部','1'=>'供应链团队','2'=>'FBA','3'=>'国内仓'],['class' => 'form-control'])->label('部门')?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...'],
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term,status:null}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供货商');
        ?>
    </div>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">提交时间：</label><?php
        $addon = <<< HTML
        <span class="input-group-addon">
            <i class="glyphicon glyphicon-calendar"></i>
        </span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'SupplierSettlementLog[create_time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'SupplierSettlementLog[update_start_time]',
                'endAttribute' => 'SupplierSettlementLog[update_end_time]',
                'startInputOptions' => ['value' => !empty($model->update_start_time) ? $model->update_start_time : date('Y-m-d 00:00:00',strtotime("last month"))],
                'endInputOptions' => ['value' => !empty($model->update_end_time) ? $model->update_end_time : date('Y-m-d 23:59:59',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>

    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置',['log'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
