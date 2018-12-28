<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\purchaseOrderServices;
use kartik\select2\Select2;
use app\services\BaseServices;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderItemsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-items-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'sku') ?></div>
    <div class="col-md-1">
        <?php // echo $form->field($model, 'open.status')->dropDownList(purchaseOrderServices::getTicketOpenStatus(),[])->label('订单状态')?>
        <?= $form->field($model, 'open.status')->dropDownList([''=>'请选择', '1'=>'未完成', '2'=>'完成'])->label('订单状态')?>
    </div>

    <div class="col-md-1">
        <?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...','id'=>'buyer'],
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
        ])->label('采购员');
        ?>
    </div>
    <div class="col-md-1"><?= $form->field($model, 'declare_name')->label('报关品名') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'invoice_code')->label('发票编码') ?></div>
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
        <label class="control-label">开票日期</label>
        <input class="open_time form-control"  name='PurchaseOrderItemsSearch[open_time]' value="<?=$model->open_time?>">
    </div>
    <div class="form-group col-md-1" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<<JS


layui.use('laydate', function(){
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
            elem: '.open_time', //指定元素
            range:'~',
            type:'date'
        });
});
JS;
$this->registerJs($js);
?>
