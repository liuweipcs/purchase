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
        'action' => $view=='index' ? ['index'] : ['supplier-integrat'],
        'method' => 'get',

    ]); ?>

    <div class="col-md-1"><?= $form->field($model, 'create_user_name')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入申请人 ...'],
            'data' =>BaseServices::getBuyer('name'),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('申请人员');
        ?>
    </div>
    <div class="col-md-1"><?= $form->field($model,'sku')->textInput(['placeholder'=>''])->label('SKU') ?></div>
    <?php if($view == 'index'){?>
    <div class="col-md-1"><?= $form->field($model,'is_sample')->dropDownList(['all'=>'全部','1'=>'待确认','2'=>'不拿样','3'=>'拿样'])->label('是否拿样') ?></div>
    <?php }?>
    <?php if($view == 'integrat'){ ?>
    <div class="col-md-1"><?=$form->field($model, "status")->dropDownList(['all'=>'全部','2'=>'通过','3'=>'不通过'],['class' => 'form-control'])->label('审核状态')?></div>
    <div class="col-md-1"><?=$form->field($model, "integrat_status")->dropDownList(['all'=>'全部','1'=>'待确认','2'=>'整合成功','3'=>'整合失败'],['class' => 'form-control'])->label('整合状态')?></div>
    <div class="col-md-1"><?=$form->field($model, "group")->dropDownList(['1'=>'供应链团队','0'=>'全部','2'=>'其他部门'],['class' => 'form-control'])->label('申请人部门')?></div>
    <?php }?>
    <?php if($view == 'index'){?>
    <div class="col-md-1"><?=$form->field($model, "type")->dropDownList(['2'=>'价格/税点修改','1'=>'供应商修改','4'=>'链接修改','3'=>'全部修改','5'=>'添加备用供应商','6'=>'采购单审核修改'],['class' => 'form-control','prompt' => '请选择'])->label('修改类型')?></div>
    <div class="col-md-1"><?=$form->field($model, "check_status")->dropDownList(['1'=>'待确定','2'=>'合格','3'=>'不合格'],['class' => 'form-control','prompt' => '请选择'])->label('质检状态')?></div>
    <?php }?>
    <?php if($view == 'integrat'){?>
        <div class="col-md-1"><?=$form->field($model, "type")->dropDownList(['0'=>'全部','2'=>'价格修改','1'=>'供应商修改','4'=>'链接修改','3'=>'全部修改','5'=>'添加备用供应商','6'=>'采购单审核修改'],['class' => 'form-control','prompt' => '请选择'])->label('修改类型')?></div>
    <?php }?>
    <div class="col-md-1">
        <?= $form->field($model, 'old_supplier_code')->widget(Select2::classname(), [
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
        ])->label('原供货商');
        ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'new_supplier_code')->widget(Select2::classname(), [
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
        ])->label('现供货商');
        ?>
        </div>
        <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">申请提交时间：</label><?php
            $addon = <<< HTML
        <span class="input-group-addon">
            <i class="glyphicon glyphicon-calendar"></i>
        </span>
HTML;
            echo '<div class="input-group drp-container">';
            echo DateRangePicker::widget([
                    'name'=>'SupplierUpdateApplySearch[time]',
                    'useWithAddon'=>true,
                    'convertFormat'=>true,
                    'startAttribute' => 'SupplierUpdateApplySearch[apply_start_time]',
                    'endAttribute' => 'SupplierUpdateApplySearch[apply_end_time]',
                    'startInputOptions' => ['value' => !empty($model->apply_start_time) ? $model->apply_start_time : date('Y-m-d 00:00:00',strtotime("last year"))],
                    'endInputOptions' => ['value' => !empty($model->apply_end_time) ? $model->apply_end_time : date('Y-m-d 23:59:59',time())],
                    'pluginOptions'=>[
                        'locale'=>['format' => 'Y-m-d H:i:s'],
                    ]
                ]).$addon ;
            echo '</div>';
            ?></div>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">审核时间：</label><?php
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
                'startInputOptions' => ['value' => !empty($model->check_start_time) ? $model->check_start_time : date('Y-m-d 00:00:00',strtotime("last year"))],
                'endInputOptions' => ['value' => !empty($model->check_end_time) ? $model->check_end_time : date('Y-m-d 23:59:59',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$add ;
        echo '</div>';
        ?></div>
    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置',$view=='index' ? ['index'] : ['supplier-integrat'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
