<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\PurchaseOrderServices;
?>
<div class="purchase-order-cancel-search">
<?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'get']); ?>
    <div class="col-md-1">
        <?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...', 'id'=>'buyer', 'value' => $model->buyer],
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
    <!-- -------------------------------------------------------------- -->
    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>
    <!--<div class="col-md-1"><?/*=$form->field($model, 'cs.sku')->label('SKU') */?></div>-->

    <div class="col-md-1"><?= $form->field($model, 'audit_status')->dropDownList(PurchaseOrderServices::getCancelAuditStatus(), ['prompt' => '请选择']) ?></div>
    <div class="col-md-1"><?= $form->field($model, 'por.pay_status')->dropDownList(PurchaseOrderServices::getReceiptStatus(), ['prompt' => '请选择'])->label('收款状态') ?></div>

    <!-- -------------------------------------------------------------- -->
    <div class="col-md-3">
        <label class="control-label" for="purchaseorderpaysearch-applicant">创建时间</label>
<?php
$addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
echo '<div class="input-group drp-container">';
echo DateRangePicker::widget([
        'name' => 'create_time',
        'value' => $model->create_time,
        'useWithAddon'=> false,
        'convertFormat'=> true,
        'initRangeExpr' => true,
        'startAttribute' => 'start_time',
        'endAttribute' => 'end_time',
        'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d 00:00',time())],
        'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59', time())],
        'pluginOptions' => [
            'locale' => ['format' => 'Y-m-d H:i'],
            'ranges' => [
                '今天' => ["moment().startOf('day')", "moment()"],
                '昨天' => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
                '最近7天' => ["moment().startOf('day').subtract(6, 'days')", "moment()"],
                '最近30天' => ["moment().startOf('day').subtract(29, 'days')", "moment()"],
                '本月' => ["moment().startOf('month')", "moment().endOf('month')"],
                '上月' => ["moment().subtract(1, 'month').startOf('month')", "moment().subtract(1, 'month').endOf('month')"],
            ]
        ],
    ]).$addon;
echo '</div>';
?>
    </div>


    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

<?php ActiveForm::end(); ?>
</div>
