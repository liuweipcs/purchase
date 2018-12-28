<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\BaseServices;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;
use app\services\SupplierServices;
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
<!--    <div class="col-md-1">--><?php //$form->field($model, 'pur_type')->dropDownList(PurchaseOrderServices::getPurType(),['prompt' => 'please choose']) ?><!--</div>-->
    <div class="col-md-2" ><label class="control-label" for="purchaseorderpaysearch-applicant">时间：</label><?php
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
//                'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
//                'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
                'startInputOptions' => ['value' => !empty($model->start_time) ? date('Y-m-d',strtotime($model->start_time)) : date('Y-m-d',strtotime("last month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? date('Y-m-d',strtotime($model->end_time)) : date('Y-m-d',time())],
                'pluginOptions'=>[
                    'locale'=>['format' => 'Y-m-d'],
                ]
            ]).$addon ;
        echo '</div>';
        ?></div>
    <div class="col-md-1"><?= $form->field($model, 'items.sku')->label('sku') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>
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
    <div class="col-md-2">
        <?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...','value' =>!empty($name)?$name:''],
            'data'=>BaseServices::getEveryOne('','name'),
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
        ])->label('采购员');
        ?>
    </div>
    <div class="col-md-2"><?= $form->field($model, 'warehouse_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请选仓库'],
            'data' =>BaseServices::getWarehouseCode(),
            'pluginOptions' => [],
        ])?></div>


    <?= Html::input('hidden', 'source', $model->source) ?>

    <div class="col-md-1"><?= $form->field($model, 'account_type')->dropDownList(SupplierServices::getSettlementMethod(), ['prompt' => '请选择'])->label('结算方式') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pay_type')->dropDownList([''=>'全部',2=>'支付宝',3=>'银行卡转帐',4=>'PayPal',5=>'富友'])->label('支付方式')?></div>

    <div class="col-md-2">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>
    <div  class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index', 'source' => $model->source], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
