<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\services\BaseServices;
use yii\web\JsExpression;
use kartik\daterange\DateRangePicker;
use app\services\PurchaseOrderServices;

$sku = !empty($params['StockSearch']['sku']) ? $params['StockSearch']['sku'] : null;
//采购建议状态
$state = null;
//采购建议状态
if ( isset($params['StockSearch']['state'])) {
    if (!empty($params['StockSearch']['state']) || $params['StockSearch']['state']==='0') {
        $state = $params['StockSearch']['state'];
    }
}
$purchase_type = !empty($params['StockSearch']['purchase_type']) ? $params['StockSearch']['purchase_type'] : null;
$purchas_status = !empty($params['StockSearch']['purchas_status']) ? $params['StockSearch']['purchas_status'] : null;
$sourcing_status = !empty($params['StockSearch']['sourcing_status']) ? $params['StockSearch']['sourcing_status'] : null;
$warn_status = !empty($params['StockSearch']['warn_status']) ? $params['StockSearch']['warn_status'] : null;
$suggest_note = !empty($params['StockSearch']['suggest_note']) ? $params['StockSearch']['suggest_note'] : null;
$warehouse_code = !empty($params['StockSearch']['warehouse_code']) ? $params['StockSearch']['warehouse_code'] : null;
$is_pass = !empty($params['StockSearch']['is_pass']) ? $params['StockSearch']['is_pass'] : null;

$audit_time = !empty($params['StockSearch']['audit_time']) ? $params['StockSearch']['audit_time'] : null;
$payer_time = !empty($params['StockSearch']['payer_time']) ? $params['StockSearch']['payer_time'] : null;
$date_eta = !empty($params['StockSearch']['date_eta']) ? $params['StockSearch']['date_eta'] : null;
$buyer = !empty($params['StockSearch']['buyer']) ? $params['StockSearch']['buyer'] : null;
$product_line = !empty($params['StockSearch']['product_line']) ? $params['StockSearch']['product_line'] : null;

unset($params);
?>

<div class="overseas-warehouse-goods-tax-rebate-search purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['test'],
        'method' => 'get',
    ]); ?>
    <input type="hidden" name='user' value="<?=$user?>">
    <input type="hidden"  name='num' value="<?=$user_number?>">
    <div class="container-fluid">
        <div class="col-md-1">
            <?= $form->field($model, 'sku')->textInput(['value'=>$sku, 'placeholder' => 'YS00840-01', 'required'=>true]); ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'purchase_type')->dropDownList(PurchaseOrderServices::getPurchaseTypeList(),['value'=>$purchase_type])->label('采购类型'); ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'state')->dropDownList(PurchaseOrderServices::getState(),['value'=>$state])->label('采购建议状态'); ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'purchas_status')->dropDownList([''=>'请选择',5=>'部分到货', 6=>'全到货', 7=>'等待到货'],['value'=>$purchas_status])->label('采购到货状态'); ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'sourcing_status')->dropDownList(PurchaseOrderServices::getSourcingStatus(),['value'=>$sourcing_status])->label('货源状态'); ?>
        </div>
        <div class="col-md-1">
            <?= $form->field($model, 'warn_status')->dropDownList(PurchaseOrderServices::getWarnStatus(),['value'=>$warn_status])->label('预警状态'); ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'suggest_note')->textInput(['value' => $suggest_note, 'placeholder'=>'备注原因模糊搜索'])->label('备注') ?>
        </div>
        <div class="col-md-1">
            <?php echo $form->field($model, 'is_pass')->dropDownList([''=>'请选择',1=>'是', 2=>'否'], ['value'=>$is_pass])->label('是否超过权限交期');?>
        </div>
        <div class="col-md-1">
            <?php echo $form->field($model, 'buyer')->textInput(['value'=>$buyer])->label('采购员');?>
        </div>

        <div class="col-md-2">
            <?php $form->field($model, 'warehouse_code')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请选仓库 ...','id'=>'warehouse_code', 'value' => $warehouse_code,],
                'data'=>BaseServices::getWarehouseCode(),
                'pluginOptions' => [
                    'multiple' => true,
                    'allowClear' => true,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ])->label('仓库');
            ?>
        </div>
    </div>
    <div class="container-fluid">
        <div class="col-md-2">
            <label class="control-label">采购单生成时间</label>
            <input class="audit_time form-control"  name='StockSearch[audit_time]' value="<?=$audit_time?>">
        </div>
        <div class="col-md-2">
            <label class="control-label">付款时间</label>
            <input class="payer_time form-control"  name='StockSearch[payer_time]' value="<?=$payer_time?>">
        </div>
        <div class="col-md-2">
            <label class="control-label">权均到货时间</label>
            <input class="date_eta form-control"  name='StockSearch[date_eta]' value="<?=$date_eta?>">
        </div>
        <div class="form-group col-md-2" style="margin-top: 24px;float:left">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('重置', ['test'],['class' => 'btn btn-default']) ?>

            &nbsp;&nbsp;&nbsp;&nbsp;
            <?php if(isset($user_number) and $user_number) {?>
            <?= Html::a('编辑显示内容', ['test'],['class' => 'btn btn-success btn-edit-fields','data-toggle'=>"modal",'data-target'=>"#create-modal",]) ?>
            <?php }?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$this->registerJsFile('@web/css/layui/layui.all.js', ['depends' => ['app\assets\AppAsset']]);

$js = <<<JS
$(function() {
    /**
     * 时间
     */
    layui.use('laydate', function(){
        var laydate = layui.laydate;
        //执行一个laydate实例
        laydate.render({
            elem: '.date_eta', //指定元素-权均到货时间
            range:'~',
            type:'date'
        });
        laydate.render({
            elem: '.payer_time', //指定元素
            range:'~',
            type:'date'
        });
        laydate.render({
            elem: '.audit_time', //指定元素
            range:'~',
            type:'date'
        });
    });
});
JS;
$this->registerJs($js);
?>