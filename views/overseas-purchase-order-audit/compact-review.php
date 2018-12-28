<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\SupplierServices;
use app\models\SupplierQuotes;
use app\models\ProductTaxRate;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\SkuSalesStatistics;
use app\models\Stock;
use app\services\SupplierGoodsServices;


$public = $orderList[0];
?>
<style type="text/css">
    .img-rounded{width: 80px; !important;}
</style>

<?php $form = ActiveForm::begin(); ?>

<input type="hidden" id="purchas_status" name="status" value="">
<input type="hidden" name="compact_number" value="<?= $model->compact_number ?>">

<div style="margin-top: 15px;">

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#cp" aria-controls="cp" role="tab" data-toggle="tab">订单信息</a></li>
        <li role="presentation"><a href="#od" aria-controls="od" role="tab" data-toggle="tab">合同信息</a></li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="cp">

            <table class="table table-bordered">
                <tr>
                    <th>采购员</th>
                    <td><?= $public->buyer ?></td>
                    <th>结算方式</th>
                    <td><?= SupplierServices::getSettlementMethod($public->account_type); ?></td>
                    <th>结算比例</th>
                    <td><?= $model->settlement_ratio; ?></td>
                </tr>
                <tr>
                    <th>供应商</th>
                    <td><?= $public->supplier_name ?></td>
                </tr>
            </table>

            <?php foreach($orderList as $k => $order): ?>
                <div class="my-box" style="border: 1px solid red;margin-top: 10px;">
                    <label>订单号：</label>
                    <input type="text" name="pos[]" value="<?= $order->pur_number ?>" readonly>

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>图片</td>
                            <td>SKU</td>
                            <td>产品名称</td>
                            <td>采购数量</td>
                            <td>单价( RMB )</td>
                            <td>产品链接</td>
                            <td>金额</td>
                            <td>出口退税税率</td>
                            <td>采购开票点</td>
                            <td>是否退税</td>
                            <td>预计到货时间</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $items = $order->purchaseOrderItems;
                        foreach($items as $v):
                            $img = Vhelper::toSkuImg($v['sku'], $v['product_img']);
                            $supplierprice = SupplierQuotes::getQuotes($v['sku'], $order->supplier_code)['supplierprice'];
                            ?>
                            <tr>
                                <td style="width: 10%">

                                    <?=Html::a($img, ['purchase-suggest/img', 'sku' => $v['sku'], 'img' => $v['product_img']], ['class' => "img", 'style'=>'margin:0px;', 'title' => '大图查看','data-toggle' => 'modal', 'data-target' => '#created-modal'])?>

                                </td>

                                <td style="width: 10%">

                                    <?=Html::a($v->sku, ['#'], ['class' => "sales", 'data-sku' => $v->sku, 'title' => '销量统计','data-toggle' => 'modal', 'data-target' => '#created-modal']) ?>

                                </td>

                                <td style="width:15%">

                                    <a href="<?=Yii::$app->params['SKU_ERP_Product_Detail']. $v->sku ?>" target="_blank"><?= $v->name ?></a>

                                </td>







                                <?php

                                $style1='';
                                $style2='';
                                $style3='';
                                $style4='';

                                $date_start = date('Y-m-d 00:00:00');
                                $date_end   = date('Y-m-d 23:23:59');
                                $model2 = \app\models\PurchaseSuggest::find()
                                    ->select('qty')
                                    ->where(['sku' => $v['sku'], 'purchase_type' => 1])
                                    ->andWhere(['>','qty',0])
                                    ->andWhere(['between', 'created_at', $date_start, $date_end])
                                    ->scalar();

                                if(!empty($model2) && $v->ctq != $model2) {
                                    $style2 = "style='color:red'";
                                }

                                if(!empty($v->e_price) && $v->e_price !=0 && !empty($grade) && $grade->grade <= 3) {
                                    $style3 = "style='color:red'";
                                }

                                if (($v['price'] - $supplierprice) > 0) {
                                    $style3 = "style='color:red;font-weight: bold;'";
                                } elseif(($v['price'] - $supplierprice) < 0) {
                                    $style3 = "style='color: #04f751;font-weight: bold;'";
                                }

                                if(!empty($v->product_link) && !empty($grade) && $grade->grade <= 3) {
                                    $style4 = "style='color:red'";
                                }

                                ?>

                                <td <?=$style2?>>

                                    <?= $v->ctq.Html::a('', ['#'], [
                                        'data-toggle' => 'modal',
                                        'data-target' => '#created-modal',
                                        'class' => 'data-updatesd glyphicon glyphicon-zoom-in',
                                        'title' => '建议',
                                        'sku' => $v['sku'],
                                        'pur_number' => $v['pur_number'],
                                    ]);?>

                                </td>

                                <td <?=$style3?>>

                                    <?php echo round($v->price,2).Html::a('', ['#'],[
                                            'data-toggle' => 'modal',
                                            'data-target' => '#created-modal',
                                            'class'=>'data-updatess glyphicon glyphicon-zoom-in',
                                            'title'=>'历史采购记录',
                                            'sku'  => $v['sku'],
                                        ]);?>

                                </td>


                                <td <?= $style4?>>
                                    <?php
                                    $plink = \app\models\SupplierQuotes::getUrl($v->sku);
                                    if($v->product_link) {
                                        $prolink = $v->product_link;
                                    } else {
                                        $prolink = $plink;
                                    }
                                    ?>
                                    <a href="<?= $prolink ?>" target="_blank"><?= Vhelper::toSubStr($prolink,1,5) ?></a>
                                </td>

                                <td>
                                    <?php
                                        if($order->is_drawback == 2){//税金税金税金
                                            $rate = \app\models\PurchaseOrderTaxes::getABDTaxes($v->sku,$v->pur_number);
                                            $tax = bcadd(bcdiv($rate,100,2),1,2);
                                            $pay  = round($tax*$v->price*$v->ctq,2); //数量*单价*(1+税点)
                                        }else{
                                            $pay = round($v->price*$v->ctq,2);
                                        }
                                    ?>
                                    <?= $pay ?>

                                </td>

                                <td>

                                    <?= ProductTaxRate::getRebateTaxRate($v['sku']); ?>

                                </td>

                                <td>

                                    <?= \app\models\PurchaseOrderTaxes::getABDTaxes($v['sku'],$v['pur_number']).'%'; ?>

                                </td>

                                <td>
                                    <?= !empty($order->is_drawback) ? ($order->is_drawback==2?'是' : '否') : '否';?>
                                </td>

                                <td <?=$style1?>>

                                    <?= $order->date_eta ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>

            <?php endforeach; ?>

            <div class="my-box">
                <label>审核备注</label>
                <textarea name="audit_note" cols="100" rows="3"></textarea>
            </div>

            <?= Html::submitButton('审批通过(Ok)', ['class' => 'btn btn-success']) ?>
            <?= Html::submitButton('审批不通过(Rollback)', ['class' => 'btn btn-warning']) ?>

        </div>

        <div role="tabpanel" class="tab-pane" id="od">
            <?= $this->render("//template/tpls/{$tpl}", ['model' => $model, 'products' => $products, 'print' => true]); ?>
        </div>

    </div>

</div>


<?php ActiveForm::end(); ?>

<?php

Modal::begin([
    'id' => 'created-modal',
    'header' => '<h4 class="modal-title">系统信息</h4>',
    //'footer' => '<a href="#" class="btn btn-primary"  data-dismiss="modal"  >Close</a>',
    //'closeButton' =>false,
    'size'=>'modal-lg',
    'options'=>[
        //'data-backdrop'=>'static',//点击空白处不关闭弹窗
        'z-index' =>'-1',

    ],
]);
Modal::end();


//$historys = Url::toRoute(['tong-tool-purchase/get-history']);
$historys = Url::toRoute(['purchase-suggest/histor-purchase-info']);
$historyb = Url::toRoute(['purchase-suggest/suggest-quantity']);
$url=Url::toRoute(['product/viewskusales']);

$js = <<<JS
$(function() {
    
    
    $(document).on('click', '.sales', function () {
        $.get('{$url}', {sku:$(this).attr('data-sku')},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });
    
    $(document).on('click', '.img', function () {
        $.get($(this).attr('href'), {},
            function (data) {
               $('#created-modal').find('.modal-body').html(data);
            }
        );
    });


    function modal_close(data)
    {
        $('#created-modal').modal("hide");
    }
    
    $(document).on('click','.data-updatess', function () {
        $.get('{$historys}', {sku:$(this).attr('sku')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);

            }
        );
    });
    $(document).on('click','.data-updatesd', function () {
        $.get('{$historyb}', {sku:$(this).attr('sku'),'pur':$(this).attr('pur_number')},
            function (data) {
                $('#created-modal').find('.modal-body').html(data);

            }
        );
    });
    $("#created-modal").on("hidden", function() {
        $(this).removeData("modal");
    });
    $("#created-modal").on("hidden.bs.modal",function(){
        $(document.body).addClass("modal-open");
    });

    
    $('.btn-success').click(function() {
        $('#purchas_status').val('3');
    });
    
    $('.btn-warning').click(function () {
        $('#purchas_status').val('4');
    });
    
});


JS;
$this->registerJs($js);
?>
