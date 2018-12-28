<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;
use app\services\SupplierServices;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
?>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
]); ?>

<?= Html::input('hidden', 'source', $model->source) ?>


    <div class="col-md-1"><?= $form->field($model,'pay_status')->dropDownList(PurchaseOrderServices::getPayStatus(),['prompt' => '请选择']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_number', [
            'inputOptions' => [
                'placeholder' => '多个用空格隔开',
                'class' => 'form-control',
            ],
        ]) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_type')->dropDownList(['all'=>'全部',1=>'国内',2=>'海外',3=>'FBA'])->label('采购类型')?></div>
    <div class="col-md-1"><?= $form->field($model, 'pur_status')->dropDownList(['6'=>'全到货','7'=>'等待到货','8'=>'部分到货等待剩余','9'=>'部分到货不等待剩余','10'=>'已作废'],['prompt' => '请选择'])->label('采购状态')?></div>

    <div class="col-md-1">
        <?= $form->field($model, 'applicant')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员...'],
            'data'    => BaseServices::getEveryOne(),
            'pluginOptions' => [
                'language'  => [
                    'errorLoading'  => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup'      => new JsExpression('function (markup) { return markup; }'),
                'templateResult'    => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ]);
        ?>
    </div>

<div class="col-md-2"><label class="control-label" for="purchaseorderpaysearch-applicant">申请时间：</label><?php
    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
    echo '<div class="input-group drp-container">';
    echo DateRangePicker::widget([
            'name'=>'created_at1',
            'useWithAddon'=>true,
            'convertFormat'=>true,
            'startAttribute' => 'start_time',
            'endAttribute' => 'end_time',
            'startInputOptions' => ['value' => !empty($model->start_time)?$model->start_time:date('Y-m-d H:i',strtotime("-3 month"))],
            'endInputOptions' => ['value' => !empty($model->end_time)?$model->end_time:date('Y-m-d H:i',time())],
            'pluginOptions'=>[
                'locale'=>['format' => 'Y-m-d H:i'],
            ]
        ]).$addon ;
    echo '</div>';
    ?></div>




<?php $l = \app\models\AlibabaZzh::getPayer(); ?>


<div class="col-md-1"><?= $form->field($model, 'chuna')->dropDownList($l, ['prompt' => '请选择', 'value' => $model->chuna])->label('出纳') ?></div>


<div class="col-md-1"><?= $form->field($model, 'pay_type')->dropDownList(['all'=>'全部',2=>'支付宝',3=>'银行卡转帐',4=>'PayPal',5=>'富友'])->label('支付方式')?></div>









<!--
<div class="col-md-2"><label class="control-label" for="purchaseorderpaysearch-applicant">付款时间：</label><?php
/*    $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
    echo '<div class="input-group drp-container">';
    echo DateRangePicker::widget([
            'name'=>'payer_time',
            'useWithAddon' => true,
            'convertFormat' => true,
            'startAttribute' => 'p_start_time',
            'endAttribute' => 'p_end_time',
            'startInputOptions' => ['value' => !empty($model->p_start_time) ? $model->p_start_time : date('Y-m-d', strtotime("-3 month"))],
            'endInputOptions' => ['value' => !empty($model->p_end_time) ? $model->p_end_time : date('Y-m-d', time())],
            'pluginOptions' => [
                'locale' => ['format' => 'Y-m-d'],
            ]
        ]).$addon ;
    echo '</div>';
    */?></div>
-->

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
                    'data' => new JsExpression('function(params) { return {q:params.term,status:null}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('供应商');
        ?>
    </div>

    <div class="col-md-1"><?= $form->field($model, 'order_account')->label('账号') ?></div>
    <div class="clearfix"></div>


    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>

    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index', 'source' => $model->source], ['class' => 'btn btn-default']) ?>
    </div>

<?php ActiveForm::end(); ?>

