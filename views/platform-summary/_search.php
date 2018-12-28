<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\TodayListSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]);?>
    <div class="col-md-1">
        <?= $form->field($model, 'sku')->textInput() ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'demand_number')->textInput() ?>
    </div>
   <!-- <div class="col-md-1">
        <?/*= $form->field($model, 'product_category')->dropDownList(BaseServices::getCategory(),['prompt' => '请选择']) */?>
    </div>-->
 <!--   <div class="col-md-1"><?/*= $form->field($model, 'product_category')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入产品分类 ...'],
            'data' =>BaseServices::getCategory(),
            'pluginOptions' => [

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
        ])*/?></div>-->
    <div class="col-md-1"><?= $form->field($model, 'product_line')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入产品线 ...'],
            'data' =>BaseServices::getProductLine(),
            'pluginOptions' => [
                'allowClear' => true,
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
        ])->label('产品线')?></div>
    <div class="col-md-1"><?= $form->field($model, 'group_id')->dropDownList(BaseServices::getAmazonGroup(),['value'=>$model->group_id])?></div>
    <div class="col-md-1"><?= $form->field($model, 'default_buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getBuyer('name'),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?></div>
    <div class="col-md-2">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...','value' =>!empty($model->supplier_code) ? $model->supplier_code : ''],
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
        <?= $form->field($model, 'is_purchase')->dropDownList(['1'=>'未生成','2'=>'已生成','all'=>'全部'],['value'=>$model->is_purchase])->label('是否生成') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'is_back_tax')->dropDownList(['all'=>'全部','1'=>'是','2'=>'否'],['value'=>!empty($model->is_back_tax)?$model->is_back_tax : ''])->label('是否退税') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'level_audit_status')->dropDownList(['1'=>'同意','4'=>'采购驳回','all'=>'全部'],['value'=>$model->level_audit_status],['prompt' => '请选择']) ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'amount_1')->textInput(['placeholder' => '20'])->label('数量1') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'amount_2')->textInput(['placeholder' => '50'])->label('数量2') ?>
    </div>
    <div class="col-md-2"><?= $form->field($model, 'xiaoshou_zhanghao')->dropDownList(BaseServices::getXiaoshouZhanghao(),['value'=>$model->xiaoshou_zhanghao])->label('销售账号')?></div>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">需求审核时间</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PlatformSummarySearch[agree_time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PlatformSummarySearch[start_time]',
                'endAttribute' => 'PlatformSummarySearch[end_time]',
//                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-6 month"))],
//                'endInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-1 day"))],
                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i:s',strtotime("-6 month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59:59',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="index">重置</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
