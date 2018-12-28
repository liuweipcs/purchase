<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use kartik\date\DatePicker;
use app\models\PurchaseOrderAccount;
use app\models\PurchaseEstimatedTime;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseDemand;
use app\models\Stock;
use app\models\PurchaseHistory;
?>

<div class="stockin-view">
    <?php $form = ActiveForm::begin(); ?>
    <input type="hidden" id="purchaseorder-purchas_status" class="form-control" name="PurchaseOrders[purchas_status]">
    <?php foreach ($model as $ak => $item) { 
        $settlement_ratio = ''; //结算比例

        if(!empty($item->purchaseOrderPayType)) {
            $settlement_ratio = $item->purchaseOrderPayType->settlement_ratio;
        }
    ?>
    <div class="col-md-12" style="border: 1px solid red">
        <div class="row">
            <input type="hidden" id="purchaseorder-id" class="form-control" name="PurchaseOrder[id][]" value="<?= $item->id ?>">
            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-pur_number">采购单号</label>
                <input type="text" id="purchaseorder-pur_number" class="form-control" name="PurchaseOrder[pur_number]"
                       value="<?= $item->pur_number ?>" disabled="disabled" maxlength="20" aria-required="true">
                <div class="help-block"></div>
            </div>
            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-pur_number">采购员</label>
                <input type="text" class="form-control" value="<?= $item->buyer ?>" disabled="disabled" maxlength="20" aria-required="true">
                <div class="help-block"></div>
            </div>
            <div class="form-group field-purchaseorder-pur_number required col-md-8">
                <label class="control-label" for="purchaseorder-pur_number">备注</label>
                <div class="form-control" style="border: none;">
                    <textarea name="PurchaseOrder[audit_note][]" style="margin: 0px; width: 530px; height: 36px;"  placeholder="请写点什么吧" ><?= !empty($item->audit_note)&&$item->audit_return==1 ? $item->audit_note :'';?></textarea>
                </div>
                <div class="help-block"></div>
            </div>
        </div>
        <div class="row">
            <div class="form-group field-purchaseorder-pur_number required col-md-4">
                <label class="control-label" >供应商</label>
                <div class="form-control" ><?=$item->supplier_name?></div>
            </div>
            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-carrier">结算方式</label>
                <div class="form-control" ><?= !empty($item->account_type) ? \app\services\SupplierServices::getSettlementMethod($item->account_type) : '' ?></div>
            </div>
            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-carrier">支付方式</label>
                <div class="form-control" ><?= !empty($item->pay_type) ?\app\services\SupplierServices::getDefaultPaymentMethod($item->pay_type) : ''?></div>
            </div>
            
            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-carrier">结算比例</label>
                <div class="form-control" ><?= $settlement_ratio ?></div>
            </div>

            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-carrier">运输方式</label>
                <div class="form-control" ><?= !empty($item->shipping_method) ? \app\services\PurchaseOrderServices::getShippingMethod($item->shipping_method) : ''?></div>
            </div>
        </div>
        <div class="row">
            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-carrier">是否加急</label>
                <div class="form-control" ><?= !empty($item->is_expedited)&&$item->is_expedited == 2 ?'加急采购单' : '不加急' ?></div>
            </div>

            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-carrier">预计到货时间</label>
                <div class="form-control" ><?=date('Y-m-d',strtotime($item->date_eta));?></div>
            </div>
            <div class="form-group field-purchaseorder-pur_number required col-md-2">
                <label class="control-label" for="purchaseorder-carrier">是否含税</label>
                <div class="form-control" ><?= !empty($item->is_drawback) && $item->is_drawback ==2 ? '含税' : '不含税'?></div>
            </div>

            <?php
                $freight1 = !empty($item->orderShip->freight)?$item->orderShip->freight:'0';
                $freight2 = 0;
                if(!empty($item->purchaseOrderPayType)) {
                    $freight2 = $item->purchaseOrderPayType->freight ? $item->purchaseOrderPayType->freight : 0;
                }
                if($freight2) {
                    $freight = $freight2;
                } else {
                    $freight = $freight1;
                }

                $discount1 = !empty($item->purchaseDiscount->discount_price)?$item->purchaseDiscount->discount_price:'0';
                $discount2 = 0;
                if(!empty($item->purchaseOrderPayType)) {
                   $discount2 = $item->purchaseOrderPayType->discount ? $item->purchaseOrderPayType->discount : 0;
                }

                if($discount2) {
                    $discount =$discount2;
                } else {
                    $discount = $discount1;
                }

               $order_number = !empty($item->purchaseOrderPayType)?$item->purchaseOrderPayType->platform_order_number:'';

                if(!$order_number) {
                    $order_number = !empty($item->orderOrders) ? $item->orderOrders->order_number : '';
                }

                $account = !empty($item->purchaseOrderPayType) ? $item->purchaseOrderPayType->purchase_acccount : '';

                if(!$order_number) {
                    $account = !empty($item->orderOrders) ? $item->orderOrders->account : '';
                }

            ?>
            <p>账号：<?= $account ? $account : '未设置' ?></p>
            <p>拍单号：<?= $order_number ?></p>
            <p>运费：<?= $freight ?></p>
            <p>优惠：<?= $discount ?></p>
        </div>
        <div class="row">
            <div class="form-group field-purchaseorder-pur_number required col-md-12">
                <label class="control-label" for="purchasenote-note">确认备注</label>
                <textarea  readonly="readonly" id="purchasenote-note" class="form-control" name="PurchaseNote[note][]" rows="3" cols="10" required="" placeholder="比如说是阿里支付单号,这个给财务能看到"><?=!empty($item->orderNote->note)?$item->orderNote->note:''?></textarea>
            </div>
        </div>
        <table class="table table-bordered">
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
            foreach ($item['purchaseOrderItems'] as $sku) {
                $expected +=$sku['qty'];
                $confirm +=$sku['ctq'];
                $receipt +=$sku['rqy'];
                $shelves +=$sku['cty'];
                $purchase_price +=$sku['ctq'] * $sku['price'];

                //优惠后金额
                $discount_total_price = !empty($item->purchaseDiscount->total_price) ? $item->purchaseDiscount->total_price : '';
                if (empty($discount_total_price)) {
                    $discount_total_price = $purchase_price + $freight - $discount;
                }
            ?>
                <tr >
                    <td>产品代码：<?= $sku['sku'] ?><a href="<?=$sku->getSkuPurchaseLink()?>" title='' target='_blank'><i class='fa fa-fw fa-internet-explorer'></i></a></div>
                    <?php $proDesc = \app\models\ProductDescription::find()->where(['sku'=>$sku['sku']])->one();?>
                    <td  width="200" style="word-break:break-all;">产品名称：<?= !empty($proDesc) ? $proDesc->title : '' ?></td>
                    <!--<td><?/*= \app\config\Vhelper::toSkuImg($sku['sku'],$sku['product_img'])*/?></td>-->
                    <td><?= Html::img(\app\config\Vhelper::downloadImg($sku['sku'],$sku['product_img'],2),['width'=>'110px'])?></td>
                    <td >产品数量：<?= $sku['ctq'] ?></td>
                    <?php
                         $stock = \app\models\Stock::getStock($sku['sku'],$item['warehouse_code']);
                        if(!empty($stock)){
                            $in_stock=$stock->stock;
                        }else{
                            $in_stock=0;
                        }
                    ?>
                    <td >销量库存：<?=Html::a('<span class="glyphicon glyphicon-stats" style="font-size:10px;color:cornflowerblue;" title="销量库存"></span>', ['product/get-stock-sales','sku'=>$sku['sku']],[
                            'data-toggle' => 'modal',
                            'data-target' => '#created-modal2',
                            //'code' => $vb->warehouse_code,
                            'class'=>'sales',
                            'sku'  => $sku['sku'],
                        ])?></td>
                    <td >产品单价：<?= $sku['price'] ?></td>
                    <td>历史采购信息：<?=PurchaseHistory::getLastPrice($sku['sku'])?><Br/><?=Html::a('<span class="glyphicon glyphicon-eye-open" style="font-size:10px;color:cornflowerblue;" title="历史采购记录"></span>', ['#'],[
                            'data-toggle' => 'modal',
                            'data-target' => '#created-modal2',
                            'class'=>'data-updatess',
                            'sku'  => $sku['sku'],
                        ])?></td>
                        <td>sku到货时间：<?=PurchaseEstimatedTime::getEstimatedTime($sku['sku'],$item->pur_number); ?></td>
                </tr>
            <?php } ?>
            <tr class="table-module-b1">
            <td colspan="2"><b>sku采购总数：<?=number_format($confirm,3)?></b></td>
            <td colspan="2"><b>总应付：<?=number_format($purchase_price,3).'&nbsp;&nbsp; RMB'?></b></td>
            </tr>
            <tr>
            <td class="ec-center" colspan="2" style="text-align: left;"><b>优惠后总金额 = 实际总金额 + 运费 - 优惠</b></td>
            <td colspan="2" >优惠后：<b><?=$discount_total_price; ?></b></td>
            </tr>
        </table>
        <div class="col-md-2">提交操作:</div>
        <div class="col-md-8">
            <label class="btn btn-info"><input name="PurchaseOrders[purchas_status][<?=$ak?>]" type="radio" value="3" checked />审批通过(Ok)</label>
            <label class="btn btn-warning"><input name="PurchaseOrders[purchas_status][<?=$ak?>]" type="radio" value="4"/>审批不通过(Rollback) </label>
        </div>
    </div>

    <?php } ?>
    <div class="form-group">
        <?= Html::submitButton('确定审核',['class' => 'btn btn-success']) ?>
        <?php if($name =='audit'){?>
        <?php Html::submitButton('提交复审(Re-examine)', ['class' => 'btn btn-info']) ?>
        <?php }?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php
Modal::begin([
    'id' => 'created-modal2',
    'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        'z-index' =>'-1',
    ],
]);
Modal::end();
$surl= Url::toRoute(['product/viewskusales']);
$historys = Url::toRoute(['purchase-suggest/histor-purchase-info']);
$js = <<<JS
$(function(){
    $('#created-modal2').on('shown.bs.modal', function (e){
            var scrollTop = $('#create-modal').scrollTop();
            $(this).find('.modal-dialog').css({
            top:scrollTop,
            });
        });
    $(document).on('click', '.btn-success', function () {
        $('#purchaseorder-purchas_status').attr('value','3');
    });
     $(document).on('click', '.btn-warning', function () {
        $('#purchaseorder-purchas_status').attr('value','4');
    });
     $(document).on('click', '.btn-info', function () {
        $('#purchaseorder-purchas_status').attr('value','5');
    });
     
    $(document).on('click','.sales', function () {
        $('#created-modal2').find('.modal-body').html('正在请求数据....');
        $.get($(this).attr('href'), {},
            function (data) {
                $('#created-modal2').find('.modal-body').html(data);
            }
        );
        return false;
    });
    $(document).on('click','.data-updatess', function () {

        $('#created-modal2').find('.modal-body').html('正在请求数据....');
        $.get('{$historys}', {sku:$(this).attr('sku')},
            function (data) {
                $('#created-modal2').find('.modal-body').html(data);

            }
        );
    });

    $(document).on('click', '.img', function () {

        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal2').find('.modal-body').html(data);
            }
        );
    });

});


JS;
$this->registerJs($js);
?>