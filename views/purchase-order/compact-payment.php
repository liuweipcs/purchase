<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\config\Vhelper;
use app\services\SupplierServices;
$this->title = '国内仓采购合同-申请付款';
$this->params['breadcrumbs'][] = '国内仓';
$this->params['breadcrumbs'][] = '采购合同';
$this->params['breadcrumbs'][] = $this->title;
$order = $orders[0];
?>
<style type="text/css">
    .tt {
        width: 200px;
        text-align: center;
    }
    .cc {
        font-size: 16px;
        color: red;
    }
</style>

<h4><?= $model->compact_number ?> 申请付款</h4>

<div class="my-box" style="margin-bottom: 45px;">
    <div class="bg-line">
        <span>1</span>
        <p>填写请款信息</p>
    </div>
    <div class="bg-line no">
        <span>2</span>
        <p>填写付款申请书</p>
    </div>
</div>

<div class="my-box">
    <a href="/purchase-compact/print-compact?cpn=<?= $model->compact_number ?>" class="btn btn-info" target="_blank">查看采购订单合同</a>
</div>

<div class="my-box">
    <table class="my-table">
        <tr>
            <th colspan="7">基本信息</th>
        </tr>
        <tr>
            <td><strong>供应商名称</strong></td>
            <td colspan="6"><?= $order->supplier_name ?></td>
        </tr>
        <tr>
            <td><strong>结算比例</strong></td>
            <td><?= $model->settlement_ratio ?></td>
            <td><strong>结算方式</strong></td>
            <td><?= !empty($order->account_type) ? SupplierServices::getSettlementMethod($order->account_type) : ''; ?></td>
            <td><strong>支付方式</strong></td>
            <td><?= !empty($order->pay_type) ? SupplierServices::getDefaultPaymentMethod($order->pay_type) : ''; ?></td>
        </tr>
    </table>
</div>

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
            $price = $v['price']; // 新单价
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

<?php

$form = ActiveForm::begin(['id' => 'compact-payment']);
// 合同剩余可请款金额
$can_pay_money = $model->real_money - $has_pay['pay_price'];

?>

<div class="my-box">

    <table class="my-table">
        <tr>
            <th colspan="2">请款信息</th>
        </tr>
        <tr>
            <td class="tt">累计金额明细</td>
            <td>
                <p>产品总额：<strong class="cc"><?= $model->real_money ?></strong> (所有采购单金额 + 运费 - 优惠)</p>
                <p>已请款金额：<strong class="cc"><?= $has_pay['pay_price'] ?></strong></p>
                <p>可请款金额：<strong class="cc" id="kqk"><?= $can_pay_money ?></strong> (产品总额 - 已请款金额)</p>
            </td>
        </tr>
        <tr>
            <td class="tt">请款金额</td>
            <td>
                <select id="pay_ratio" class="form-control" name="Payment[pay_ratio]">
                    <option value="">请选择...</option>
                    <?php
                    foreach($select_ratio as $v):
                    ?>
                        <option value="<?= $v['ratio'].'/'.$v['money'] ?>"><?= $v['ratio'].'/'.$v['money'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td class="tt">备注</td>
            <td>
                <textarea name="Payment[create_notice]" rows="3" class="form-control"></textarea>
            </td>
        </tr>
    </table>
</div>

<div class="my-box">
    <input type="hidden" name="Payment[compact_number]" value="<?= $model->compact_number ?>">
    <input type="hidden" name="Payment[source]" value="1">
    <input type="hidden" name="Payment[js_ratio]" value="<?= $model->settlement_ratio ?>">

    <button class="btn btn-success" type="button" id="sub-btn">去填写付款申请书</button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(function(){
   $('#sub-btn').click(function() {
       var ratio = $('#pay_ratio').val();
       if(ratio == '') {
           layer.alert('必须为本次请款选择一个金额');
           return false;
       }
       $('#compact-payment').submit();
   });
});
JS;
$this->registerJs($js);
?>



