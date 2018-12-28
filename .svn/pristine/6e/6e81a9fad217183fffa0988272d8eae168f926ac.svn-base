<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use kartik\datetime\DateTimePicker;
use app\models\PurchaseHistory;
use app\config\Vhelper;
use app\models\PurchaseOrderItems;
use kartik\select2\Select2;
use yii\web\JsExpression;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;

use kartik\date\DatePicker;
use app\models\ProductTaxRate;
use app\models\Product;
$suppurl = \yii\helpers\Url::to(['/supplier/search-supplier']);
$this->title = '合同采购确认';

$model = $models[0];

$settlement_ratio = $model->purchaseOrderPayType->settlement_ratio;
$freight_payer = $model->purchaseOrderPayType->freight_payer == 1 ? '甲方支付' : '乙方支付';
$freight_formula_mode = $model->purchaseOrderPayType->freight_formula_mode == 'weight' ? '重量' : '体积';

?>
<style type="text/css">
    .label-box {
        position: absolute;
        top: 34px;
        left: 0;
        width: 500px;
        border: 1px solid #3c8dbc;
        padding: 8px;
        display: none;
        z-index: 500;
        background-color: #fff;
    }
    .label-box span:hover {
        background-color: red !important;
        cursor: pointer;
    }
    .tc {
        color: red;
    }
    .is_drawback, .freight_formula_mode {
        width: 100px;
    }
</style>

<div class="my-box" style="margin-bottom: 45px;">
    <div class="bg-line">
        <span>1</span>
        <p>确认采购单信息</p>
    </div>
    <div class="bg-line no">
        <span>2</span>
        <p>确认合同信息</p>
    </div>
</div>

<div class="my-box">
    <h5>共有 <strong class="tc"><?= count($models) ?></strong> 个采购单，供应商名称为<b class="tc"><?= $model->supplier_name ?></b></h5>
</div>

<?php $form = ActiveForm::begin(['id' => 'compact-confirm']); ?>

<div class="my-box" style="border: 2px solid #3c8dbc; margin-bottom: 10px;">
    <h5><label class="label label-info">订单公共数据</label></h5>
    <div class="col-md-2">
        <label>采购来源</label>
        <input type="text" name="purchase_source" class="form-control" value="合同采购" disabled>
    </div>
    <div class="col-md-2">
    	<label>供应商</label>
    	<input type="text" name="purchase_source" class="form-control" value="<?php echo $model->supplier_name;?>" disabled>
    </div>
    <div class="col-md-2">
    	<label>支付方式</label>
        <input type="text" name="purchase_source" class="form-control" value="<?php echo SupplierServices::getDefaultPaymentMethod($model->pay_type);?>" disabled>
    </div>
    <div class="col-md-2">
    	<label>供应商运输</label>
    	<input type="text" name="purchase_source" class="form-control" value="<?php echo PurchaseOrderServices::getShippingMethod($model->shipping_method);?>" disabled>
    </div>

    <div class="col-md-2">
    	<label>结算方式</label>
    	<input type="text" name="purchase_source" class="form-control" value="<?php echo SupplierServices::getSettlementMethod($model->account_type);?>" disabled>
    </div>
	<div class="col-md-2">
    	<label>预计到货时间</label>
    	<input type="text" name="purchase_source" class="form-control" value="<?php echo $model->date_eta;?>" readonly>
    </div>
    <div class="col-md-2">
    	<label>是否中转</label>
    	<input type="text" name="purchase_source" class="form-control" value="<?php echo $model->is_transit == 1 ? '直发' : '是';?>" disabled>
    </div>
    <div class="col-md-2">
    	<label>中转仓库</label>
        <?php $transit_warehouses = ['shzz' => '上海中转仓库','AFN'=>'东莞中转仓库'];?>
        <input type="text" name="purchase_order" class="form-control" value="<?php echo PurchaseOrderServices::getTransitWarehouse($model->transit_warehouse) ?>" disabled>
    </div>
    <div class="col-md-2">
        <label>结算比例</label>
        <input type="text" class="form-control" value="<?= $settlement_ratio ?>" readonly>
    </div>
    <div class="col-md-2">
    	<label>是否退税</label>
    	<input type="text" class="form-control" value="<?= $model->is_drawback == 2 ? '是' : '否'; ?>" readonly>
    </div>
	<div class="col-md-2">
    	<label>运费支付</label>
    	<input type="text" class="form-control" value="<?= $freight_payer; ?>" readonly>
    </div>
    <div class="col-md-2">
    	<label>运费计算方式</label>
    	<input type="text" class="form-control" value="<?= $freight_formula_mode; ?>" readonly>
    </div>
    <p class="text_line"></p>

</div>

<?php foreach($models as $k => $v): ?>

<div class="my-box" style="border: 1px solid red;margin-bottom: 10px;">
    <label class="label label-info"><?= $k+1 ?></label>
    <label>PO号：</label>
    <?= $v->pur_number ?>
    <table class="table table-bordered" style="margin-top: 10px;">
        <thead>
        <tr>
            <td>图片</td>
            <td>出口退税税率</td>
            <td>采购开票点</td>
            <td>SKU</td>
            <td>产品名</td>
            <td>采购数量</td>
            <td>单价</td>
            <td>金额</td>
            <td>产品链接</td>
        </tr>
        </thead>
        <tbody class="bs">
        <?php
        $total = 0;
        $cancelInfo = \app\models\PurchaseOrderCancelSub::getCancelCtq([$v->pur_number]);// 获取采购单 SKU取消数量
        foreach($v->purchaseOrderItemsCtq as $k => $item):
            $sku = strtoupper($item->sku);
            if (!empty($cancelInfo[$v->pur_number][$sku]) && ($cancelInfo[$v->pur_number][$sku] == $item->ctq)) {// 全部取消的则不展示
                continue;
            }
            $totalprice = $item->ctq*$item->price;
            $total += $totalprice;
            $img = Vhelper::downloadImg($item->sku, $item->product_img,2);
            $img = Html::img($img, ['width' => 100]);
        ?>
            <tr>
                <td>
                    <?= Html::a($img, ['#'], ['class' => "img", 'data-skus' => $item['sku'], 'data-imgs' => $item->product_img, 'title' => '大图查看', 'data-toggle' => 'modal', 'data-target' => '#created-modal']) ?>
                </td>
                <td>
                    <?= ProductTaxRate::getRebateTaxRate($item->sku); ?>
                </td>
                <td>
                	<?= $v->is_drawback == 2 ? $item->pur_ticketed_point : 0; ?>
                </td>
                <td>
                    <?= Html::a($item->sku, ['#'], ['class' => "sales", 'data-sku' => $item->sku, 'title' => '销量统计', 'data-toggle' => 'modal', 'data-target' => '#created-modal']).(empty(\app\models\Product::findOne(['product_is_new'=>1,'sku'=>$item->sku]))?'':'<sub><font size="1" color="red">新</font></sub>') ?>
                </td>
                <td style="width: 200px;">
                    <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail']. $item->sku?>" target="_blank" title="<?= $item->name ?>"><?= $item->name?></a>
                </td>
                <td class="ctqs">
                	<?php echo $item->ctq;?>
                </td>
                <td>
                	<?php echo $item->price;?>
                </td>
                <td><?php echo $totalprice;?></td>
                <td>
                    <?php
                    $plink = $item->product_link ? $item->product_link : \app\models\SupplierQuotes::getUrl($item->sku);
                    echo Html::input('text', 'PurchaseOrderItems[product_link][]', $plink, ['readonly'=>true]) ?>
                    <a href='<?= $plink ?>' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a>
                </td>
            </tr>
        <?php endforeach; ?>

        <tr class="table-module-b1">
            <td class="total" colspan="10">总额：<b><?=round($total,4).'&nbsp;&nbsp;'.$v->currency_code?></b></td>
        </tr>
        <tr style="display:none">
            <td>确认备注</td>
            <td colspan="9">
                <textarea id="purchasenote-note" name="note[<?php echo $v->pur_number?>]" rows="3" class="form-control"><?= !empty($v->orderNote->note) ? $v->orderNote->note : ''; ?></textarea>
            </td>
        </tr>
        </tbody>
    </table>
    <p class="text_line"></p>
</div>

<?php endforeach; ?>

<button type="button" id="btn-submit" class="btn btn-primary abd_submit">保存并确认采购合同</button>

<?php ActiveForm::end(); ?>
<?php
Modal::begin([
    'id' => 'created-modal',
    'closeButton' => false,
    'size' => 'modal-lg',
]);
Modal::end();

$url_update_qty = Url::toRoute('edit-sku');
$url_sku_sales = Url::toRoute(['product/viewskusales']);
$imgurl = Url::toRoute(['purchase-suggest/img']);
$settlementurl = Url::toRoute(['supplier/get-supplier-issettlement']);
$count_arr = count($models);

$js = <<<JS
$(function() {
    
    // 表单提交验证
    $('#btn-submit').click(function() {
        $('#compact-confirm').submit();
    });
    
    // sku销量查看
    $('.sales').click(function () {
        $.get('$url_sku_sales', {sku: $(this).attr('data-sku')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    // 图片大图查看
    $('.img').click(function () {
        $.get('$imgurl', {img: $(this).attr('data-imgs'), sku: $(this).attr('data-skus')},
            function(data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
});

JS;
$this->registerJs($js);
?>

