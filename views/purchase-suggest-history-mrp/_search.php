<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use app\models\PurchaseUser;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggestHistorySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-suggest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>


<!--    <div class="col-md-1">--><?php //$form->field($model, 'warehouse_code')->label('仓库编码') ?><!--</div>-->
<!---->
<!--    <div class="col-md-1">--><?php //$form->field($model, 'warehouse_name')->label('仓库名') ?><!--</div>-->

    <div class="col-md-2" >
        <label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label>
        <?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseSuggestHistoryMrpSearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseSuggestHistoryMrpSearch[start_time]',
                'endAttribute' => 'PurchaseSuggestHistoryMrpSearch[end_time]',
//                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
//                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',strtotime("last month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?>
    </div>

    <div class="col-md-1"> <?=$form->field($model, 'state')->dropDownList(PurchaseOrderServices::getProcesStatus(),['prompt'=> '全部']) ?></div>

    <div class="col-md-1"><?= $form->field($model, 'sku') ?></div>

<!--    <div class="col-md-1"><?/*= $form->field($model, 'name')->label('产品名') */?></div>
-->
    <?php // echo $form->field($model, 'supplier_code') ?>

    <div class="col-md-2">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...','value' =>!empty($name)?$name:''],
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'left')->dropDownList([ '1' => '是', '2' => '否'], ['prompt' => '请选择'])->label('是否欠货') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'product_status')->dropDownList(\app\services\SupplierGoodsServices::getProductStatus(), ['prompt' => '请选择'])->label('产品状态') ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'amount_1')->textInput(['placeholder' => '20'])->label('数量1(区间)') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'amount_2')->textInput(['placeholder' => '50'])->label('数量2(区间)') ?>
    </div>
    <div class="col-md-1">
    <?= $form->field($model, 'buyer_id')->widget(Select2::classname(), [
        'options' => ['placeholder' => '请选择'],
        'data'=>PurchaseUser::getBuyerAndGroup(),
        'pluginOptions' => ['width'=>'130px'],
    ])->label('采购员') ?>
    </div>

    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary','id'=> 'sou_suo']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
