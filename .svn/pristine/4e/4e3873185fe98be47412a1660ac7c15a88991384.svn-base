<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderSearch */
/* @var $form yii\widgets\ActiveForm */
//$model->buyer= Yii::$app->user->id;
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',

    ]); ?>


    <div class="col-md-1"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
                    'url' => $url,
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],*/
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?>
    </div>

    <div class="col-md-1"><?= $form->field($model, 'items.sku')->label('sku') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'purchas_status')->dropDownList(['1'=>'待确认','2'=>'交货确认'],['prompt' => '请选择']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_number')->label('采购单') ?></div>
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
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'create_type')->dropDownList(\app\services\PurchaseOrderServices::getCreateType(), ['prompt' => '请选择']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'audit_return')->dropDownList(\app\services\PurchaseOrderServices::getAuditReturn(), ['prompt' => '请选择']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->dropDownList(\app\services\BaseServices::getWarehouseCode(), ['prompt' => '请选择'])->label('仓库名称') ?></div>
<!--    <div class="col-md-1">--><?php //$form->field($model, 'shipping_method')->dropDownList(\app\services\PurchaseOrderServices::getShippingMethod(), ['prompt' => 'please choose']) ?><!--</div>-->
    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
            'name'=>'s',
            'useWithAddon'=>true,
            'convertFormat'=>true,
            'startAttribute' => 'PurchaseOrderSearch[start_time]',
            'endAttribute' => 'PurchaseOrderSearch[end_time]',
//            'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
//            'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
            'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',strtotime("last month"))],
            'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
            'pluginOptions'=>[
                'locale'=>['format' => 'Y-m-d'],
            ]
        ]).$addon ;
        echo '</div>';
        ?></div>
    <!--<div class="col-md-1"><?/*= $form->field($model, 'page_size')->label('page_size') */?></div>
    <div class="col-md-1">
        <div class="form-group field-purchaseordersearch-page_size">
            <label class="control-label" for="purchaseordersearch-page_size">每页的数据</label>
            <input type="text" id="purchaseordersearch-page_size" class="form-control" name="PurchaseOrderSearch[page_size]" value="">
            <div class="help-block"></div>
        </div>
    </div>-->
    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
