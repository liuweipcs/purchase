<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\StockinSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    .col-md-1{
        padding-left: 0px;
    }
</style>
<div class="stockin-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
 <!--   <div class="col-md-1"> -->  <?php //$form->field($model, "product_status")->dropDownList(\app\services\SupplierGoodsServices::getProductStatus(),['class' => 'form-control','prompt' => '请选择']) ?><!--  </div> --> 

   <div class="col-md-2"><?= $form->field($model, 'product_category_id')->dropDownList(\app\services\BaseServices::getCategory(), ['prompt' => '请选择'])->label('分类') ?></div>
    <div class="col-md-2"><?=  $form->field($model, 'sku')->textInput(['placeholder'=>'']) ?></div>
    <div class="col-md-2"><?php /* $form->field($model, 'suppliercode')->dropDownList(\app\services\BaseServices::getSupplier(), ['prompt' => 'please choose'])->label('供应商代码') */?>
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
                    'data' => new JsExpression('function(params) { return {q:params.term,status:null}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?>
    </div>
    <div class="col-md-2"><?=  $form->field($model, 'purchase_type')->dropDownList(array(1=>'国内',2=>'海外',3=>'FBA'), ['prompt' => 'please choose'])->label('采购类型') ?></div>
        <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">采购时间</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name'=>'PurchaseAmountSearch[agree_time]',
                'useWithAddon'=>true,
                'convertFormat'=>true,
                'startAttribute' => 'PurchaseAmountSearch[start_time]',
                'endAttribute' => 'PurchaseAmountSearch[end_time]',
//                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-6 month"))],
//                'endInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-1 day"))],
                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i:s',strtotime("-6 month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59:59',time()-86400)],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d H:i:s'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <div class="col-md-1"><?php // $form->field($model, 'buyer')->dropDownList(\app\services\BaseServices::getEveryOne(), ['prompt' => 'please choose'])->label('默认采购员') ?></div>




 


    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
