<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;

if (isset($params['PurchaseOrder']['purchase_type']) && !empty($params['PurchaseOrder']['purchase_type'])) {
    $purchase_type = $params['PurchaseOrder']['purchase_type'];
} else {
    $purchase_type = 0;
}
$prompt = PurchaseOrderServices::getPurchaseTypeList($purchase_type);
$list = PurchaseOrderServices::getPurchaseTypeList($purchase_type, true);

if (isset($params['WarehouseResults']['time']) && !empty($params['WarehouseResults']['time'])) {
    $start_time = $params['WarehouseResults']['start_time'];
    $end_time = $params['WarehouseResults']['end_time'];
} else {
    $start_time = date("Y-m-d H:i:s",strtotime("-10 day"));
    $end_time = date("Y-m-d H:i:s",time());
}


?>




<?php $form = ActiveForm::begin(['action' => ['index'], 'method' => 'get']); ?>
<?= Html::input('hidden', 'is_warehouse', $is_warehouse) ?>


<div class="col-md-1">
    <?= $form->field($model, 'purchase_type')->dropDownList($list,['prompt' => $prompt])->label('类型'); ?>
</div>

<div class="col-md-3" ><label class="control-label" for="purchaseorderpaysearch-applicant">入库时间/付款时间    </label>
<?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
            'name'=>'WarehouseResults[time]',
            'useWithAddon'=>true,
            'convertFormat'=>true,
            'startAttribute' => 'WarehouseResults[start_time]',
            'endAttribute' => 'WarehouseResults[end_time]',
            'startInputOptions' => ['value' => $start_time],
            'endInputOptions' => ['value' => $end_time],
            'pluginOptions'=>[
                'locale'=>['format' => 'Y-m-d H:i:s'],
            ]
        ]).$addon ;
        echo '</div>';
        ?></div>

<div class="form-group col-md-2" style="margin-top: 24px;float:left">
    <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('重置', ['index'], ['class' => 'btn btn-default']) ?>
</div>

<?php ActiveForm::end(); ?>


