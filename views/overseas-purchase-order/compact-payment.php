<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\config\Vhelper;
use kartik\select2\Select2;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\models\ProductTaxRate;
use app\models\PurchaseOrderItems;
use app\models\PurchaseCompact;
$this->title = '海外仓采购合同-申请付款';
$this->params['breadcrumbs'][] = '海外仓';
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
    .box-span span {
        display: inline-block;
        padding: 0px 15px;
        color: red;
        font-size: 15px;
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
    <a class="btn btn-info" href="/purchase-compact/print-compact?id=<?=$model->id;?>" target="_blank">查看采购订单合同</a>
</div>

<div class="my-box">

    <table class="my-table">

        <tr>
            <th colspan="6">基本信息</th>
        </tr>

        <tr>
            <td><strong>供应商名称</strong></td>
            <td colspan="3"><?= $order->supplier_name ?></td>
            <td><strong>是否退税</strong></td>
            <td>
                <?php if($model->is_drawback == 1): ?>
                    <span class="label label-info">不退税</span>
                <?php elseif($model->is_drawback == 2): ?>
                    <span class="label label-success">退税</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>结算比例</strong></td>
            <?php
                $arr = explode('+', $model->settlement_ratio);
                $settlement_ratio = '';
                if(count($arr)>=3){
                    $settlement_ratio = '结算方式(月结)+10%定金+发货前30%尾款+到货后60%尾款月结';
                }else{
                    $settlement_ratio = $model->settlement_ratio;
                }
            ?>
            <td><?= $settlement_ratio ?></td>
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
            <th colspan="7">采购单信息</th>
        </tr>
        <tr>
            <td>采购单号</td>
            <td>商品</td>
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
            $items = $order->purchaseOrderItemsCtq; // 单个订单的sku信息
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

            if($model->is_drawback == 2) {
                $rate = \app\models\PurchaseOrderTaxes::getABDTaxes($v['sku'],$order->pur_number); // 开票点
                $price = ((float)$rate*$v['price'])/100 + $v['price']; // 新单价
            } else {
                $price = $v['price'];
            }

            $num = $v['ctq'] ? $v['ctq'] : 0; // 采购数量

            $oneSkuMOney = $price*$num;
            $skus += $num;
            $prices += $price;
            $moneys += $oneSkuMOney;
        ?>
        <tr>

            <?php if($k == 0): ?>

                <td rowspan="<?= $r ?>" style="vertical-align: middle;text-align: center;width: 150px;"><?= $order->pur_number ?></td>

            <?php endif; ?>


            <td width="430px">
                <div class="media">
                    <div class="media-left">
                        <?= $img ?>
                    </div>
                    <div class="media-body">
                        <p>SKU：<?= $v['sku'] ?></p>
                        <p><?= $v['name'] ?></p>
                    </div>
                </div>
            </td>



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
            <td colspan="2" style="text-align: right;">总计</td>
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
$noCancelPrice = PurchaseOrderItems::getNoCancelTotalPrice($model->compact_number);
$select_ratio = PurchaseCompact::getCompactPayPrice($model->compact_number); //剩余有效sku的比例请款

// 合同剩余可请款金额
$can_pay_money = $noCancelPrice - $has_pay['pay_price'];

?>

<div class="my-box">

    <table class="my-table">
        <tr>
            <th colspan="2">请款信息</th>
        </tr>
        <tr>
            <td class="tt">金额明细</td>
            <td>

                <div class="box-span">
                    <span>总商品额：<?= $model->product_money ?></span>
                    <span>总运费：<?= $model->freight ?></span>
                    <span>总优惠：<?= $model->discount ?></span>
                    <span>实际总额：<?= $model->real_money ?></span>
                    <span>已请款金额：<?= $has_pay['pay_price'] ?></span>
                    <span>可请款金额：<?= $can_pay_money ?></span>
                    <span>已取消金额：<?= $cancel_price ?></span>
                </div>

            </td>
        </tr>


        <?php if($model->is_drawback == 2): ?>

        <tr>
            <td class="tt">账号</td>
            <td>
            <select name="Payment[purchase_account]" class="form-control" style="width: 200px;">
                <option value="0">请选择...</option>
                <?php
                $accountes = BaseServices::getAlibaba();
                foreach($accountes as $k=>$v): ?>
                    <option value="<?= $k ?>"><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            </td>
        </tr>

        <tr>
            <td class="tt">拍单号</td>
            <td>
                <input type="text" name="Payment[pai_number]" class="form-control" style="width: 200px;" value="">
            </td>
        </tr>

        <?php endif; ?>


        <tr>
            <td class="tt">请款金额</td>
            <td>
                <select id="pay_ratio" class="form-control" name="Payment[pay_ratio]" style="width: 200px;">
                    <option value="">请选择...</option>
                    <?php foreach($select_ratio as $v): 
                        $ratio = intval($v['ratio']);
                        // $v['money'] = $noCancelPrice*$ratio/100;
                    ?>
                        <option value='<?= \yii\helpers\Json::encode($v); ?>'><?= $v['ratio'].'/'.$v['money'] ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="help-block">注：含税合同，申请运费，请在这里直接选择，不要在下面的输入框输入。</p>
            </td>
        </tr>


        <tr>
            <td class="tt">手动请款</td>
            <td>
                <input type="number" id="pay_price" name="Payment[pay_price]" class="form-control" style="width: 200px;" value="" placeholder="输入一个金额">
                <p class="help-block">注：针对合同里的尾款，部分请款时使用，可手动输入一个金额。</p>
                <p class="help-block">注：如果这里输入了金额，那么申请金额将以这里为准，上面选择的金额将无效。</p>
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
    
    $('#pay_ratio').change(function() {
        $('#pay_price').val('');
    });
    
    $('#pay_price').focus(function() {
        $('#pay_ratio').val('');
    });
    
   $('#sub-btn').click(function() {
       var ratio = $('#pay_ratio').val();
       var price = $('#pay_price').val();
       if(ratio == '' && price == '') {
           layer.alert('必须为本次请款选择或输入一个金额');
           return false;
       }
       
       $('#compact-payment').submit();
   });
});
JS;
$this->registerJs($js);
?>



