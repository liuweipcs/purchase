<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\models\PurchaseOrderTaxes;
$pay_type = [
    '1' => '剩余数量',
    '2' => '到货数量',
    '3' => '入库数量',
    '4' => '手动请款',
    '5' => '比例支付',
];

$real_price = $orderInfo['sku_count_money'] + $orderInfo['order_freight'] - $orderInfo['order_discount'];

if(!empty($model->is_drawback) && $model->is_drawback == 2) {
    $drawback_label = '<span class="label label-info">含税</span>';
    $is_drawback = true;
} else {
    $drawback_label = '<span class="label label-info">不含税</span>';
    $is_drawback = false;
}

?>
<style>
    h5 {
        font-weight: bold;
    }
    .container-fluid {
        border: 1px solid #ccc;
    }
    .row {
        padding: 8px;
    }
</style>

<?php ActiveForm::begin(['id' => 'payment-form']); ?>

    <h4>申请付款</h4>
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-12">
                <table class="table table-bordered table-condensed">
                    <tr>
                        <th class="col-md-2">采购单号</th>
                        <td><?= $orderInfo['pur_number'] ?></td>
                        <th class="col-md-2">状态</th>
                        <td><?= PurchaseOrderServices::getPurchaseStatus($orderInfo['purchas_status']); ?></td>
                    </tr>
                    <tr>
                        <th class="col-md-2">供应商</th>
                        <td colspan="3"><?= $orderInfo['supplier_name'] ?></td>
                    </tr>
                    <tr>
                        <th>结算方式</th>
                        <td><?= !empty($orderInfo['account_type']) ? SupplierServices::getSettlementMethod($orderInfo['account_type']) : ''; ?></td>
                        <th>支付方式</th>
                        <td><?= !empty($orderInfo['pay_type']) ? SupplierServices::getDefaultPaymentMethod($orderInfo['pay_type']) : ''; ?></td>
                    </tr>
                    <tr>
                        <th>是否含税</th>
                        <td><?= $drawback_label ?></td>
                        <th>是否加急</th>
                        <td><?= !empty($orderInfo['is_expedited']) ? PurchaseOrderServices::getIsExpedited($orderInfo['is_expedited']) : ''; ?></td>
                    </tr>
                </table>
            </div>

        </div>


        <div class="row" style="background-color: #f3f3f3;">
            <div class="col-md-2">图片</div>
            <div class="col-md-4">产品</div>
            <div class="col-md-6">数量</div>
        </div>

        <?php

        foreach($orderInfo['purchaseOrderItems'] as $k => $v):

            $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'img-thumbnail']);

            // 含税的单，计算税后单价
            if($is_drawback) {
                $point = PurchaseOrderTaxes::getABDTaxes($v['sku'], $v['pur_number']);
                $point_price = $v['price']*$point + $v['price'];
                $price_html = "<em>{$point_price}（含税点{$point}%）</em>";
            } else {
                $price_html = '<em>'.$v['price'].'</em>';
            }

            ?>

            <div class="row">

                <div class="col-md-2">
                    <?= $img ?>
                </div>

                <div class="col-md-4">
                    <p>SKU：<em><?= $v['sku'] ?></em></p>
                    <p>单价：<?= $price_html ?></p>
                    <p><?= $v['name'] ?></p>
                </div>

                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li>订单数量 : <strong><?= $v['ctq'] ?></strong></li>
                        <li>取消数量 : <strong><?= $v['quxiao_num'] ?></strong></li>
                        <li>收货数量 : <strong><?= $v['shouhuo_num'] ?></strong></li>
                        <li>未到货数量 : <strong><?= $v['weidaohuo_num'] ?></strong></li>
                        <li>入库数量 : <strong><?= $v['ruku_num'] ?></strong></li>
                        <li>不良品数量 : <strong><?= $v['nogoods'] ?></strong></li>
                    </ul>
                </div>

            </div>

        <?php endforeach; ?>

        <div class="row">

            <div class="col-md-2">
                <label>产品总额</label>
                <input type="text" class="form-control" value="<?= $orderInfo['sku_count_money'] ?>" disabled>
            </div>

            <div class="col-md-2">
                <label>运费</label>
                <input type="text" class="form-control" value="<?= $orderInfo['order_freight'] ?>" disabled>
            </div>

            <div class="col-md-2">
                <label>优惠</label>
                <input type="text" class="form-control" value="<?= $orderInfo['order_discount'] ?>" disabled>
            </div>

            <div class="col-md-2">
                <label>实际金额</label>
                <input type="text" class="form-control" value="<?= $real_price ?>" disabled>
            </div>

            <div class="col-md-2">
                <label>已申请金额</label>
                <input type="text" class="form-control" value="<?= $payInfo['countPayMoney'] ?>" disabled>
            </div>

            <div class="col-md-2">
                <label>可申请金额</label>
                <input type="text" class="form-control" value="<?= $orderInfo['sku_count_money'] - $payInfo['countPayMoney'] ?>" id="countPayMoney" disabled>
            </div>

        </div>

        <div class="row">

            <div class="col-md-2">

                <?php if($rpt == 0): ?>

                    <label>请款方式</label>
                    <select name="payType" class="form-control request-type">
                        <option value="0">请选择...</option>
                        <option value="4">手动请款</option>
                        <option value="5">比例支付</option>
                    </select>

                <?php else: ?>

                    <label>请款方式</label>
                    <input type="text" name="payName" class="form-control" value="<?= $pay_type[$rpt] ?>" readonly>
                    <input type="hidden" name="payType" value="<?= $rpt ?>">

                <?php endif; ?>

            </div>

            <?php if($rpt == 5): ?>

                <?php
                if(!empty($type)):
                    $list = [];
                    $settlement_ratio = '';
                    $settlement_ratio = $type->settlement_ratio;
                    $ratio_list = explode('+', $settlement_ratio);
                    foreach($ratio_list as $percent) {
                        $p = (float)$percent/100;
                        $list[$percent] = $real_price*$p;
                    }
                ?>
                <div class="col-md-2 ratio">
                    <div class="form-group">
                        <label for="is_freight">结算比例</label>
                        <select class="form-control settlement_ratio" name="settlement_ratio">
                            <option value="0">请选择...</option>
                            <?php foreach($list as $k=>$v): ?>
                            <option value="<?= $v ?>"><?= $k ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>

                <?php
                if(!empty($type)):
                    $list = [];
                    $settlement_ratio = '';
                    $settlement_ratio = $type->settlement_ratio;
                    $ratio_list = explode('+', $settlement_ratio);
                    foreach($ratio_list as $percent) {
                        $p = (float)$percent/100;
                        $list[$percent] = $real_price*$p;
                    }
                    ?>
                    <div class="col-md-2 ratio" style="display: none;">
                        <div class="form-group">
                            <label for="is_freight">结算比例</label>
                            <select class="form-control settlement_ratio" name="settlement_ratio">
                                    <option value="0">请选择...</option>
                                <?php foreach($list as $k=>$v): ?>
                                    <option value="<?= $v ?>"><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <div class="col-md-2">
                <div class="form-group">
                    <label for="pay_price">请款金额</label>
                    <?php if($rpt == 4): ?>
                        <input type="text" class="form-control" id="pay_price" name="pay_price" value="0" autocomplete="off">
                    <?php else: ?>
                        <input type="text" class="form-control" id="pay_price" name="pay_price" value="0" autocomplete="off" readonly="<?= $rpt == 4 ? 'false' : 'true' ?>">
                    <?php endif; ?>
                </div>
            </div>

            <textarea class="form-control" id="create_notice" rows="3" name="create_notice" placeholder="请填写备注，这些财务会看到"></textarea>

            <span class="btn btn-info" id="btn-submit" style="margin-top: 10px;">申请</span>

        </div>

    </div>

</div>

    <input type="hidden" name="rpt" id="rpt" value="<?= $rpt ?>">
    <input type="hidden" name="pur_number" value="<?= $orderInfo['pur_number'] ?>">

<?php
ActiveForm::end();
?>
<?php
$js = <<<JS
$(function() {
    
    var payType = $('#rpt').val();
    
    function checkMoney(v) {
        if(v == '') {
            return true;
        }
        var temp = /^\d+\.?\d{0,3}$/;
        if(temp.test(v)) {
            return true;
        } else {
            return false;
        }
    }
    
    function accMul(arg1,arg2)
    {
        var m=0,s1=arg1.toString(),s2=arg2.toString();
        try{m+=s1.split(".")[1].length}catch(e){}
        try{m+=s2.split(".")[1].length}catch(e){}
        return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m); 
    }
    
    // 方式切换
    $('.request-type').change(function() {
        payType = $(this).val();
        var attr = '';
        switch(payType) {
            case '4':
                attr = 'data-pay4';
                $('.ratio').hide();
                $('#pay_price').val('0');
                $('#pay_price').attr('readonly', false);
                break;
            case '5':
                $('.ratio').show();
                $('#pay_price').val('0');
                $('#pay_price').attr('readonly', true);
                break;
        }
        if(attr) {
            $('#pay_price').val(countMoney(attr));
        }
    });
    
    $('.settlement_ratio').change(function() {
        $('#pay_price').val($(this).val());
    });
    
    // 提交验证
    $('#btn-submit').click(function() {
        var pay_price = $('#pay_price').val();
        var countPayMoney = $('#countPayMoney').val();
        if(!checkMoney(pay_price)) {
            layer.alert('金额输入有误！');
            return false;
        }
        if(pay_price <= 0) {
            layer.alert('请款金额不能小于等于零！');
            return false;
        }
        if(parseFloat(pay_price) > parseFloat(countPayMoney)) {
            layer.alert('请款金额已经超过了可申请金额，无法申请！');
            return false;
        }
        if($('#create_notice').val() == '') {
            layer.alert('请务必填写请款备注！');
            return false;
        }
        $('#payment-form').submit();
    });
    
});
JS;
$this->registerJs($js);
?>