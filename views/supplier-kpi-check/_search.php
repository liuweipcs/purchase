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
        'action' => ['index'],
        'method' => 'get',

    ]); ?>
    <div class="col-md-2">
        <?= $form->field($model, 'supplier_code')->widget(Select2::classname(), [
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
        <?php echo '<label>统计月份</label>';
        echo \kartik\date\DatePicker::widget([
            'name' => 'SupplierKpiCaculte[month]',
            'options' => ['placeholder' => '','id'=>'supplierkpicaculte-month'],
            //注意，该方法更新的时候你需要指定value值
            'value' => $model->month ? date('Y-m',strtotime($model->month)) : date('Y-m',time()),
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm',
                'todayHighlight' => true
            ]
        ]);?></div>
    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置',['index']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
