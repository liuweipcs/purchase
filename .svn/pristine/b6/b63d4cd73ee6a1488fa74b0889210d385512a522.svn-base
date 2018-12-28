

<?php
use yii\widgets\ActiveForm;
use app\services\SupplierServices;
use \kartik\datetime\DateTimePicker;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use \app\models\PurchaseHistory;
use app\models\PlatformSummary;
use app\services\PurchaseOrderServices;
use app\services\SupplierGoodsServices;
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
$this->title = '创建采购计划单';
$this->params['breadcrumbs'][] = ['label' => '采购订单确认', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
Modal::begin([
    'id' => 'create-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    'footer' => '<a href="#" class="btn btn-primary closes" data-dismiss="modal">Close</a>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗

    ],
]);
Modal::end();

$ordermodel->transit_warehouse=!empty($temporay['0']->product_id)?PlatformSummary::getSku($temporay['0']->product_id,'transit_warehouse'):'AFN';
$ordermodel->is_expedited='1';
?>
<h4><span class="glyphicon glyphicon-heart" style="color: red" aria-hidden="true"></span>温馨小提示:如果是供应商没有,代表这个sku在通途没有下过单,需要选择一个供应商</h4>
<div class="purchase-order-form">
    <?php $form = ActiveForm::begin([
        'id'=>'proform',
    ]); ?>
    <div class="row">
        <div class="col-md-2">
            <?php

           $fullName = \yii\helpers\ArrayHelper::getValue($temporay, function ($temporay, $defaultValue) {
                return !empty($temporay['0']->product_id)?PlatformSummary::getSku($temporay['0']->product_id,'purchase_warehouse'):'';


            });
            $sku = \yii\helpers\ArrayHelper::getValue($temporay, function ($temporay, $defaultValue) {

                return !empty($temporay['0']->sku)?$temporay['0']->sku:'';

            });
            $name = \app\models\SupplierQuotes::getFiled($sku,'suppliercode');
            $name = BaseServices::getSupplierName($name,'supplier_name');
            ?>
            <?= $form->field($ordermodel, 'warehouse_code')->widget(Select2::classname(), [
                'options' => ['placeholder' => '请选仓库 ...','value'=>!empty($fullName)?$fullName:''],
                'data'=>BaseServices::getWarehouseCode(),
                'pluginOptions' => [
                    'placeholder' => 'search ...',
                    'allowClear' => true,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
                    ],
                  /*  'ajax' => [
                        'url' => $url,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],*/
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(res) { return res.text; }'),
                    'templateSelection' => new JsExpression('function (res) { return res.text; }'),
                ],
            ]);
            ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($ordermodel, 'is_expedited')->dropDownList(['1'=>'不加急','2'=>'加急采购单'],['prompt' => '请选择']) ?>
        </div>

        <div class="col-md-4">
            <?php //if($model->isNewRecord){$model->is_transit='0';} echo $form->field($model, 'is_transit')->radioList(['0'=>'需要中转','1'=>'直发'])->label('') ?>

            <div class="form-group field-purchaseorder-is_transit">
                <label class="control-label"></label>
                <input type="hidden" name="PurchaseOrder[is_transit]" value="">
                <input type="hidden" name="PurchaseOrder[purchase_type]" value="" class="purchase_type">
                <div id="purchaseorder-is_transit">
                    <label><input id="check-radio1" type="radio" name="PurchaseOrder[is_transit]" value="1" checked=""> 需要中转</label>
                    <label><input id="check-radio2" type="radio" name="PurchaseOrder[is_transit]" value="0" > 直发</label>
                </div>
                <div class="help-block"></div>
            </div>

        </div>

        <div class="col-md-4" id="warehouse">
            <?= $form->field($ordermodel, 'transit_warehouse')->dropDownList(['shzz'=>'宁波中转仓库','AFN'=>'东莞中转仓库'],['prompt' => '请选中转仓']) ?>

        </div>

        <!-- <div class="col-md-4">
            <?/*= $form->field($ordermodel, 'pur_type')->dropDownList(PurchaseOrderServices::getPurType(),['prompt' => '请选补货方式']) */?>
        </div>-->

        <div class="col-md-4">
            <?= $form->field($ordermodel, 'supplier_code')->widget(Select2::classname(), [
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
            ]);
            ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($purchasenote, 'note')->textarea(['placeholder' => '请把下面的物流类型给加上吧！'])->label('采购计划说明') ?>
            </div>
        <?php if($temporay){?>
        <table class="table table-bordered">

            <thead>
            <tr>
                <th>sku</th>
                <th>采购仓</th>
                <th>中转仓</th>
                <th>产品名</th>
                <th>供应商(仅供参考)</th>
                <th>物流类型</th>
                <th>平台</th>
                <th>采购单价</th>

                <th>采购数量</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if($temporay){
            foreach($temporay as $k=>$v){
                ?>
                <tr>
                    <td><input type="text" name="PurchaseOrder[items][<?=$k?>][sku]" value="<?=$v->sku?>" readonly></td>
                    <td><?php
                        $purchase_warehouse = PlatformSummary::getSku($v->product_id,'purchase_warehouse');
                        echo $purchase_warehouse = !empty($purchase_warehouse)?BaseServices::getWarehouseCode($purchase_warehouse):'';
                       ?></td>
                    <td><?php
                        $transit_warehouse = PlatformSummary::getSku($v->product_id,'transit_warehouse');
                        echo $purchase_warehouse = !empty($transit_warehouse)?BaseServices::getWarehouseCode($transit_warehouse):'';
                        ?></td>
                    <td><?=$v->title?$v->title:\app\models\ProductDescription::getFiled($v->sku,'title')?></td>
                    <td><?php
                        $bS = \app\models\SupplierQuotes::getFiled($v->sku,'suppliercode');
                        echo !empty($bS->suppliercode)?BaseServices::getSupplierName($bS->suppliercode):'';
                        ?></td>
                    <td><?php
                        $b=PlatformSummary::getSku($v->product_id,'transport_style');
                        echo !empty($b)?PurchaseOrderServices::getTransport($b):'';?></td>
                    <td><?=$b=PlatformSummary::getSku($v->product_id,'platform_number');?></td>
                    <td><input  type="text" name="PurchaseOrder[items][<?=$k?>][purchase_price]" value="<?php
                        $bss=\app\models\SupplierQuotes::getFiled($v->sku,'supplierprice');
                       echo !empty($bss->supplierprice)?$bss->supplierprice:'10';?>"></td>

                    <input  type="hidden" name="PurchaseOrder[items][<?=$k?>][demand_number]" value="<?=PlatformSummary::getSku($v->product_id,'demand_number')?>"/>
                    <input  type="hidden" name="PurchaseOrder[items][<?=$k?>][create_id]" value="<?=PlatformSummary::getSku($v->product_id,'create_id')?>"/>
                    <input  type="hidden" name="PurchaseOrder[items][<?=$k?>][create_time]" value="<?=PlatformSummary::getSku($v->product_id,'create_time')?>"/>
                   <td><input  type="number" name="PurchaseOrder[items][<?=$k?>][purchase_quantity]" value="<?=$v->purchase_quantity?$v->purchase_quantity:PlatformSummary::getSku($v->product_id,'purchase_quantity')?>"></td>
                </tr>
            <?php }} ?>
            </tbody>

        </table>
        <?php }?>

</div>

    <?= Html::a('添加产品', ['product-index'], ['class' => 'btn btn-success','id'=>'add-product','data-toggle' => 'modal', 'data-target' => '#create-modal',]) ?>
    <?php Html::a('导入产品', ['import-product'], ['class' => 'btn btn-success','id'=>'import-product','data-toggle' => 'modal', 'data-target' => '#create-modal',]) ?>
    <?= Html::a('清除产品', ['eliminate'], ['class' => 'btn btn-success']) ?>
    <?php Html::a('模板', ['template'], ['class' => '']) ?>


<div class="form-group" style="margin-top: 10px;">
    <?= Html::submitButton($ordermodel->isNewRecord ? '提交' : '更新', ['class' => $ordermodel->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>
</div>

<?php
$js=<<<JS
    $(document).on('click', '#add-product', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $(document).on('click', '#import-product', function () {
        $.get($(this).attr('href'), {},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    //$('#warehouse').hide();
    //默认赋值为1
    $('.purchase_type').attr('value',2);
    $('#check-radio2').on('click', function(){
      $('#warehouse').hide();
      $('.purchase_type').attr('value',2);
    });

    $('#check-radio1').on('click', function(){
       $('#warehouse').show();
       $('.purchase_type').attr('value',2);
    });
JS;

$this->registerJs($js);
?>
