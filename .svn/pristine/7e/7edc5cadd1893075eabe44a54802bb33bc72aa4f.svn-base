<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\PurchaseOrder;
use app\models\PurchaseOrderPay;
use app\models\PurchaseOrderCancelSub;
use app\models\PurchaseOrderCancel;
use app\services\PurchaseOrderServices;

$item_price = PurchaseOrder::getOrderTotalPrice($items_details[0]['pur_number']); //订单总额
$pay_price = PurchaseOrderPay::getOrderPaidMoney($items_details[0]['pur_number']); //已付款的
$cancel_price = PurchaseOrderCancelSub::getCancelPriceOrder($items_details[0]['pur_number']); //已取消总金额

$freight = PurchaseOrderCancel::getFreight($cancel_id); //运费
$discount = PurchaseOrderCancel::getDiscount($cancel_id); //优惠额
?>
<?php echo $this->render('_public', ['order_details' => $order_details]); ?>
<?php ActiveForm::begin([
    'action'=>['cancel-audit'],
    // 'method' => 'get',
    'id'=>'submit-form']);
?>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-condensed">
                <tr>
                    <th class="col-md-2">运费</th>
                    <td><?= $freight ?></td>
                </tr>
                <tr>
                    <th class="col-md-2">优惠额</th>
                    <td><?= $discount ?></td>
                </tr>
                <tr>
                    <th class="col-md-2">SKU</th>
                    <th class="col-md-2">单价</th>
                    <th class="col-md-2">名称</th>
                    <th class="col-md-1">订单数量</th>
                    <th class="col-md-1">已取消数量</th>
                    <th class="col-md-1">入库数量</th>
                    <th class="col-md-2">取消数量</th>
                </tr>
                <tbody>
                <?php
                $cancel_price = 0;
                $cancel_total = 0;
                ?>
                <?php foreach($items_details as $k => $v):?>
                    <tr>
                        <td><?= $v['sku'] ?></td>
                        <td class="cancel_price"><?=$v['price']?></td>
                        <td><?= $v['name'] ?></td>
                        <td class="ctq"><?= $v['ctq'] ?></td><!--订单数量-->
                        <td><?= $v['quxiao_ctq'] ?></td><!--已取消数量-->
                        <td><?=$v['instock_qty_count'] ?></td><!--入库数量-->
                        <td class="ctqs"><?= $v['cancel_ctq'] ?></td><!--取消数量-->

                        <!-- 订单号 -->
                        <input type="hidden" name="cancel_ctq[pur_number][<?= $k ?>]" value="<?=$v['pur_number'] ?>">
                        <!-- sku -->
                        <input type="hidden" name="cancel_ctq[sku][<?= $k ?>]" value="<?=$v['sku'] ?>">
                        <!-- 需求单号 -->
                        <input type="hidden" name="cancel_ctq[demand_number][<?= $k ?>]" value="<?= $v['demand_number'] ?>">
                        <!-- 旧的需求状态 -->
                        <input type="hidden" name="cancel_ctq[old_demand_status][<?= $k ?>]" value="<?= $v['old_demand_status'] ?>">
                        <!-- 入库数量 -->
                        <input type="hidden" name="cancel_ctq[instock_qty_count][<?= $k ?>]" value="<?= $v['instock_qty_count'] ?>">
                        <!-- 订单数量 -->
                        <input type="hidden" name="cancel_ctq[ctq][<?= $k ?>]" value="<?= $v['ctq'] ?>">
                        <!-- 取消数量 -->
                        <input type="hidden" name="cancel_ctq[cancel_ctq][<?= $k ?>]" value="<?= $v['cancel_ctq'] ?>">
                        <!-- 单价 -->
                        <input type="hidden" name="cancel_ctq[price][<?= $k ?>]" value="<?= $v['price'] ?>">
                        <!-- 付款金额 -->
                        <input type="hidden" name="cancel_ctq[pay_price][<?= $k ?>]" value="<?= $v['pay_price'] ?>">
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th class="col-md-2 total" colspan="7" style="color: red;">
                        <?php
                        $info = PurchaseOrderCancelSub::getCancelDetail($cancel_id);
                        echo "取消件数：{$info['cancel_ctq_total']}件<br />取消金额：{$info['cancel_price_total']}元";
                        ?>
                    </th>
                </tr>
                <tr>
                    <th class="col-md-2">采购员备注</th>
                    <td colspan="6"><?=$order_details['buyer_note']?></textarea></td>
                </tr>
                <tr>
                    <th class="col-md-2">审核备注</th>
                    <td colspan="6"><textarea name="audit_note" class="form-control" rows="3" id="confirm_note" placeholder="请填写备注"></textarea></td>
                </tr>
                <tr>
                    <th class="col-md-2" rowspan="2">审核结果</th>
                    <td colspan="3"><input type="radio" name="audit_status" value="2" id="pass" checked>通过</td>
                    <td colspan="3"><input type="radio" name="audit_status" value="3" id="rebut">驳回</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <input type="hidden" name="pur_number" value="<?= $items_details[0]['pur_number'] ?>">
    <input type="hidden" name="cancel_id" value="<?=$cancel_id?>">
    <input type="hidden" name="freight" value="<?=$freight?>">
    <input type="hidden" name="discount" value="<?=$discount?>">

    <div class="form-group col-md-2" style="margin-top: 24px;">
        <?= Html::button(Yii::t('app', '提交'), ['class' => 'btn btn-primary submit']) ?>
        <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>
<?php
ActiveForm::end();
?>

<?php
$js = <<<JS
$(function() {
    var bool=true;
    //提交
    $('.submit').click(function() {
       if($.trim($('#confirm_note').val()) == '') {
            layer.alert('备注不能为空');
            return false;
       }else {
            $('#submit-form').submit();
            $(this).prop('disabled',true);
       }
    });
    //部分取消 启用编辑
    $('#part_cancel').click(function() {
        $('.cancel_ctq').attr('readonly', false);
        setTotal(bool=false);
    });
    //全部取消 禁用编辑
    $('#all_cancel').click(function() {
        $(".table tbody tr .ctqs").each(function(){
            var ctq = $(this).parent().find('.ctq').html();
            $(this).find('input[class*=cancel_ctq]').val(ctq);
        });
        $('.cancel_ctq').attr('readonly', false);
        var pay_price=$(this).attr('pay_price');
        var item_price=$(this).attr('item_price');
        total_pay_item_price = item_price-pay_price;
        setTotal(bool=true);
    });
    //统计取消的数量和金额
    $('.cancel_ctq').change(function() {
        var obj = $(this);
        var objTr = $(this).parent().parent();
        //获取单价
        var unitPrice = objTr.find(".cancel_price").html();
            
        var sum = accMul(unitPrice,obj.val());
        //objTr.find(".payable_amount").val(sum);
        setTotal(bool=false);
    });
    $('.freight').change(function() {
        setTotal(bool=false);
    });
    $('.discount').change(function() {
        setTotal(bool=false);
    });
    
    function accMul(arg1,arg2){
        //乘法
        var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
        try { m += s1.split(".")[1].length;}
        catch (e) {}
        try {m += s2.split(".")[1].length;}
        catch (e) {}
        return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
    }
    //数量修改时统计
    function setTotal(bool){
        var s=0;
        var cancel_ctq = 0;
        var cancel_price=0;
        $(".table tbody tr .ctqs").each(function(){
            cancel_ctq += parseInt(($(this).find('input[class*=cancel_ctq]').val())); //取消数量
            cancel_price = parseFloat($(this).parent().find('.cancel_price').html()); //取消单价
            s += parseInt(($(this).find('input[class*=cancel_ctq]').val())) * parseFloat($(this).parent().find('.cancel_price').html());
        });
        if (bool==true) {
            $(".total").html('取消件数：'+ cancel_ctq + '件<br />取消金额：'+ total_pay_item_price.toFixed(2)+'RMB<input type="hidden" name="cancel_total_price" value="'+ total_pay_item_price +'">');
        } 
        if (bool ==false) {
            var freight = parseFloat($('.freight').val());
            var discount = parseFloat($('.discount').val());
            var total_price = s + freight - discount;
            $(".total").html('取消件数：'+ cancel_ctq + '件<br />取消金额：'+ total_price.toFixed(2)+'RMB<input type="hidden" name="cancel_total_price" value="'+ total_price +'">');
        }
    }
});
JS;
$this->registerJs($js);
?>