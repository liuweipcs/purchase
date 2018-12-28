<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\config\Vhelper;
use app\models\WarehouseResults;
use app\models\PurchaseOrderItems;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use app\models\Product;
use app\models\SupplierQuotes;
use \toriphes\lazyload\LazyLoad;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>
<style>
    body {
        font: 12px/150% Arial, Helvetica, sans-serif, '宋体';
    }
    #print_content {
        background: none repeat scroll 0 0 white;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        text-align: center;
        width: 21cm;
        padding: 0px;
        font-size: 13px;
    }

    #print_content table {
        border-collapse: collapse;
        border: none;
        width: 100%;
    }

    .list td, .list th {
        border: solid #000000 1px;
        height: 27px;
    }

    .print-header{
        border:0;
    }

</style>
<!--  -->

<div id="print_content">
    <div style="page-break-after:always;clear:both;padding-top: 20px;">
        <div style="text-align:left;margin-top: 30px;">
            <table border="0" class="print-header">
                <tbody>
                <tr>
                  <td style="text-align:left;width:30%"></td>
                    <td style="text-align:center;">
                        
                    </td>
                    <td style="text-align:right;vertical-align:bottom; ">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div style="clear:both;">
            <table class="list">
                <tr>
                    <th width="13%">PO号</th>
                    <th width="10%">SKU数量</th>
                    <th width="10%">采购数量</th>
                    <th width="20%">采购员</th>
                    <th width="10%">结算方式</th>
                    <th width="5%">支付方式</th>
                    <th>供应商运输</th>
                    <th>运费</th>
                    <th>优惠额</th>
                </tr>
<?php
foreach($models_array as $ak=>$vb){
    $vb->account_type = $vb->account_type ? $vb->account_type : 2;// 预设默认值 结算
    $vb->pay_type = $vb->pay_type ? $vb->pay_type : 2;// 支付
    $vb->shipping_method = $vb->shipping_method ? $vb->shipping_method : 2;// 运输
    $purchase_acccount = '';
    $platform_order_number = '';

    if(!empty($vb->purchaseOrderPayType)) {
        $purchase_acccount = $vb->purchaseOrderPayType->purchase_acccount;
        $platform_order_number = $vb->purchaseOrderPayType->platform_order_number;
    } else {
        $platform_order_number = !empty($vb->orderOrders->order_number)?$vb->orderOrders->order_number:'';
    }
}?>
                <tr>
                    <td><?=$vb->pur_number?></td>
                    <?php
                    $qsum = PurchaseOrderItems::find()
                        ->select(['sum(qty) as qty, sum(ctq) as ctq, count(id) as id, sum(price) as price'])
                        ->where(['pur_number' => $vb->pur_number])->asArray()->all();
                    ?>
                    <td><?=!empty($qsum[0]['id']) ? $qsum[0]['id'] : '' ?></td>
                    <td><?=!empty($qsum[0]['ctq']) ? $qsum[0]['ctq'] : $qsum[0]['qty'] ?></td>
                    <td><?=$vb->buyer?></td>
                    <td><?=SupplierServices::getSettlementMethod($vb->account_type)?></td>
                    <td><?=SupplierServices::getDefaultPaymentMethod($vb->pay_type)?></td>
                    <td><?=PurchaseOrderServices::getShippingMethod($vb->shipping_method)?></td>
                    <td><?=!empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->freight : 0;?></td>
                    <td><?=!empty($vb->purchaseOrderPayType) ? $vb->purchaseOrderPayType->discount : 0;?></td>
                    
                </tr>











                <tr>
                    <th width="13%">是否中转</th>
                    <th width="13%">中转仓</th>
                    <th width="13%">是否退税</th>
                    <th width="13%">供应商</th>
                    <th width="10%">预计到货时间</th>
                    <th width="10%">账号</th>
                    <th width="20%">拍单号</th>
                    <th width="10%">是否加急</th>
                    <th width="10%"></th>
                </tr>

<?php 

    if($vb->purchase_type!=1){
    $vb->is_transit=1;
    if (empty($vb->is_drawback)) {
        $vb->is_drawback=1;
    }

    $is_transit = ($vb->is_transit==1) ? '是' : '否'; //是否中转
    $transit_warehouse = ($vb->transit_warehouse=='AFN') ? '东莞中转仓库' : '上海中转仓库'; //中转仓
    $is_drawback = ($vb->is_drawback==2) ? '是' : '否'; //是否退税
?>


<?php }else{
    $vb->is_transit=2;
    $is_transit = ($vb->is_transit==1) ? '是' : '否'; //是否中转
    $transit_warehouse = ''; //中转仓
    $is_drawback = ''; //是否退税
}?>

                <tr>
                    <td><?=$is_transit?></td>
                    <td><?=$transit_warehouse?></td>
                    <td><?=$is_drawback?></td>
                    <td><?=$vb->supplier_name ?></td>
                    <td><?=!empty($vb->date_eta)?$vb->date_eta:date('Y-m-d',strtotime('+12 day'))?></td>

                    <td><?= $platform_order_number ?></td><!-- 账号 -->
                    <td><?=$platform_order_number?></td>
                    <td><?=($vb->is_expedited==1)?'不加急':'加急';?></td><!-- 是否加急 -->
                    <td></td><
                </tr>
                <tr>
                    <th>图片</th>
                    <th>SKU</th>
                    <th>产品名</th>
                    <th>采购数量</th>
                    <th>单价</th>
                    <th>上次采购单价</th>
                    <th>金额</th>
                    <th>产品链接</th>
                    <th>未处理原因</th>
                </tr>

<?php
    $total =0;
    foreach($vb->purchaseOrderItems as $k=> $v){
        $price = Product::getProductPrice($v->sku);
        $price = !empty($price) ? $price : $v->price;

        $totalprice = $v->ctq*$price;
        $totalprices = $v->qty*$price;
        $total += $totalprice?$totalprice:$totalprices;
        $img_url = Vhelper::getSkuImage($v->sku);
        $img = LazyLoad::widget(['src'=>$img_url]);
        $img =Html::img($img,['width'=>100]);
?>

                <tr>
                    <td><?= Html::img($img_url,['width'=>'110px']) ?></td>
                    <td><?=$v->sku?></td>
                    <td><?=$v->name?></td>
                    <td><?=$v->ctq ? $v->ctq : $v->qty?></td>
                    <td><?=round($price,4)?></td>
                    <td><?php
                        $last_order = \app\models\PurchaseOrderItems::find()
                            ->select('t.price')
                            ->from(\app\models\PurchaseOrderItems::tableName().' as t')
                            ->leftJoin('pur_purchase_order as t1','t1.pur_number = t.pur_number')
                            ->where(['t.sku'=>$v->sku,'t1.purchas_status'=>[3,5,6,7,8,9]])
                            ->orderBy('t1.id DESC')
                            ->one();
                        echo round((!empty($last_order->price)?$last_order->price:'首次采购'),4);
                     ?></td>
                    <td><?=$totalprice?$totalprice:$totalprices?></td>
                    <td><?php
                        $plink=$v->product_link ? $v->product_link : SupplierQuotes::getUrl($v->sku);
                        echo $plink;
                    ?></td>
                    <td><?=!empty($v->purchaseSuggestNote)?$v->purchaseSuggestNote->suggest_note:''?></td>
                </tr>
<?php }?>
                <tr>
                    <td>总额</td>
                    <td><b><?=round($total,2).'&nbsp;&nbsp;'.$vb->currency_code?></b></td>
                </tr>
                <tr>
                    <td>确认备注</td>
                    <td colspan="8"><b><?=!empty($vb->orderNote->note)?$vb->orderNote->note:''?></b></td>
                </tr>
            </table>
        </div>
    </div>
</div>


