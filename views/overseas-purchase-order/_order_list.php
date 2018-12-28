<?php
use yii\helpers\Html;
use app\config\Vhelper;
use app\models\PurchaseOrderTaxes;
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
            <td>数量</td>
            <td>金额</td>
            <td>运费</td>
            <td>优惠金额</td>
        </tr>

        <?php

        // 数量统计
        $skus = 0;
        $prices = 0;
        $moneys = 0;
        $freights = 0;
        $discounts = 0;

        foreach($orders as $order):
            $items = $order->purchaseOrderItems; // 单个订单的sku信息
            $orderinfo = $order->purchaseOrderPayType;
            $freight = !empty($orderinfo) ? $orderinfo->freight : 0;
            $discount = !empty($orderinfo) ? $orderinfo->discount : 0;
            $r = count($items);

            $freights += $freight;
            $discounts += $discount;

            ?>


            <?php
            foreach($items as $k => $v):
                $img = Vhelper::downloadImg($v['sku'], $v['product_img'],2);
                $img = Html::img($img, ['width' => 100]);




                $rate = PurchaseOrderTaxes::getABDTaxes($v['sku'], $v['pur_number']);




                $price = ((int)$rate*$v['price'])/100 + $v['price']; // 新单价
                $num = $v['ctq'] ? $v['ctq'] : 0; // 采购数量
                $oneSkuMOney = $price*$num;

                $skus += $num;
                $prices += $price;
                $moneys += $oneSkuMOney;

                ?>
                <tr>

                    <?php if($k == 0): ?>

                        <td rowspan="<?= $r ?>" style="vertical-align: middle;text-align: center;"><?= $order->pur_number ?></td>

                    <?php endif; ?>


                    <td><?= $v->sku ?></td>
                    <td><?= Html::a($img, ['#'], ['class' => "img", 'data-skus' => $v['sku'], 'data-imgs' => $v['product_img'], 'title' => '大图查看', 'data-toggle' => 'modal', 'data-target' => '#created-modal']) ?></td>


                    <td style="width: 500px;"><?= $v->name ?></td>

                    <td><?= $price ?></td>
                    <td><?= $num ?></td>
                    <td><?= $oneSkuMOney ?></td>

                    <?php if($k == 0): ?>

                        <td rowspan="<?= $r ?>" style="vertical-align: middle;text-align: center;"><?= $freight ?></td>

                    <?php endif; ?>

                    <?php if($k == 0): ?>

                        <td rowspan="<?= $r ?>" style="vertical-align: middle;text-align: center;"><?= $discount ?></td>

                    <?php endif; ?>

                </tr>

            <?php endforeach; ?>

        <?php endforeach; ?>

        <tr>
            <td colspan="4" style="text-align: right;">总计</td>
            <td><strong><?= $prices ?></strong></td>
            <td><strong><?= $skus ?></strong></td>
            <td><strong><?= $moneys ?></strong></td>
            <td><strong><?= $freights ?></strong></td>
            <td><strong><?= $discounts ?></strong></td>
        </tr>
    </table>

</div>
