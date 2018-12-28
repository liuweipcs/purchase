<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\models\WarehouseResults;
use app\models\PurchaseDemand;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderAccount;
use app\config\Vhelper;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;
use app\models\PurchaseCancelQuantity;

?>
<?php
$freight1 = !empty($model->orderShip->freight)?$model->orderShip->freight:'0';
$freight2 = 0;
$settlement_ratio = ''; //结算比例
if(!empty($model->purchaseOrderPayType)) {
    $freight2 = $model->purchaseOrderPayType->freight ? $model->purchaseOrderPayType->freight : 0;
    $settlement_ratio = $model->purchaseOrderPayType->settlement_ratio;
}
if($freight2) {
    $freight = $freight2;
} else {
    $freight = $freight1;
}

$discount1 = !empty($model->purchaseDiscount->discount_price)?$model->purchaseDiscount->discount_price:'0';
$discount2 = 0;
if(!empty($model->purchaseOrderPayType)) {
    $discount2 = $model->purchaseOrderPayType->discount ? $model->purchaseOrderPayType->discount : 0;
}

if($discount2) {
    $discount =$discount2;
} else {
    $discount = $discount1;
}

$order_number = !empty($model->purchaseOrderPayType)?$model->purchaseOrderPayType->platform_order_number:'';

if(!$order_number) {
    $order_number = !empty($model->orderOrders) ? $model->orderOrders->order_number : '';
}

$account = !empty($model->purchaseOrderPayType) ? $model->purchaseOrderPayType->purchase_acccount : '';

if(!$order_number) {
    $account = !empty($model->orderOrders) && isset($model->orderOrders->account) ? $model->orderOrders->account : '';
}

?>
<div class="row">
    <div class="col-md-2">
        <label class="control-label" for="purchaseorder-carrier">采购单</label>
        <div class="form-control" ><?=$model->pur_number?></div>
    </div>
    <div class="col-md-3" style="width: 250px">
        <label class="control-label" for="purchaseorder-carrier">供应商</label>
        <div class="form-control" style="width: 240px"><?=$model->supplier_name?></div>
    </div>

    <div class="col-md-2">
        <label class="control-label" for="purchaseorder-carrier">结算方式</label>
        <div class="form-control"><?= !empty($model->account_type) ? \app\services\SupplierServices::getSettlementMethod($model->account_type) : '' ?></div>
    </div>
    <div class="col-md-2">
        <label class="control-label" for="purchaseorder-carrier">支付方式</label>
        <div class="form-control" ><?= !empty($model->pay_type) ?\app\services\SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''?></div>
    </div>
    <div class="form-group field-purchaseorder-pur_number required col-md-2">
        <label class="control-label" for="purchaseorder-carrier">结算比例</label>
        <div class="form-control" ><?= $settlement_ratio ?></div>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
        <label class="control-label" for="purchaseorder-carrier">运输方式</label>
        <div class="form-control" ><?= !empty($model->shipping_method) ? \app\services\PurchaseOrderServices::getShippingMethod($model->shipping_method) : ''?></div>
    </div>
    <div class="col-md-1">
        <label class="control-label" for="purchaseorder-carrier">运费</label>
        <div class="form-control" ><?= $freight ?></div>
    </div>
    <div class="col-md-2">
        <label class="control-label" for="purchaseorder-carrier">是否加急</label>
        <div class="form-control" ><?= !empty($model->is_expedited)&&$model->is_expedited == 2 ?'加急采购单' : '不加急' ?></div>
    </div>
    <div class="field-purchaseorder-pur_number required col-md-2">
        <label class="control-label" for="purchaseorder-carrier">预计到货时间</label>
        <div class="form-control" ><?=date('Y-m-d',!empty($model->date_eta) ? strtotime($model->date_eta) :time());?></div>
    </div>
    <div class="col-md-2">
        <label class="control-label" for="purchaseorder-carrier">1688拍单号</label>
        <div class="form-control" ><?= $order_number ?></div>
    </div>
    <div class="col-md-2">
        <label class="control-label" for="purchaseorder-carrier">是否含税</label>
        <div class="form-control" ><?= !empty($model->is_drawback) && $model->is_drawback ==2 ? '含税' : '不含税'?></div>
    </div>
</div>
<div class="row">
    <div class="form-group field-purchaseorder-pur_number required col-md-2">
        <label class="control-label" for="purchaseorder-carrier">账号</label>
        <div class="form-control" ><?= $account ? $account : '未设置' ?></div>
    </div>
    <div class="col-md-1">
        <label class="control-label" for="purchaseorder-carrier">优惠金额</label>
        <div class="form-control" ><?= $discount ?></div>
    </div>
</div>

<div class="col-md-12">
    <label class="control-label" for="purchasenote-note">确认备注</label>
    <textarea id="purchasenote-note" class="form-control" name="PurchaseNote[note][]" rows="3" cols="10" required="" placeholder="比如说是阿里支付单号,这个给财务能看到"><?=!empty($model->orderNote->note)?$model->orderNote->note:''?></textarea>
</div>
<div class="row">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>图片</th>
            <th>产品编号</th>
            <th>产品名称</th>
            <th>销售状态</th>
            <th>预期数量</th>
            <th>确认数量</th>
            <th>取消数量</th>
            <th>收货数量</th>
            <th>良品上架数量</th>
            <th>采购单价</th>
            <th>状态</th>
            <th>收货人</th>
            <th>收货时间</th>
            <th>sku时间</th>
        </tr>
        </thead>
        <tbody>
        <?php
        //预期
        $expected =0;
        //确认
        $confirm=0;
        //收货
        $receipt =0;
        //上架
        $shelves=0;
        //采购单价
        $purchase_price=0;
        if($model->purchaseOrderItems){
            foreach($model->purchaseOrderItems as $v){
                $expected +=$v->qty;
                $confirm +=$v->ctq;
                $receipt +=$v->rqy;
                $shelves +=$v->cty;
                $purchase_price +=$v->ctq * $v->price;

                //优惠后金额
                $discount_total_price = !empty($item->purchaseDiscount->total_price) ? $item->purchaseDiscount->total_price : '';
                if (empty($discount_total_price)) {
                    $discount_total_price = $purchase_price + $freight - $discount;
                }

                $results = WarehouseResults::getResults($v->pur_number,$v->sku,'instock_user,instock_date');
                $img=Html::img(Vhelper::getSkuImage($v['sku']),['width'=>'110px']);
                ?>
                <tr>
                    <td><?=Html::a($img,['purchase-suggest/img', 'sku' => $v['sku'],'img' => $v['product_img']], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?></td>
                    <td><?php
                        $html = Html::a($v->sku, Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku,['target'=>'blank']);
                        $html .='<br>'.Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$v->sku],
                                [
                                    'class' => 'btn btn-xs stock-sales-purchase',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#created-modal',
                                ]);
                        $html .=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['purchase-suggest/histor-purchase-info','sku'=>$v->sku],[
                            'data-toggle' => 'modal',
                            'data-target' => '#created-modal',
                            'class'=>'btn btn-xs stock-sales-purchase',
                        ]);
                        echo $html;
                        ?></td>
                    <td width="200" style="word-break:break-all;"><?=!empty($v->desc) ? $v->desc->title : ''?></td>
                    <td><?=app\services\SupplierGoodsServices::getProductStatus($v->sales_status)?></td>
                    <td><?=$v->qty?></td>
                    <td><?=$v->ctq?></td><!--确认数量-->
                    <td><?=\app\models\PurchaseOrderCancelSub::getCancelCtq($v->pur_number,$v->sku); ?></td><!--取消数量-->
                    <td><?php echo ($v->rqy == $v->ctq) ? $v->rqy : "<span style='color: red;'>$v->rqy</span>" ;?></td><!--收货数量-->
                    <td><?php echo ($v->cty == $v->ctq) ? $v->cty : "<span style='color: red;'>$v->cty</span>" ;?></td><!--良品上架数量-->
                    <td><?=$v->price.'&nbsp;'.$model['currency_code']?></td>
                    <td><?=PurchaseOrderServices::getPurchaseStatus($model['purchas_status'])?></td>
                    <td><?=!empty($results->instock_user)?$results->instock_user:''?></td>
                    <td><?=!empty($results->instock_date)?$results->instock_date:''?></td>
                    <td><?=PurchaseEstimatedTime::getEstimatedTime($v->sku,$v->pur_number); ?></td>
                </tr>
            <?php }?>
            <tr class="table-module-b1">
                <td class="ec-center" colspan="4" style="text-align: left;"><b>汇总：</b></td>
                <td><b><?=$expected?></b></td>
                <td ><b><?=$confirm?></b></td>
                <td></td>
                <td ><b><?=$receipt?></b></td>
                <td ><b><?=$shelves?></b></td>
                <td colspan="3"><b>总应付：<?=number_format($purchase_price,3).'&nbsp;&nbsp;'.$model['currency_code']?></b></td>
                <td><b></b></td>
            </tr>
            <tr class="table-module-b1">
                <td class="ec-center" colspan="8" style="text-align: left;"><b>优惠后总金额 = 实际总金额 + 运费 - 优惠</b></td>
                <td colspan="3">优惠后：<b><?=$discount_total_price; ?></b></td>
                <td><b></b></td>
            </tr>
        <?php }?>
        </tbody>

    </table>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>SKU</th>
            <th>收货时间</th>
            <th>收货数量</th>
            <th>收货人员</th>
            <th>品检类型</th>
            <th>品检时间</th>
            <th>品检人员</th>
            <th>质检备注</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($model->arrival as $v){ ?>
            <tr>
                <td><?= $v->sku?></td>
                <td><?= $v->delivery_time?></td>
                <td><?= $v->delivery_qty?></td>
                <td><?= $v->delivery_user?></td>
                <td><?= $v->check_type?>
                </td>
                <td><?= $v->check_time?></td>
                <td><?= $v->check_user?></td>
                <td><?= $v->note?></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>

<?php
Modal::begin([
    'id' => 'created-modal',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',
    ],
]);
Modal::end();
$js = <<<JS
    $(document).on('click', '.img', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
   
    $(document).on('click', '.stock-sales-purchase', function () {
        $('#created-modal').find('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
JS;
$this->registerJs($js);
?>
