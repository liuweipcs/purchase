<?php
use yii\helpers\Html;
use app\config\Vhelper;
use app\models\PurchaseOrderTaxes;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderItems;
?>

<div class="my-box">
    <table class="my-table">
        <tr>
            <th colspan="10">采购单信息</th>
        </tr>
        <tr>
            <td>采购单号</td>
            <td>SKU</td>
            <td>图片</td>
            <td>品名</td>

            <td>单价</td>
            <?php if($is_drawback == 2): ?>
                <td>含税单价</td>
            <?php endif; ?>

            <td>数量</td>
            <td>金额</td>
            <td>运费</td>
            <td>优惠金额</td>
        </tr>

        <?php

        // 数量统计
        $skus = 0;
        $prices = 0;
        $o_prices = 0;
        $moneys = 0;
        $freights = 0;
        $discounts = 0;

        $pur_numbers = [];
        if (!empty($orders)) {
            $pur_numbers = Vhelper::changeData($orders);
            if (!empty($pur_numbers['pur_number'])) $pur_numbers = $pur_numbers['pur_number'];
        }
        $cancelInfo = PurchaseOrderCancelSub::getCancelCtq($pur_numbers);

        foreach($orders as $order):

            $items = $order->purchaseOrderItemsCtqArr; // 单个订单的sku信息
            $orderinfo = $order->purchaseOrderPayType;
            $freight = !empty($orderinfo) ? $orderinfo->freight : 0;
            $discount = !empty($orderinfo) ? $orderinfo->discount : 0;
            $r = count($items);

            $freights += $freight;
            $discounts += $discount;

            ?>

            <?php

            $is_first = true;
            //合并单元格
            $cancel_items = $items;
            foreach($cancel_items as $ck=>$cv):
                $csku = strtoupper($cv['sku']);
                if (!empty($cancelInfo[$cv['pur_number']][$csku]) && ($cancelInfo[$cv['pur_number']][$csku]==$cv['ctq'])) {
                    unset($cancel_items[$ck]);
                    continue;
                }
            endforeach;

            foreach($items as $k => $v):
                if (!empty($sku_list) && !in_array($v['sku'],$sku_list)) continue;
                //判断是否是作废的sku
                $icsku = strtoupper($v['sku']);
                if (!empty($cancelInfo[$cv['pur_number']][$icsku]) && ($cancelInfo[$cv['pur_number']][$icsku]==$v['ctq'])) continue;

                $img = Vhelper::downloadImg($v['sku'], $v['product_img'],2);
                $img = Html::img($img, ['width' => 100]);

                //判断是否是：FBA、海外，是（base_price）为原价
                $type = Vhelper::getNumber($v['pur_number']);
                if ($type!=1) {
                    $v['price'] = (int)$v['base_price']>0?$v['base_price']:$v['price'];
                }
                $o_price = $v['price'];
                if($is_drawback == 1) {
                    $price = $v['price'];
                } else {
                    $rate = PurchaseOrderTaxes::getABDTaxes($v['sku'], $v['pur_number']);
                    $price = ((float)$rate*$v['price'])/100 + $v['price'];
                }

                $num = $v['ctq'] ? $v['ctq'] : 0;
                $oneSkuMOney = $price*$num;

                $skus += $num;
                $prices += $price;
                $o_prices += $o_price;
                $moneys += $oneSkuMOney;

                ?>
                <tr>
                    <?php if($is_first == true): $is_first=false;?>

                        <td rowspan="<?= count($cancel_items) ?>" style="vertical-align: middle;text-align: center;"><?= $order->pur_number ?></td>

                    <?php endif; ?>

                    <td><?= $v['sku'] ?></td>
                    <td>
                        <a href='javascript:void(0)' class='img' title='点击查看大图'><?= $img ?></a>
                    </td>

                    <td style="width: 500px;"><?= $v['name'] ?></td>

                    <?php if($is_drawback == 2): ?>
                        <td><?= $o_price ?></td>
                        <td><?= $price ?></td>
                    <?php else: ?>
                        <td><?= $price ?></td>
                    <?php endif; ?>

                    <td><?= $num ?></td>
                    <td><?= $oneSkuMOney ?></td>

                    <?php if($k == 0): ?>

                        <td rowspan="<?= count($cancel_items) ?>" style="vertical-align: middle;text-align: center;"><?= $freight ?></td>

                    <?php endif; ?>

                    <?php if($k == 0): ?>

                        <td rowspan="<?= count($cancel_items) ?>" style="vertical-align: middle;text-align: center;"><?= $discount ?></td>

                    <?php endif; ?>

                </tr>

            <?php endforeach; ?>

        <?php endforeach; ?>

        <tr>
            <td colspan="4" style="text-align: right;">总计</td>

            <?php if($is_drawback == 2): ?>
                <td><strong><?= $o_prices ?></strong></td>
            <?php endif; ?>

            <td><strong><?= $prices ?></strong></td>
            

            <td><strong><?= $skus ?></strong></td>
            <td><strong><?= $moneys ?></strong></td>
            <td><strong><?= $freights ?></strong></td>
            <td><strong><?= $discounts ?></strong></td>
        </tr>
    </table>

</div>

