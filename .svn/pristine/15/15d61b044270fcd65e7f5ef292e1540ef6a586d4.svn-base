<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\models\PurchaseOrderTaxes;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
?>


<?php ActiveForm::begin(['id' => 'payment-form']); ?>

<table class="table table-bordered table-condensed">
    <tr>
        <th class="col-md-2">采购单号</th>
        <td><?= $order['pur_number'] ?></td>
        <th class="col-md-2">状态</th>
        <td><?= PurchaseOrderServices::getPurchaseStatus($order['purchas_status']); ?></td>
    </tr>
    <tr>
        <th class="col-md-2">供应商</th>
        <td colspan="3"><?= $order['supplier_name'] ?></td>
    </tr>
    <tr>
        <th>结算方式</th>
        <td><?= !empty($order['account_type']) ? SupplierServices::getSettlementMethod($order['account_type']) : ''; ?></td>
        <th>支付方式</th>
        <td><?= !empty($order['pay_type']) ? SupplierServices::getDefaultPaymentMethod($order['pay_type']) : ''; ?></td>
    </tr>
    <tr>
        <th>是否含税</th>
        <td colspan="3"><?= !empty($model->is_drawback) && $model->is_drawback == 2 ? '含税' : '不含税'?></td>
    </tr>
</table>

<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">商品信息</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>#</th>
                <th>商品</th>
                <th>数量</th>
                <th>小计</th>
            </tr>
            </thead>
                <?php
                $skus = $data['purchaseOrderItems'];
                foreach($skus as $i => $v):
                    $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'media-object']);
                    if($order->is_drawback == 2) {
                        $rate = PurchaseOrderTaxes::getABDTaxes($v['sku'], $v['pur_number']);
                        $price = ((int)$rate*$v['price'])/100 + $v['price']; // price
                    } else {
                        $price = $v['price'];
                    }
                    $num = $v['ctq'] ? $v['ctq'] : 0;
                    $oneSkuMOney = $price*$num;
                    ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td width="430px">
                            <div class="media">
                                <div class="media-left">
                                    <?= $img ?>
                                </div>
                                <div class="media-body">
                                    <p>SKU：<?= $v['sku'] ?></p>
                                    <p>单价：<strong style="color: red;"><?= $price ?></strong></p>
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
                            </ul>
                        </td>
                        <td><?= $oneSkuMOney ?></td>
                    </tr>

                <?php endforeach; ?>

        </table>
    </div>
</div>

<input type="hidden" name="Remove[pur_number]" value="<?= $order['pur_number'] ?>">
<textarea name="Remove[confirm_note]" rows="3" class="form-control" placeholder="填写备注"></textarea>

<button type="submit" class="btn btn-danger" style="margin-top: 10px">作废</button>

<?php ActiveForm::end(); ?>


















