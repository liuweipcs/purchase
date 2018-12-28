<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\PurchaseOrderServices;
use app\models\WarehouseResults;
use app\config\Vhelper;
use yii\bootstrap\Modal;
use app\models\PurchaseOrderItems;
use app\models\PurchaseOrderBreakage;

/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
$ordersitmes = $model;
$skus = []; //已请款的sku

if (!empty($requisition_number)) {
    $skus = \app\models\OrderPayDemandMap::getCurrentPaySkus(['requisition_number'=>$requisition_number]); //获取当前请款的sku
    $orderPayDemandMap = \app\models\OrderPayDemandMap::find()->where(['requisition_number'=>$requisition_number])->all();
    //echo "<pre>";var_dump($orderPayDemandMap);exit;
}

?>
<style type="text/css">
    .img-rounded{width: 60px; height: 60px; !important;}
    .floors{max-height: 750px; overflow-y: scroll}
    .modal-lg{width: 90%; !important;}
</style>
<h4 class="modal-title">采购单产品信息</h4>

<div class="row">
    <div class="col-md-4">
        <label>采购单号</label>
        <input type="text" disabled value="<?=$ordersitmes->pur_number?>">
    </div>

    <div class="col-md-4">
        <label>采&nbsp;&nbsp;购&nbsp;&nbsp;&nbsp;员</label>
        <input type="text" disabled value="<?=$ordersitmes->buyer?>">
    </div>

    <div class="col-md-4">
        <label>供应商</label>
        <?php

        $freight = 0;
        $discount = 0;
        $order_number = '';
        $account = '';
        if(!empty($orderPayDemandMap)){
            foreach ($orderPayDemandMap as $ordermap){
                $freight += $ordermap['freight'];
                $discount += $ordermap['discount'];
            }
        } else {
            if(!empty($ordersitmes->purchaseOrderPayType)) {

                $type = $ordersitmes->purchaseOrderPayType;

                $freight = $type->freight ? $type->freight : 0;
                $discount = $type->discount ? $type->discount : 0;
                $order_number = $type->platform_order_number ? $type->platform_order_number : '';
                $account = $type->purchase_acccount ? $type->purchase_acccount : '';

            }
        }
        $m_style1='';
        $m_style2='';
        if(!empty($ordersitmes->e_supplier_name) && !empty($grade) && $grade->grade<3){
            $m_style1="style='color:red'";
        }

        /* if(!empty($ordersitmes->e_account_type) && !empty($grade) && $grade->grade<3){
             $m_style2="style='color:red'";
         }*/
        ?>
        <input type="text" <?=$m_style1?> disabled value="<?=$ordersitmes->supplier_name?>">
    </div>

    <div class="col-md-4">
        <?php
        $qsum=PurchaseOrderItems::find()
            ->select(['count(sku) as sku_count,sum(qty) as qty, sum(ctq) as ctq, sku'])
            ->where(['pur_number'=>$ordersitmes->pur_number])->asArray()->all();

        $last_order = \app\models\PurchaseOrderItems::find()
            ->select('t.price')
            ->from(\app\models\PurchaseOrderItems::tableName().' as t')
            ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
            ->where(['t.sku'=>$qsum[0]['sku'],'t1.purchas_status'=>[3,5,6,7,8,9]])
            ->orderBy('t1.id DESC')
            ->one();
        $last_price = round((!empty($last_order->price)?$last_order->price:'首次采购'),2);
        ?>

        <label>SKU数量</label>
        <input type="text" disabled value="<?=!empty($qsum[0]['sku_count']) ? $qsum[0]['sku_count'] : '';?>">
    </div>

    <div class="col-md-4">
        <label>采购数量</label>
        <input type="text" disabled value="<?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : ''?>">
    </div>
    
    <div class="col-md-4">
        <label>上次采购单价</label>
        <input type="text" disabled value="<?=$last_price?>">
    </div>

    <div class="col-md-4">
        <label>总金额</label>
        <input type="text" disabled value="<?=round(PurchaseOrderItems::getCountPrice($ordersitmes->pur_number),2)?>">
    </div>

    <div class="col-md-4">
        <label>运&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;费</label>
        <input type="text" disabled value="<?= $freight ?>">
    </div>

    <div class="col-md-4">
        <label>优&nbsp;&nbsp;惠&nbsp;&nbsp;&nbsp;额</label>
        <input type="text" disabled value="<?= $discount ?>">
    </div>

    <div class="col-md-4">
        <label>结款方式</label>
        <input type="text" <?=$m_style2?> disabled value="<?=$ordersitmes->account_type ? \app\services\SupplierServices::getSettlementMethod($ordersitmes->account_type) : ''?>">
    </div>

    <div class="col-md-4">
        <label>支付方式</label>
        <input type="text" <?=$m_style2?> disabled value="<?=$model->pay_type ? \app\services\SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''?>">
    </div>

    <div class="col-md-4">
        <label>供应商运输</label>
        <input type="text" disabled value="<?= $model->shipping_method ? \app\models\PurchaseOrder::getShippingMethod($model->shipping_method) : '' ?>">
    </div>

    <div class="col-md-4">
        <label>账号</label>
        <input type="text" disabled value="<?=\app\models\PurchaseOrderPayType::getPurchaseAccount($model->pur_number)?>">
    </div>

    <div class="col-md-4">
        <label>拍&nbsp;&nbsp;单&nbsp;&nbsp;&nbsp;号</label>
        <input type="text" disabled value="<?=\app\models\PurchaseOrderPayType::getOrderNumber($model->pur_number)?>">
    </div>

    <div class="col-md-4">
        <label>是否加急</label>
        <input type="text" disabled value="<?=!empty($model->is_expedited)?$model->is_expedited==1?'否':'是':'' ?>">
    </div>

    <!--<div class="col-md-4">
        <label>订单号</label>
        <input type="text" disabled value="<?/*=$order_number;*/?>">
    </div>

    <div class="col-md-4">
        <label>是否退税</label>
        <input type="text" disabled value="<?/*=!empty($model->is_drawback) ? ($model->is_drawback==2?'是' : '否') : '否';*/?>">
    </div>-->

</div>


<div class="row floors">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>PO号</th>
            <th>图片</th>
            <th>SKU</th>
            <th>产品名称</th>
            <th>产品链接</th>
            <th>预计到货时间</th>
            <th>单价( RMB )</th>
            <th>采购数量</th>
            <th>上次采购单价</th>
            <th>金额</th>
            <th>状态</th>
<!--            <th>收货数量</th>-->
            <th>采购开票点</th>
            <th>取消数量</th>
            <th>报损数量</th>
            <th>入库数量</th>
            <th>不良品数量</th>
            <th>入库人</th>
            <th>入库时间</th>
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
        // 入库数量总金额
        $total_price = 0;

        if($model->purchaseOrderItems){
            foreach($model->purchaseOrderItems as $v){
                $type = Vhelper::getNumber($v->pur_number);
                if (!empty($skus) && !in_array($v->sku,$skus) && $type===2) continue;
                $expected +=$v->qty;
                $confirm +=$v->ctq;
                $receipt +=$v->rqy;
                $shelves +=$v->cty;
                //$purchase_price +=$v->ctq * $v->price;
                $results = WarehouseResults::getResults($v->pur_number,$v->sku,'instock_user,instock_date,arrival_quantity,instock_qty_count,nogoods');
                $instock_qty_count_td = !empty($results->instock_qty_count) ? (($results->instock_qty_count == $v->ctq) ? $results->instock_qty_count : "<span style='color: red;'>$results->instock_qty_count</span>"): 0;
                $arrival_quantity_td = !empty($results->arrival_quantity) ? (($results->arrival_quantity == $v->ctq) ? $results->arrival_quantity : "<span style='color: red;'>$results->arrival_quantity</span>"): 0;


                if ($type ==1 ) { //国内
                    $ruku = $instock_qty_count_td;
                } elseif ($type===2) { //海外
                    $ruku = $arrival_quantity_td;
                } elseif ($type==3) { //FBA
                    $ruku = $v->cty;
                } else {
                    $ruku = $instock_qty_count_td;
                }
                $arrival_quantity = !empty($results->arrival_quantity)?$results->arrival_quantity:'0';


               $no =  !empty($results->nogoods)?$results->nogoods:0;

                $total_price += ($arrival_quantity-$no) * $v->price;
                $img=Vhelper::downloadImg($v['sku'],$v['product_img'],2);
                $img =Html::img($img,['width'=>100]);

                ?>
                <tr>
                    <td><?=$v->pur_number ?></td>
                    <td><?=Html::a($img,['#'], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal', 'data-skus' => $v['sku'],'data-imgs' => $v['product_img']])?></td>
                    <td>
                        <?=Html::a($v->sku,['#'], ['class' => "sales", 'data-sku' =>$v->sku, 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]).\app\services\SupplierGoodsServices::getSkuStatus($v->sku).\app\models\ProductRepackageSearch::getPlusWeightInfo($v->sku,true,1); ?>
                    </td>

                    <td title="<?=$v->name?>">
                        <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku?>" target="_blank">
                            <?=$v->name?>
                        </a>
                    </td>

                    <td>
                        <?php
                        $product=\app\models\Product::find()->where(['sku'=>$v->sku])->one();
                        $plink=!empty($product->supplierQuote) ? $product->supplierQuote->supplier_product_address : '';
                        //$plink=\app\models\SupplierQuotes::getUrl($v['sku']);
                        if($plink){
                            $prolink=$plink;
                        }else{
                            $prolink=$v->product_link;
                        }
                        ?>
                        <a href="<?=$prolink?>" target="_blank"><?=Vhelper::toSubStr($prolink,1,5)?></a>
                    </td>
                    <td><?=$model->date_eta?></td>
                    <td><?=round($v->price,2)?></td>
                    <td><?=$v->ctq?></td><!--采购数量-->
                    <td><?php 
                    $last_order = \app\models\PurchaseOrderItems::find()
                                                        ->select('t.price')
                                                        ->from(\app\models\PurchaseOrderItems::tableName().' as t')
                                                        ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
                                                        ->where(['t.sku'=>$v->sku,'t1.purchas_status'=>[3,5,6,7,8,9]])
                                                        ->orderBy('t1.id DESC')
                                                        ->one();

                    echo round((!empty($last_order->price)?$last_order->price:'首次采购'),2)
                    ?></td><!--上次采购单价-->
                    <td>
                        <?php
                            if($model->is_drawback == 2 && $type != 3){//税金税金税金
                                $rate = \app\models\PurchaseOrderTaxes::getABDTaxes($v->sku,$v->pur_number);
                                $tax = bcadd(bcdiv($rate,100,2),1,2);
                                $pay  = round($tax*$v->price*$v->ctq,2);//数量*单价*(1+税点)
                                $purchase_price += $pay;
                            }else{
                                $pay = round($v->price*$v->ctq,2);
                                $purchase_price +=$v->ctq * $v->price;
                            }
                        ?>
                        <?= $pay ?>
                    </td>
                    <td><?=PurchaseOrderServices::getPurchaseStatus($model['purchas_status'])?></td>
                    <td><?= \app\models\PurchaseOrderTaxes::getABDTaxes($v->sku,$v->pur_number).'%'; ?></td>
                    <td><?=isset($refund_ctq[$v->sku])?$refund_ctq[$v->sku]:0?></td>
                    <td><?=PurchaseOrderBreakage::getNumber($v->sku,$v->pur_number)?></td>
                <?php
                $delivery_qty = 0;
                if(!empty($arriva)){
                    foreach($arriva as $k=>$av){
                        if ($v->sku == $av->sku) {
                            $delivery_qty += $av->delivery_qty;
                        }
                    }
                }?>
                    <!--<td><?/*=$delivery_qty */?></td>-->
                    <td><?php echo $ruku; ?></td><!--入库数量-->
                    <td><?=!empty($results->nogoods)?$results->nogoods:0; ?></td>
                    <td><?=!empty($results->instock_user)?$results->instock_user:''?></td>
                    <td><?=!empty($results->instock_date)?$results->instock_date:''?></td>
                </tr>
            <?php }?>


            <tr class="table-module-b1">
                <td class="ec-center" colspan="6" style="text-align: left;"><b>汇总：</b></td>
                <td ></td>
                <td ><b><?=$confirm?></b></td>
                <td ></td>
                <td ><b><?=round($purchase_price,2)?></b></td>
                <td ></td>
                <td ></td>
                <td ></td>
                <td>入库金额：<b style="color:red;"><?=round($total_price,2)?></td>
                <td ></td>
                <td ></td>
                <td ></td>
            </tr>




        <?php }?>
        </tbody>
    </table>

    <h4>采购到货记录</h4>
    <div class="stockin-update">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>序号</th>
                <th>SKU</th>
                <th>产品名称</th>
                <th>收货数量</th>
                <th>次品数量</th>
                <th>收货人</th>
                <th>收货时间</th>
                <th>备注</th>
            </tr>
            </thead>
            <tbody class="pay">
            <?php if(!empty($arriva)){ ?>
                <?php foreach($arriva as $k=>$v){ ?>
                    <tr>
                        <th><?=$k+1?></th>
                        <th><?=$v->sku?></th>
                        <th><?=$v->name?></th>
                        <th><?=$v->delivery_qty?></th>
                        <th><?=$v->bad_products_qty?></th>
                        <th><?=$v->delivery_user?></th>
                        <th><?=$v->delivery_time?></th>
                        <th><?=$v->note?></th>
                    </tr>
                <?php }} ?>
            </tbody>

        </table>
    </div>

</div>

<?php
Modal::begin([
    'id' => 'created-modal',
    //'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',

    ],
]);
Modal::end();

$url=Url::toRoute(['product/viewskusales']);
$imgurl=Url::toRoute(['purchase-suggest/img']);

$js = <<<JS

    $(document).on('click', '.sales', function () {
        $.get('{$url}', {sku:$(this).attr('data-sku')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '.img', function () {
        $.get('{$imgurl}', {img:$(this).attr('data-imgs'),sku:$(this).attr('data-skus')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });

JS;
$this->registerJs($js);
?>
