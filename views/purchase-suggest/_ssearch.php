<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use kartik\select2\Select2;
use app\models\PurchaseUser;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseSuggestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-suggest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['purchase-suggest/purchase-sum-view'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-2"><?= $form->field($model, 'sku') ?></div>
    <div class="col-md-2">
        <?= $form->field($model, 'warehouse_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选仓库 ...','value' =>!empty($name)?$name:''],
            'data'=>BaseServices::getWarehouseCode(),
            'pluginOptions' => [
                'placeholder' => 'search ...',
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /* 'ajax' => [
                     'url' => $url,
                     'dataType' => 'json',
                     'data' => new JsExpression('function(params) { return {q:params.term}; }')
                 ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('仓库');
        ?>
    </div>
    <!--<div class="col-md-2"><?/*= $form->field($model, 'create_id')->label('创建人') */?></div>-->
    <!--<div class="col-md-1"><?/*= $form->field($model, 'platform_number')->label('平台号') */?></div>-->
    <!--<div class="col-md-1"><?/*= $form->field($model, 'purchase_quantity')->label('采购数量') */?></div>-->
    <!--<div class="col-md-1"><?/*= $form->field($model, 'sales_note')->label('备注') */?></div>-->
    <!--<div class="col-md-1"><?/*= $form->field($model, 'suggest_status')->label('采购建议状态') */?></div>-->

    <div class="col-md-2"><?= $form->field($model, 'suggest_status')->dropDownList([ '1' => '未使用过', '2' => '使用过'], ['prompt' => '请选择'])->label('采购建议状态') ?></div>
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
                'name'=>'PurchaseSuggestQuantitySearch[create_time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseSuggestQuantitySearch[start_time]',
                'endAttribute' => 'PurchaseSuggestQuantitySearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['purchase-suggest/purchase-sum-view'],['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>