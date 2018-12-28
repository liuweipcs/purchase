<?php
use yii\helpers\Html;
use yii\bootstrap\Modal;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
?>
    <style>
        h5 {
            font-weight: bold;
        }
        input { padding-left: 5px; }
        em {
            color: #e4393c;
            font-weight: 700;
            font-style: normal;
        }
        s {
            color: #e4393c;
            font-weight: 700;
        }
        p {
            margin: 0;
        }
        span.mm {
            padding: 0 10px 0 10px;
        }
        .container-fluid {
            border: 1px solid #ccc;
        }
        .row {
            border-top: 1px solid #ccc;
            padding: 10px;
        }
    </style>
<?php ActiveForm::begin(['id' => 'payment-form']); ?>
    <h4>申请付款</h4>
    <div class="container-fluid">
        <div class="row">
            <h5>订单信息</h5>
            <div class="col-md-12">
                <table class="table table-bordered">
                    <tr>
                        <th class="col-md-2">采购单号</th>
                        <td><?= $orderInfo['pur_number'] ?></td>
                        <th class="col-md-2">供应商</th>
                        <td><?= $orderInfo['supplier_name'] ?></td>
                    </tr>
                    <tr>
                        <th>结算方式</th>
                        <td><?= !empty($orderInfo['account_type']) ? SupplierServices::getSettlementMethod($orderInfo['account_type']) : ''; ?></td>
                        <th>支付方式</th>
                        <td><?= !empty($orderInfo['pay_type']) ? SupplierServices::getDefaultPaymentMethod($orderInfo['pay_type']) : ''; ?></td>
                    </tr>
                    <tr>
                        <th>是否含税</th>
                        <td><?= !empty($model->is_drawback) && $model->is_drawback == 2 ? '含税' : '不含税'?></td>
                        <th>是否加急</th>
                        <td><?= !empty($orderInfo['is_expedited'])?PurchaseOrderServices::getIsExpedited($orderInfo['is_expedited']):''; ?></td>
                    </tr>
                    <tr>
                        <th>1688拍单号</th>
                        <td><?= ($model->orderOrders) ? $model->orderOrders->order_number : ''; ?></td>
                        <th>账号</th>
                        <td><?= ($model->purchaseOrderAccount) ? $model->purchaseOrderAccount->account : ''; ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row" style="background-color: #f3f3f3;">
            <div class="col-md-2">图片</div>
            <div class="col-md-6">产品</div>
            <div class="col-md-4">数量</div>
        </div>
        <?php
        $pay_money = 0;
        foreach($orderInfo['purchaseOrderItems'] as $k=>$v):
            $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'img-thumbnail']);
            $sku_num = $v['ctq'] ? $v['ctq'] : 0; // 确认数量为空的情况下，以预期数量为准
            $pay1 = $v['ruku_num'];
            $pay2 = $sku_num-$v['yizhifu_num']-$v['quxiao_num']-$v['weidaohuo_num'];
            $pay3 = $sku_num-$v['yizhifu_num']-$v['quxiao_num'];
            $pay4 = $sku_num-$v['quxiao_num'];
            $arr = [$pay1, $pay1, $pay2, $pay3, $pay4];
            $num = $arr[$rpt];
            $pay_money += $num*$v['price'];
            ?>
            <div class="row">
                <input type="hidden" name="skus[<?= $k ?>][sku]" value="<?= $v['sku'] ?>">
                <input type="hidden" name="skus[<?= $k ?>][price]" value="<?= $v['price'] ?>">
                <div class="col-md-2">
                    <?= $img ?>
                </div>
                <div class="col-md-6">
                    <p>SKU：<em><?= $v['sku'] ?></em></p>
                    <p>单价：<em><?= $v['price'] ?></em></p>
                    <p>本次采购价：<em><?= $v['price'] ?></em></p>
                    <p><?= $v['name'] ?></p>
                </div>
                <div class="col-md-4">
                    <p>订单数量: <?= $sku_num ?></p>
                    <p>取消数量: <?= $v['quxiao_num'] ?></p>
                    <p>收货数量: <?= $v['shouhuo_num'] ?></p>
                    <p>未到货数量: <?= $v['weidaohuo_num'] ?></p>
                    <p>入库数量: <?= $v['ruku_num'] ?></p>
                    <p>不良品数量: <?= $v['nogoods'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="row" style="background-color: #f3f3f3;">
            <div class="col-md-12">
                <span class="mm">订单总金额: <em><?= sprintf("%.2f", $orderInfo['sku_count_money']) ?></em></span>
                <span class="mm">运费: <em><?= $orderInfo['order_freight'] ?></em></span>
                <span class="mm">优惠额: <em><?= $orderInfo['order_discount'] ?></em></span>
                <span class="mm">实际金额: <em><?= sprintf("%.2f", $orderInfo['order_real_money']) ?></em></span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label>请款金额</label>
                    <input type="text" class="form-control" name="order_real_money" value="<?= $orderInfo['order_real_money'] ?>" readonly>
                </div>
                <div class="form-group">
                    <label>请款备注</label>
                    <textarea class="form-control" id="create_notice" rows="3" name="create_notice" placeholder="请填写备注，这些财务会看到"></textarea>
                </div>
                <input type="hidden" name="payType" value="5">
                <input type="hidden" name="order_freight" value="<?= $orderInfo['order_freight'] ?>">
                <input type="hidden" name="order_discount" value="<?= $orderInfo['order_discount'] ?>">
                <button type="submit" class="btn btn-info">申请</button>
            </div>
        </div>

    </div>

    <input type="hidden" name="rpt" id="rpt" value="<?= $rpt ?>">
    <input type="hidden" name="pur_number" value="<?= $orderInfo['pur_number'] ?>">

<?php
ActiveForm::end();
?>
