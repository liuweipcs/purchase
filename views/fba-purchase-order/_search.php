<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;

?>
<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= Html::input('hidden', 'source', $model->source) ?>
    <div class="col-md-1">
        <label>合同号</label>
        <input type="text" name="compact_number" value="<?= $model->compact_number ?>" class="form-control">
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
    <div class="col-md-1">
        <?= $form->field($model, 'account_type')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入结算方式 ...'],
            'data' =>SupplierServices::getSettlementMethod(),
            'pluginOptions' => [
                'allowClear' => true,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(res) { return res.text; }'),
                'templateSelection' => new JsExpression('function (res) { return res.text; }'),
            ],
        ])->label('结算方式');
        ?>
    </div>
    <div class="col-md-2"><?= $form->field($model, 'purchas_status')->widget(Select2::classname(), [
            'options' => ['placeholder' => '请输入采购单状态 ...','id'=>'status'],
            'data' =>['1'=>'待确认','2'=>'采购确认','3'=>'已审批','6'=>'全到货','7'=>'等待到货','8'=>'部分到货等待剩余','9'=>'部分到货不等待剩余','10'=>'已作废'],
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
        ])->label('采购单状态');
        ?>
    </div>
<div class="col-md-1">
    <?= $form->field($model, 'is_drawback')->dropDownList(['2'=>'退税','1'=>'不退税'],['prompt'=>'请选择'])->label('是否退税') ?>
</div>
<div class="col-md-1">
    <?= $form->field($model, 'pay_status')->dropDownList(PurchaseOrderServices::getPayStatus(),['prompt'=>'请选择'])->label('付款状态') ?>
</div>
    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>
    <div class="col-md-1"><?=$form->field($model, 'supplier_name')->label('供应商') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'items.sku')->label('sku') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'ship.express_no')->label('物流单号') ?></div>
    <div class="col-md-1"><?= $form->field($model, 'warehouse_code')->dropDownList(BaseServices::getWarehouseCode(), ['prompt' => '请选择']) ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'is_check_goods')->dropDownList(PurchaseOrderServices::getIsCheckGoods())->label('是否需要验货') ?>
    </div>
    <div class="col-md-1" ><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label><?php
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
            'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i:s',strtotime("last month"))],
            'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59:59',time())],
            'pluginOptions'=>[
                'locale'=>['format' => 'Y-m-d H:i:s'],
            ]
        ]).$addon ;
        echo '</div>';
        ?>
    </div>

    <div class="col-md-1">
        <?= $form->field($model, 'receive_goods')->dropDownList(['r_format'=>'正常回货','r_due'=>'即将到期','r_pastdue' => '已超期'],['prompt'=>'请选择'])->label('订单类型') ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'supplier_special_flag')->dropDownList(\app\services\SupplierServices::supplierSpecialFlag(),['prompt'=>'请选择'])->label('跨境宝供应商') ?>
    </div>

    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('重置', ['index', 'source'=>$model->source], ['class' => 'btn btn-default']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
