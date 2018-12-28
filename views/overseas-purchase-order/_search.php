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
?>
<div style="margin-left: 20px;">
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
]); ?>
<div class="row">
    <?= Html::input('hidden', 'source', $model->source) ?>
    <div class="col-md-1">
        <label>合同号</label>
        <input type="text" name="compact_number" value="<?= $model->compact_number ?>" class="form-control">
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'pur_number', [
            'inputOptions' => [
                'placeholder' => '多个用空格隔开',
                'class' => 'form-control',
            ],
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('采购员');
        ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'purchas_status')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入仓库到货状态 ...'],
            'data' =>['99'=>'未全部到货','3'=>'已审批','6'=>'全到货','7'=>'等待到货','8'=>'部分到货等待剩余','9'=>'部分到货不等待剩余','10'=>'已作废'],
            'pluginOptions' => [
                'multiple' => true,
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) {return res.text; }'),
                'templateSelection' => new JsExpression('function (res) {return res.text; }'),
            ],
        ])->label('仓库到货状态');
        ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'arrival_status')->dropDownList(PurchaseOrderServices::getArrivalStatus(),['prompt' => '请选择'])->label('采购到货状态') ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'pay_status')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入付款状态 ...'],
            'data' =>PurchaseOrderServices::getPayStatus(),
            'pluginOptions' => [
                'multiple' => true,
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) {return res.text; }'),
                'templateSelection' => new JsExpression('function (res) {return res.text; }'),
            ],
        ])->label('付款状态');
        ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入供应商 ...','value'=>!empty($model->supplier_code) ? $model->supplier_code :''],
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
</div>
<div class="row">
    <div class="col-md-1"><?= $form->field($model, 'items.sku', [
        'inputOptions' => [
            'placeholder' => '多个用空格隔开',
            'class' => 'form-control',
        ],
    ])->label('sku') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'ship.express_no')->label('物流单号') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->dropDownList(BaseServices::getWarehouseCode(), ['prompt' => '请选择']) ?></div>
<div class="col-md-3" ><label class="control-label" for="purchaseorderpaysearch-applicant">审核时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
echo '<div class="input-group drp-container">';
echo DateRangePicker::widget([
        'name'=>'PurchaseOrderSearch[time]',
    'useWithAddon'=>true,
    'convertFormat'=>true,
    'startAttribute' => 'PurchaseOrderSearch[start_time]',
    'endAttribute' => 'PurchaseOrderSearch[end_time]',
    'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("-6 month"))],
    'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
    'pluginOptions'=>[
        'locale'=>['format' => 'Y-m-d H:i:s'],
    ]
]).$addon ;
echo '</div>';
?>
</div>
    <div class="form-group" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index', 'source' => $model->source], ['class' => 'btn btn-default']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
</div>