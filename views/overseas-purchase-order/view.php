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
use app\models\ProductTaxRate;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseOrderBreakage;
use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>
<style type="text/css">
    .img-rounded{width: 60px; height: 60px; !important;}
    .floors{max-height: 750px; overflow-y: scroll}
    .modal-lg{width: 90%; !important;}
</style>
<h4 class="modal-title">采购单产品信息</h4>
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
            <th>金额</th>
            <th>出口退税税率</th>
            <th>采购开票点</th>
            <th>是否退税</th>
            <th>状态</th>
<!--            <th>收货数量</th>-->
            <th>报损数量</th>
            <th>仓库取消数量</th>
            <th>入库数量</th>
            <th>入库人</th>
            <th>入库时间</th>
            <th>采购到货日期</th>
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
        //含税的单价
        $rate_price = 0;
        if($model->purchaseOrderItemsCtq){
            foreach($model->purchaseOrderItemsCtq as $v){
                $expected +=$v->qty;
                $confirm +=$v->ctq;
                $receipt +=$v->rqy;
                $shelves +=$v->cty;
                $purchase_price +=$v->ctq * $v->price;
                $results = WarehouseResults::getResults($v->pur_number,$v->sku,'instock_user,instock_date,arrival_quantity');
                $arrival_quantity =    !empty($results->arrival_quantity)?$results->arrival_quantity:'0';


                $total_price += $arrival_quantity * $v->price;
                $img=Vhelper::downloadImg($v['sku'],$v['product_img'],2);
                $img =Html::img($img,['width'=>100]);

                $tax = PurchaseOrderTaxes::getABDTaxes($v['sku'],$v['pur_number']);
                $tax_price = 0;
                if($model->is_drawback == 2 && $tax>0){//税金税金税金
                    $tax = bcadd(bcdiv($tax,100,2),1,2);
                    $tax_price  = round($tax*$v->price*$v->ctq,2);
                    $rate_price += $tax_price;
                }else{
                    $rate_price += round($v->price*$v->ctq,2);
                }
                ?>
                <tr>
                    <td><?=$v->pur_number ?></td>
                    <td><?=Html::a($img,['#'], ['class' => "img", 'style'=>'margin-right:5px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal', 'data-skus' => $v['sku'],'data-imgs' => $v['product_img']])?></td>
                    <td>
                        <?=Html::a($v->sku,['#'], ['class' => "sales", 'data-sku' =>$v->sku, 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal',]).\app\services\SupplierGoodsServices::getSkuStatus($v->sku) ?>
                    </td>

                    <td title="<?=$v->name?>">
                        <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail'].$v->sku?>" target="_blank">
                            <?=$v->name?>
                        </a>
                    </td>

                    <td>
                        <?php
                        $plink=\app\models\Product::find()->select('pur_supplier_quotes.supplier_product_address')->joinWith(['supplierQuote'])->scalar();
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
                    <td>
                        <?php if($tax_price > 0){?>
                            <?=round($tax_price,2)?>
                        <?php }else{?>
                            <?=round($v->price*$v->ctq,2)?>
                        <?php }?>
                    </td>
                    <td><?=ProductTaxRate::getRebateTaxRate($v['sku']); ?></td>
                    <td><?=PurchaseOrderTaxes::getABDTaxes($v['sku'],$v['pur_number']) . '%'; ?></td>
                    <td><?=!empty($model->is_drawback) ? ($model->is_drawback==2?'是' : '否') : '否';?></td>
                    <td><?=PurchaseOrderServices::getPurchaseStatus($model['purchas_status'])?></td>
                    <td><?=PurchaseOrderBreakage::getNumber($v['sku'],$v['pur_number'])?></td>
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
                    <td><?=$v->wcq;?></td><!--仓库取消数量-->
                    <td><?=!empty($results->arrival_quantity) ? (($results->arrival_quantity == $v->ctq) ? $results->arrival_quantity : "<span style='color: red;'>$results->arrival_quantity</span>"): 0; ?></td><!--入库数量-->
                    <td><?=!empty($results->instock_user)?$results->instock_user:''?></td>
                    <td><?=!empty($results->instock_date)?$results->instock_date:''?></td>
                    <td>
                        <?php 
                            $time = $model_estimated_time->getEstimatedTime($v->sku,$v->pur_number);
                            $operation_count = $model_estimated_time->getOperationCount($v->sku,$v->pur_number);

                            if ($operation_count >= 2):
                                echo $time;
                            else:
                        ?>
                            <input type="date" name="arrival-time" value="<?=$time?>" class='pur_arrival_time' sku="<?=$v->sku?>" pur_number="<?=$v->pur_number?>" old_time="<?=$time?>" readonly="readonly" min="2015-09-16"><!-- 采购到货日期 -->
                        <?php endif; ?>
                    </td>


                </tr>
            <?php }?>
            <tr class="table-module-b1">
                <td class="ec-center" colspan="7" style="text-align: left;"><b>汇总：</b></td>
                <td ><b><?=$confirm?></b></td>
                <td >
                    <?php if($rate_price > 0){?>
                        <b><?=round($rate_price,2)?></b>
                    <?php }else{?>
                        <b><?=round($purchase_price,2)?></b>
                    <?php }?>
                </td>
                <td >&nbsp;</td>
                <td><b style="color:red;"><?php /*round($total_price,2)*/ ?></td>
                <td >&nbsp;</td>
                <td >&nbsp;</td>
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
$arrival_time_url=Url::toRoute(['update-arrival-time']);

$js = <<<JS

$(function () {
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

    //保存采购到货时间和状态
    $('.pur_arrival_time').change(function () {
        // $.post("{$arrival_time_url}", {ids:ids},function (data) {
          //       $('.modal-body').html(data);
          //   });
    });

    //双击编辑到货时间
    $("input[name='arrival-time']").dblclick(function(){
        $(this).removeAttr("readonly");
    });

    //失焦添加readonly
    $("input[name='arrival-time']").blur(function(){
        $(this).attr("readonly","true");
        var arrival_time = $(this).val();
        var sku   = $(this).attr('sku');
        var pur_number   = $(this).attr('pur_number');
        var old_time = $(this).attr('old_time');

        layer.confirm('你只有两次修改机会，你确定修改吗？', {icon: 3, title:'提示'}, 
        function(index){
            $.ajax({
                url:'{$arrival_time_url}',
                data:{arrival_time:arrival_time,sku:sku,pur_number:pur_number},
                type: 'get',
                dataType:'json',
                success:function (data) {
                    if (data == 1) {
                        console.log('到货日期添加--成功');
                    } 

                    if (data==-1) {
                        $(".pur_arrival_time").val(old_time);
                        alert('时间格式错误');
                    } else {
                        console.log('到货日期添加--失败');
                    }
                }
            });
           layer.close(index);
        }, 
        function(index) {
            $(".pur_arrival_time").val(old_time);
            layer.close(index);
        });
        $(this).attr("readonly","readonly")
    });
});
    
JS;
$this->registerJs($js);
?>
