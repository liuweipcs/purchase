<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\SupplierServices;
use app\services\BaseServices;
$pay_type = [
    '1' => '剩余数量',
    '2' => '到货数量',
    '3' => '入库数量',
    '4' => '手动请款',
];

$list = BaseServices::getAlibaba();


?>
<?php ActiveForm::begin(['id' => 'payment-form']); ?>
    <h4>申请付款</h4>
    <div class="my-box">

        <table class="my-table">
            <thead>
            <tr>
                <th>图片</th>
                <th>SKU</th>
                <th>单价</th>
                <th>名称</th>




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
                ?>


                <input type="hidden" name="skus[<?= $k ?>][sku]" value="<?= $v['sku'] ?>">
                <input type="hidden" name="skus[<?= $k ?>][price]" value="<?= $v['price'] ?>">


                <tr>
                    <td><?= $img ?></td>
                    <td><?= $v['sku'] ?></td>
                    <td><?= $v['price'] ?></td>
                    <td><?= $v['name'] ?></td>


                    <td><?= $sku_num ?></td>



                    <td><?= $v['quxiao_num'] ?></td>


                    <td><?= $v['ruku_num'] ?></td>
                    <td><?= $v['yizhifu_num'] ?></td>
                    <td>
                        <input type="text" class="sku_num" name="skus[<?= $k ?>][num]" value="<?= $num ?>" style="width:45px;" data-price="<?= $v['price'] ?>"  data-pay1="<?= $pay1 ?>" data-pay2="<?= $pay2 ?>" data-pay3="<?= $pay3 ?>" data-pay4="<?= $pay4 ?>" readonly>
                    </td>




                </tr>


            <?php endforeach; ?>







            </tbody>
        </table>






    </div>






    <div class="my-box">

        <div class="fg">

            <p>
                【采购单号：<?= $orderInfo['pur_number'] ?>】
                【<?= PurchaseOrderServices::getPurchaseStatus($orderInfo['purchas_status']); ?>】
                【供应商：<?= $orderInfo['supplier_name'] ?>】</p>
            【订单总额：<?= $orderInfo['sku_count_money'] ?>】
            【运费：<?= $orderInfo['order_freight'] ?>】
            【优惠额：<?= $orderInfo['order_discount'] ?>】
            【已申请额：<?= $payInfo['countPayMoney'] ?>】
            【可申请额：<?= $orderInfo['sku_count_money']-$payInfo['countPayMoney'] ?>】
            【实际总额：<?= $orderInfo['sku_count_money']+$orderInfo['order_freight']-$orderInfo['order_discount'] ?>】</p>
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
                    <option value="3">剩余数量</option>
                    <option value="2">到货数量</option>
                    <option value="1">入库数量</option>
                    <option value="4">手动请款</option>
                </select>

            <?php else: ?>
                <input type="text" name="payName" value="<?= $pay_type[$rpt] ?>" readonly>
                <input type="hidden" name="payType" value="<?= $rpt ?>">
            <?php endif; ?>

        </div>




        <div class="fg">
            <label>金额</label>


            <?php if($rpt == 4): ?>
                <input type="text" id="pay_price" name="pay_price" value="0">
            <?php else: ?>
                <input type="text" id="pay_price" name="pay_price" value="<?= $orderInfo['sku_count_money']-$payInfo['countPayMoney'] ?>"readonly="<?= $rpt == 4 ? 'false' : 'true' ?>">
            <?php endif; ?>



        </div>

        <div class="fg">

            <label>运费/优惠额</label>
            <input type="checkbox" name="freight" value="<?= $orderInfo['order_freight'] ?>">运费<?= $orderInfo['order_freight'] ?>
            <input type="checkbox" name="discount" value="<?= $orderInfo['order_discount'] ?>">优惠额<?= $orderInfo['order_discount'] ?>

        </div>


        <div class="fg">
            <label>备注</label>
            <textarea name="create_notice" rows="3" cols="60"></textarea>
        </div>


        <div class="fg">
            <label></label>

            <input type="button" id="btn-submit" value="提交">
            <input type="reset" value="重置">
        </div>

    </div>

    <input type="hidden" name="rpt" id="rpt" value="<?= $rpt ?>">
    <input type="hidden" name="pur_number" value="<?= $orderInfo['pur_number'] ?>">
    <input type="hidden" name="shengyu_money" value="<?= $orderInfo['sku_count_money']-$payInfo['countPayMoney'] ?>" id="shengyu_money">

<?php
ActiveForm::end();
?>
<?php
$js = <<<JS
$(function() {
    
    var payType = $('#rpt').val();
    
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
        switch(payType) {
            case '1':
                attr = 'data-pay1';
                $('#pay_price').attr('readonly', true);
                break;
            case '2':
                attr = 'data-pay2';
                $('#pay_price').attr('readonly', true);
                break;
            case '3':
                attr = 'data-pay3';
                $('#pay_price').attr('readonly', true);
                break;
            case '4':
                attr = 'data-pay4';
                $('#pay_price').attr('readonly', false);
                break;
        }
        $('#pay_price').val(countMoney(attr));
    });
    
    // 提交验证
    $('#btn-submit').click(function() {
        var pay_price = $('#pay_price').val(),
            shengyu_money = $('#shengyu_money').val();
        if(pay_price <= 0) {
            // layer.alert('请款金额不能小于等于零！');
            // return false;
        }

        var freight = $("input[name='freight']:checkbox:checked").val(); //运费
        var discount = $("input[name='discount']:checkbox:checked").val(); //优惠
        if (freight === undefined) {
            freight = 0;
        }
        if (discount === undefined) {
            discount = 0;
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
    
});
JS;
$this->registerJs($js);
?>