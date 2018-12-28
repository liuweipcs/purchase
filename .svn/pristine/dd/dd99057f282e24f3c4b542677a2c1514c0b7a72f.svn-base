<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\PlatformSummaryServices;
use app\services\SupplierServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
/* @var $this yii\web\View */
/* @var $model app\models\TodayListSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['sales-index'],
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
    <div class="col-md-2"><?= $form->field($model, 'product_line')->widget(Select2::classname(), [
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
    <div class="col-md-1">
        <?= $form->field($model, 'is_purchase')->dropDownList(['all'=>'全部','1'=>'未生成','2'=>'已生成'],['value'=>!empty($model->is_purchase)?$model->is_purchase : ''])->label('是否生成采购单') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'is_back_tax')->dropDownList(['all'=>'全部','1'=>'是','2'=>'否'],['value'=>!empty($model->is_back_tax)?$model->is_back_tax : ''])->label('是否退税') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'level_audit_status')->dropDownList(['all'=>'全部','0'=>'待同意','1'=>'同意','2'=>'驳回','3'=>'撤销','4'=>'采购驳回','5'=>'删除','6'=>'规则拦截','7'=>'待提交'],['value'=>!empty($model->level_audit_status)?$model->level_audit_status : 0])->label('需求状态') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'init_level_audit_status')->dropDownList(PlatformSummaryServices::getInitLevelAuditStatus(),['value'=>$model->init_level_audit_status])->label('采购主管审核状态') ?>
    </div>
    <div class="col-md-2"><?= $form->field($model, 'group_id')->dropDownList(BaseServices::getAmazonGroup(),['value'=>$model->group_id])?></div>
    <div class="col-md-2"><?= $form->field($model, 'xiaoshou_zhanghao')->dropDownList(BaseServices::getXiaoshouZhanghao(),['value'=>$model->xiaoshou_zhanghao])->label('销售账号')?></div>
    <div class="col-md-3" ><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PlatformSummarySearch[time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PlatformSummarySearch[start_time]',
                'endAttribute' => 'PlatformSummarySearch[end_time]',
                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>

    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="sales-index">重置</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
