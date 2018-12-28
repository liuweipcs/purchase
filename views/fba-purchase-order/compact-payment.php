<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use kartik\select2\Select2;
use app\services\BaseServices;
use app\models\ProductTaxRate;
use app\models\PurchaseOrderItems;

$pay_type = [
    '3' => '剩余数量',
    '2' => '到货数量',
    '1' => '入库数量',
    '4' => '手动请款',
    '5' => '比例请款',
];
//'3-23' => '剩余数量',
//'2-24' => '到货数量',
//'1-25' => '入库数量',
//'4-21' => '手动请款',
//'5-12' => '比例请款',


$list = BaseServices::getAlibaba();

$this->title = 'FBA采购合同-申请付款';
$this->params['breadcrumbs'][] = 'FBA采购';
$this->params['breadcrumbs'][] = '采购合同';
$this->params['breadcrumbs'][] = $this->title;
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
    <table class="my-table">
        <tr>
            <th colspan="6">基本信息</th>
        </tr>
        <tr>
            <td><strong>供应商名称</strong></td>
            <td><?= $model->supplier_name ?></td>
            <td><strong>运输方式</strong></td>
            <td><?= PurchaseOrderServices::getShippingMethod($model->shipping_method) ?></td>
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
            <td><strong>支付方式</strong></td>
            <td><?= !empty($model->pay_type) ? SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''; ?></td>
            <td><strong>结算方式</strong></td>
            <td><?= !empty($model->account_type) ? SupplierServices::getSettlementMethod($model->account_type) : ''; ?></td>
            <td><strong>结算比例</strong></td>
            <?php
                $settlement_ratio_arr = explode('+', $compactModel->settlement_ratio);
                $settlement_ratio = '';
                if(count($settlement_ratio_arr)>=3){
                    $settlement_ratio = '结算方式(月结)+10%定金+发货前30%尾款+到货后60%尾款月结';
                }else{
                    $settlement_ratio = $compactModel->settlement_ratio;
                }
            ?>
            <td><?= $settlement_ratio ?></td>
        </tr>
    </table>

</div>
<?php ActiveForm::begin(['id' => 'compact-payment']); ?>
    <h4>采购单信息</h4>
    <div class="my-box">
        <table class="my-table">
            <thead>
            <tr>
                <th>采购单号</th>
                <th>SKU</th>
                <th>图片</th>
                <th>单价</th>
                <th>名称</th>
                <th>运费</th>
                <th>优惠额</th>
                <th>订单数量</th>
                <th>取消数量</th>
                <th>入库数量</th>
                <th>已请款数量</th>
                <th>请款数量</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $pay_price = 0;
            $orderTotalPrice = 0; //合同总额
            
            // $sku_count_money = array_sum(array_map(create_function('$val', 'return $val["sku_count_money"];'), $orderInfos));
            $sku_count_money = $compactModel['real_money'];
            
            $order_freight = array_sum(array_map(create_function('$val', 'return $val["order_freight"];'), $orderInfos));
            $order_discount = array_sum(array_map(create_function('$val', 'return $val["order_discount"];'), $orderInfos));
            foreach ($orderInfos as $key => $orderInfo):
            $items = $orderInfo['purchaseOrderItems'];
            $r = count($items);
            foreach($orderInfo['purchaseOrderItems'] as $k=>$v):
                $img = Html::img(Vhelper::downloadImg($v['sku'], $v['product_img'], 2), ['width' => '110px', 'class' => 'img-thumbnail']);
                $sku_num = $v['ctq'] ? $v['ctq'] : 0;

                $pay1 = $v['ruku_num']-$v['yizhifu_num'];
                $pay2 = $sku_num-$v['yizhifu_num']-$v['quxiao_num']-$v['weidaohuo_num'];
                $pay3 = $sku_num-$v['yizhifu_num']-$v['quxiao_num'];
                $pay4 = 0;

                $arr  = [$pay3, $pay1, $pay2, $pay3, $pay4];
                $num  = $arr[$rpt];
                $pay_price += $num*$v['price'];
                $orderTotalPrice += $v['ctq']*$v['price'];
                ?>
                <input type="hidden" name="skus[<?= $k ?>][sku]" value="<?= $v['sku'] ?>">
                <input type="hidden" name="skus[<?= $k ?>][price]" value="<?= $v['price'] ?>">
                <tr>
                    <?php if($k == 0): ?>
                        <td rowspan="<?= $r ?>" style="vertical-align: middle;text-align: center;width: 150px;"><?= $v['pur_number'] ?></td>
                    <?php endif; ?>
                    <td><?= $v['sku'] ?></td>
                    <td><?= $img ?></td>
                    <td><?= $v['price'] ?></td>
                    <td><?= $v['name'] ?></td>
                    <td><?= $v['freight'] ?></td>
                    <td><?= $v['discount'] ?></td>
                    <td><?= $sku_num ?></td>
                    <td><?= $v['quxiao_num'] ?></td>
                    <td><?= $v['ruku_num'] ?></td>
                    <td><?= $v['yizhifu_num'] ?></td>
                    <td>
                        <input type="text" class="sku_num" name="skus[<?= $k ?>][num]" value="<?= $num ?>" style="width:45px;" data-price="<?= $v['price'] ?>"  data-pay1="<?= $pay1 ?>" data-pay2="<?= $pay2 ?>" data-pay3="<?= $pay3 ?>" data-pay4="<?= $pay4 ?>" data-pay5="0" readonly>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    $sku_count_money = $noCancelPrice = PurchaseOrderItems::getNoCancelTotalPrice($compactModel->compact_number);
    // $select_ratio = PurchaseCompact::getCompactPayPrice($compactModel->compact_number); //剩余有效sku的比例请款

    ?>
    <div class="my-box">
        <div class="fg">
            <p>
                【采购单号：<?= $orderInfo['pur_number'] ?>】
                【<?= PurchaseOrderServices::getPurchaseStatus($orderInfo['purchas_status']); ?>】
                【供应商：<?= $orderInfo['supplier_name'] ?>】</p>
            【订单总额：<?= $sku_count_money ?>】
            【运费：<?= $order_freight ?>】
            【优惠额：<?= $order_discount ?>】
            【已申请额：<?= $payInfo['countPayMoney'] ?>】
            【可申请额：<?= $sku_count_money-$payInfo['countPayMoney'] ?>】
            【取消金额：<?= isset($cancel_price)?$cancel_price:'' ?>】</p>
        </div>
        <div class="fg" style="border: 1px solid red;">
            <h5>请款方式计算公式：</h5>
            <p>剩余数量：订单数量-已请款数量-已取消数量</p>
            <p>到货数量：订单数量-已请款数量-已取消数量-未到货数量</p>
            <p>入库数量：入库数量-已请款数量</p>
            <p>手动请款：手动输入请款金额</p>
        </div>
        <div class="fg">
            <label>请款方式</label>
            <?php if($rpt == 0): ?>
                <select class="payType" name="payType">
                    <?php foreach ($pay_type as $tk=>$tv) {
                        echo "<option value=\"$tk\">$tv</option>";
                    }?>
                </select>
            <?php else: ?>
                <input type="text" name="payName" value="<?= $pay_type[$rpt] ?>" readonly>
                <input type="hidden" name="payType" value="<?= $rpt ?>">
            <?php endif; ?>
            <label>计算比例</label>
                <select class="pay_ratio" id="pay-ratio" name="pay_ratio" disabled>
                    <option value="0">请选择</option>
                    <?php foreach ($settlement_ratio_arr as $k=>$ratio) : 
                      $ratio = intval($ratio);
                    ?>
                    <option value="<?=$ratio;?>" sku-count-money="<?=$orderTotalPrice?>"><?php echo $ratio.'%/'. $orderTotalPrice*$ratio/100;?></option>
                    <?php endforeach; ?>
                </select>
            <?php if($rpt == 4): ?>
            <label>请款金额</label>
            <input type="text" id="pay_price" name="Payment[pay_price]" value="0">
            <label>订单金额</label>
            <input type="text" id="order_price" name="Payment[pay_price_total]" value="0" readonly="true">
            <?php else: ?>
            <label>请款金额</label>
            <input type="text" id="pay_price" name="Payment[pay_price]" value="<?= $orderTotalPrice ?>" readonly="<?= $rpt == 4 ? 'false' : 'true' ?>">
            <label>订单金额</label>
            <input type="text" id="order_price" name="Payment[pay_price_total]" value="<?= $orderTotalPrice ?>" readonly="true">
            <?php endif; ?>
            
            <label>运费/优惠额</label>
            <input type="checkbox" name="Payment[freight]" freight='0' value="<?= $order_freight ?>" >运费<?= $order_freight ?>
            <input type="checkbox" name="Payment[discount]" discount='0' value="<?= $order_discount ?>">优惠额<?= $order_discount ?>
        </div>
        <div class="fg">
            <label>备注</label>
            <textarea name="Payment[create_notice]" rows="3" cols="60"></textarea>
        </div>
    </div>
    <input type="hidden" name="rpt" id="rpt" value="<?= $rpt ?>">
    <input type="hidden" name="pur_number" value="<?= $orderInfo['pur_number'] ?>">
    <input type="hidden" name="shengyu_money" value="<?= $sku_count_money-$payInfo['countPayMoney'] ?>" id="shengyu_money">

    <div class="my-box">
        <input type="hidden" name="Payment[purchase_account]" value="<?= $model->account_type ?>">
        <input type="hidden" name="Payment[compact_number]" value="<?= $compactModel->compact_number ?>">
        <input type="hidden" name="Payment[source]" value="1"> <!-- source=1表示合同请款 -->
        <input type="hidden" name="Payment[js_ratio]" value="<?= $compactModel->settlement_ratio ?>">
        <button class="btn btn-success" type="button" id="sub-btn">去填写付款申请书</button>
    </div>
<?php ActiveForm::end();?>
<?php
$js = <<<JS
$(function() {
    
    var payType = $('#rpt').val();

    $('#sub-btn').click(function() {
       var ratio = $('#pay_ratio').val();
       var price = $('#pay_price').val();
       if(ratio == '' && price == '') {
           layer.alert('必须为本次请款选择或输入一个金额');
           return false;
       }
       
       $('#compact-payment').submit();
   });
    
    function floatAdd(arg1,arg2)
    {    
         var r1,r2,m;    
         try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
         try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}    
         m=Math.pow(10,Math.max(r1,r2));    
         return (arg1*m+arg2*m)/m;    
    }
    
    function checkMoney(v) {
        if(v == '') {
            return true;
        }
        var temp = /^\d+\.?\d{0,2}$/;
        if(temp.test(v)) {
            return true;
        } else {
            return false;
        }
    }
    
    function floatSub(arg1,arg2)
    {    
        var r1,r2,m,n;    
        try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
        try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}    
        m=Math.pow(10,Math.max(r1,r2));    
        //动态控制精度长度    
        n=(r1>=r2)?r1:r2;    
        return ((arg1*m-arg2*m)/m).toFixed(n);    
    }
    
    function accMul(arg1,arg2)
    {
        var m=0,s1=arg1.toString(),s2=arg2.toString();
        try{m+=s1.split(".")[1].length}catch(e){}
        try{m+=s2.split(".")[1].length}catch(e){}
        return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m); 
    }
    
    // 金额计算
    function countMoney(attr) 
    {
        var total_money = 0;
        $('.sku_num').each(function() {
            var price = $(this).attr('data-price');
            if(payType == 0) {
                var num = $(this).val();
            } else {
                var num = $(this).attr(attr);
            }
            var money = accMul(price, num);
            total_money = floatAdd(total_money, money);
            $(this).val(num);
        });
        return total_money;
    }
    
    // 方式切换
    $('.payType').change(function() {
        payType = $(this).val();
        var attr = '';
        $("#pay-ratio").attr("disabled", true);
        $("#pay-ratio").val(0);
        switch(payType) {
            case '1': //25剩余数量
                attr = 'data-pay1';
                $('#pay_price').attr('readonly', true);
                break;
            case '2': //24到货数量
                attr = 'data-pay2';
                $('#pay_price').attr('readonly', true);
                break;
            case '3': //23剩余数量
                attr = 'data-pay3';
                $('#pay_price').attr('readonly', true);
                break;
            case '4': //21手动请款
                attr = 'data-pay4';
                $('#pay_price').attr('readonly', false);
                break;
            case '5': //12比例请款
                attr = 'data-pay5';
                $("#pay-ratio").attr("disabled", false);
                break;
        }
        $('#pay_price').val(countMoney(attr));
        Total();
    });
    
    // 提交验证
    $('#btn-submit').click(function() {
        var pay_price = $('#pay_price').val(),
            shengyu_money = $('#shengyu_money').val();
        if(pay_price <= 0) {
            layer.alert('请款金额不能小于等于零！');
            return false;
        }
        if(parseFloat(pay_price) > parseFloat(shengyu_money)) {
            layer.alert('请款金额已经超过了可申请额，无法申请！');
            return false;
        }
        if($('#create_notice').val() == '') {
            layer.alert('请填写请款备注！');
            return false;
        }
        $('#payment-form').submit();
    });
    //手动输入金额
    $("input[name='Payment[pay_price]']").blur(function() {
        Total();
    });

    //结算比例
    $('.pay_ratio').change(function() {
        var pay_ratio = parseFloat($("#pay-ratio").val());
        var sku_count_money = parseFloat($('#pay-ratio').find('option:selected').attr('sku-count-money'));

        var pay_price = sku_count_money*pay_ratio/100;
        $("#pay_price").val(pay_price);
        Total();
    });
    //运费和优惠额
    $('input:checkbox').on('change', function(){
        Total();
    });
    function Total()
    {
        var freight = $("input[name='Payment[freight]']:checkbox:checked").val(); //运费
        var discount = $("input[name='Payment[discount]']:checkbox:checked").val(); //优惠
        var old_freight = 0;
        var old_discount = 0;
        var is_freight = $("input[name='Payment[freight]']").attr('freight');
        var is_discount = $("input[name='Payment[discount]']").attr('discount');
        var pay_price = $('#pay_price').val();  //当前请款金额

        if (freight === undefined) {
            freight = 0;
            old_freight = $("input[name='Payment[freight]']").attr('freight');
            $("input[name='Payment[freight]']").attr('freight', 0);
        } else {
            $("input[name='Payment[freight]']").attr('freight', freight);
        }

        if (discount === undefined) {
            discount = 0;
            old_discount = $("input[name='Payment[discount]']").attr('discount');
            $("input[name='Payment[discount]']").attr('discount', 0);
        } else {
            $("input[name='Payment[discount]']").attr('discount', discount);
        }

        if (is_freight>0) {
            freight = 0;
        }
        if (is_discount>0) {
            discount = 0;
        }

        var pay_price = parseFloat(pay_price) + parseFloat(freight) - parseFloat(discount) - parseFloat(old_freight) + parseFloat(old_discount);
        $("#pay_price").val(pay_price);
        
    }
});
JS;
$this->registerJs($js);
?>