<?php
use yii\helpers\Html;
use app\config\Vhelper;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseOrderPayDetail;
use app\models\PurchaseOrderItems;

$listWhere = ['pur_number'=>$model->pur_number, 'requisition_number'=>$model->requisition_number];
$sku_list = PurchaseOrderPayDetail::getSkuList($listWhere);

?>
<style type="text/css">
    .box-span {
        padding: 5px;
    }
    .box-span span {
        display: inline-block;
        padding: 0px 15px 0px 0px;
        font-size: 15px;
    }
    .goods-box {
        overflow-y: scroll;
        max-height: 500px;
        display: block;
    }
</style>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">商品信息</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body goods-box">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>采购单号</th>
                <th>商品</th>
                <th>数量</th>
                <th>小计</th>
            </tr>
            </thead>
            <?php
            $totalMoney = 0;
            foreach($data as $k => $value):
                $skus = $value['purchaseOrderItems'];
                ?>

                <?php
                foreach($skus as $i => $v):
                    $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'media-object']);

                    $rate = -1;
                    if($compact->is_drawback == 1) {
                    } else {
                        $rate = PurchaseOrderTaxes::getABDTaxes($v['sku'], $v['pur_number']);
                    }
                    $itemsPrice = PurchaseOrderItems::getItemsPrice($v['pur_number'],$v['sku']);
                    $o_price = $itemsPrice['base_price'];
                    $price = $itemsPrice['price'];
                    $num = $v['ctq'] ? $v['ctq'] : 0;
                    $oneSkuMOney = $price*$num;
                    ?>

                    <tr>
                        <?php if($i == 0): ?>
                            <td rowspan="<?= count($skus) ?>" style="vertical-align: middle;text-align: center;"><?= $value['pur_number'] ?></td>
                        <?php endif; ?>

                        <td width="430px">
                            <div class="media">
                                <div class="media-left">
                                    <?= $img ?>
                                </div>
                                <div class="media-body">
                                    <p>SKU：<?= $v['sku'] ?></p>

                                    <?php if($rate == -1): ?>

                                        <p>单价：<strong style="color: red;"><?= $price ?></strong></p>

                                    <?php else: ?>

                                        <p>单价：<strong style="color: red;"><?= $price ?></strong>(原价格：<?= $o_price ?> 开票点：<?= $rate ?>)</p>

                                    <?php endif; ?>

                                    <p><?= $v['name'] ?></p>
                                </div>
                            </div>
                        </td>

                        <td width="250px;">
                            <ul class="list-unstyled">
                                <li>订单数量：<strong><?= $num ?></strong></li>
                                <li>取消数量：<strong><?= $v['quxiao_num'] ?></strong></li>
                                <li>收货数量：<strong><?= $v['shouhuo_num'] ?></strong></li>
                                <li>未到货数量：<strong><?= $v['weidaohuo_num'] ?></strong></li>
                                <li>入库数量：<strong><?= $v['ruku_num'] ?></strong></li>
                                <li>不良品数量：<strong><?= $v['nogoods'] ?></strong></li>
                                <?php if (is_array($sku_list)): ?>
                                    <li>本次请款数量：<strong><?= isset($sku_list[$v['sku']])?$sku_list[$v['sku']]:$num ?></strong></li>
                                <?php else: ?>
                                    <li>本次请款数量：<strong><?= $num ?></strong></li>
                                <?php endif; ?>
                            </ul>
                        </td>
                        <td><?= $oneSkuMOney ?></td>
                    </tr>

                    <?php
                    $totalMoney += $oneSkuMOney;
                endforeach;
                ?>
            <?php endforeach; ?>


            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td><?= $totalMoney ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="box box-warning">

    <div class="box-header">
        
        <div class="box-span">
            <span>供应商名称：<strong style="color: red;"><?= $compact->supplier_name; ?></strong></span>
            <span>支付方式：<strong style="color: red;"><?= !empty($model->pay_type) ? SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''; ?></strong></span>
            <span>是否退税：<?php
                if($compact->is_drawback == 1) {
                    echo '<span class="label label-success">不退税</span>';
                } else {
                    echo '<span class="label label-info">退税</span>';
                }
                ?>
            </span>
        </div>

        <div class="box-span">
            <span>请款类别：<strong style="color: red;"><?= $model->pay_name ?></strong></span>
            <?php if($model->pay_category != 10): ?>
            <span>本次请款比例：<strong style="color: red;"><?= $model->pay_ratio ?></strong></span>
            <?php endif; ?>
            <span>总结算比例：<?= $model->js_ratio ?></span>
        </div>

        <div class="box-span">
            <span>本次请款金额：<strong style="color: red;"><?= $model->pay_price ?></strong></span>
            <span>总商品额：<?= $compact->product_money ?></span>
            <span>总运费：<?= $compact->freight ?></span>
            <span>总优惠：<?= $compact->discount ?></span>
            <span>实际总额：<?= $compact->real_money ?></span>
        </div>

        <div class="box-span">
            <span>申请人：<strong style="color: red;"><?= BaseServices::getEveryOne($model->applicant) ?></strong></span>
            <span>申请时间：<?= $model->application_time ?></span>
        </div>

    </div>

    <div class="box-body">
        <p style="padding: 0px 0px 0px 5px;">请款备注：<?= $model->create_notice ?></p>
    </div>

</div>














