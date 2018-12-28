<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use app\services\BaseServices;
use yii\web\JsExpression;
use app\models\PurchaseUser;

/* @var $this yii\web\View */
/* @var $model app\models\GroupAuditConfigSearch */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="group-audit-config-search">

    <?php $form = ActiveForm::begin([
        'action' => ['excep'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1">
        <?= $form->field($model, 'time_limit')->label('时间节点(>)(H)') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_name')->textInput(['placeholder' => '代码或全称'])->label('供应商名称') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'sku')->label('SKU') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'product_status')->dropDownList(\app\services\SupplierGoodsServices::getProductStatus())->label('产品状态') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'creater')->label('开发人员') ?>
    </div>
    <!--<div class="col-md-1"><?/*= $form->field($model, 'default_buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        */?>
    </div>-->
    <div class="col-md-1">
        <?= $form->field($model, 'default_buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选择'],
            'data'=>PurchaseUser::getBuyerAndGroup(),
            'pluginOptions' => ['width'=>'130px'],
        ])->label('采购员') ?>
    </div>
    <div class="form-group col-md-2"  style="margin-top: 24px;float:left">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
