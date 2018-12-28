<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\PurchaseOrderServices;

?>
<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',

    ]); ?>

    <div class="col-md-1"><?= $form->field($model, 'buyer')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购员 ...'],
            'data' =>BaseServices::getEveryOne('','name'),
            'pluginOptions' => [

                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                /*'ajax' => [
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

    <div class="col-md-1"><?= $form->field($model, 'items.sku')->label('SKU') ?></div>

    <div class="col-md-1">
        <?= $form->field($model, 'purchas_status')->dropDownList(
            [
                '99'=>'未全部到货',
                '3'=>'已审批',
                '6'=>'全到货',
                '7'=>'等待到货',
                '8'=>'部分到货等待剩余',
                '9'=>'部分到货不等待剩余',
                '10'=>'已作废'
            ],
            ['prompt' => '请选择']
        )->label('订单状态') ?>
    </div>

    <div class="col-md-1"><?= $form->field($model, 'all_status')->dropDownList(PurchaseOrderServices::getAllOrdersStatus(),['prompt' => '请选择']) ?></div>

    <div class="col-md-1"><?= $form->field($model, 'pur_number')->label('采购单号') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'supplier_name')->label('供应商') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'create_type')->dropDownList(PurchaseOrderServices::getCreateType(), ['prompt' => '请选择'])->label('订单类型') ?></div>

    <div class="col-md-1"><?= $form->field($model, 'audit_return')->dropDownList(PurchaseOrderServices::getAuditReturn(), ['prompt' => '请选择']) ?></div>

    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->dropDownList(BaseServices::getWarehouseCode(), ['prompt' => '请选择'])->label('仓库名称') ?></div>

    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label><?php
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
            'startInputOptions' => ['value' => date('Y-m-d H:i:s',strtotime("last month"))],
            'endInputOptions' => ['value' => date('Y-m-d H:i:s',time())],
            'pluginOptions'=>[
                'locale'=>['format' => 'Y-m-d H:i:s'],
            ]
        ]).$addon ;
        echo '</div>';
        ?></div>


    <div class="form-group col-md-1" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index'],['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
