<?php
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
$noPayMoney = $orderInfo['order_real_money']-$payInfo['hasPaidMoney']; // 订单未付款
?>
<style>
    .middle{vertical-align:middle;text-align: center;}
</style>
<?php ActiveForm::begin(['id'=>'edit-form']);?>
<h5>编辑采购单</h5>
<div class="container-fluid">
    <?= $this->render('_public', ['orderInfo' => $orderInfo, 'payInfo' => $payInfo]); ?>
    <div class="row">
        <div class="col-md-12">
            <h5>付款明细</h5>
            <?php if($payInfo['countPayMoney'] > 0): ?>
                <table class="table table-bordered table-condensed" style="background-color: #ccc;">
                    <tr>
                        <th>订单总额</th>
                        <th>总请款额</th>
                        <th>已付总额</th>
                    </tr>
                    <tr>
                        <td><em><?= $orderInfo['order_real_money'].' '.$orderInfo['currency_code'] ?></em></td>
                        <td><em><?= $payInfo['countPayMoney'].' '.$orderInfo['currency_code'] ?> </em></td>
                        <td><em><?= $payInfo['hasPaidMoney'].' '.$orderInfo['currency_code'] ?></em></td>
                    </tr>
                    <tr>
                        <th>请款单状态</th>
                        <td colspan="2">
                            <?php
                            foreach($payInfo['payStatusList'] as $type) {
                                echo PurchaseOrderServices::getPayStatusType($type);
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            <?php else: ?>
                <table class="table table-bordered">
                    <tr>
                        <td colspan="2" style="color: #ccc;text-align: center;">该订单还没有付款记录</td>
                    </tr>
                </table>
            <?php endif; ?>
            <h5>退款明细</h5>
            <?php if($refundInfo['has_refund_money'] > 0): ?>
                <?php if(isset($refundInfo['refund_list']) && count($refundInfo['refund_list'])>0):?>
                    <table class="table table-bordered table-condensed" style="background-color: #ccc;">
                        <tr>
                            <th>退款操作人</th>
                            <th>退款时间</th>
                            <th>退款金额</th>
                        </tr>
                        <?php foreach ($refundInfo['refund_list'] as $key=>$value):?>
                            <tr>
                                <td><?= $value['username'] ?></td>
                                <td><?= $value['application_time'] ?></td>
                                <td>
                                    <em onclick="getSkuDetail('<?=$value['requisition_number']?>',this)" style="cursor: pointer" is_show="0"><?= $value['pay_price'].' '.$value['currency'] ?> </em>
                                    <?php if($value['freight']>0):?>
                                        <em>&nbsp;&nbsp;运费:<?= $value['freight'].' '.$value['currency'] ?> </em>
                                    <?php endif; ?>
                                    <?php if($value['discount']>0):?>
                                        <em>&nbsp;&nbsp;优惠:<?= $value['discount'].' '.$value['discount'] ?> </em>
                                    <?php endif; ?>
                                    <span id="<?=$value['requisition_number']?>" style="display: block"></span>
                                </td>
                            </tr>
                        <?php endforeach;?>
                    </table>
                <?php endif;?>
                <table class="table table-bordered table-condensed" style="background-color: #ccc;">
                    <tr>
                        <th>退款次数</th>
                        <th>已退金额</th>
                        <th>可退金额</th>
                    </tr>
                    <tr>
                        <td><em><?= $refundInfo['refund_num'] ?></em>（次）</td>
                        <td><em><?= $refundInfo['has_refund_money'].' '.$refundInfo['currency'] ?> </em></td>
                        <td><em><?= $payInfo['hasPaidMoney'] - $refundInfo['has_refund_money_true'].' '.$refundInfo['currency'] ?></em></td>
                    </tr>
                    <tr>
                        <th>退款单状态</th>
                        <td colspan="2">
                            <?php
                            foreach($refundInfo['refund_status_list'] as $type) {
                                echo PurchaseOrderServices::getReceiptStatusCss($type);
                            }
                            ?>
                        </td>
                    </tr>
                </table>

            <?php else: ?>

                <table class="table table-bordered">
                    <tr>
                        <td colspan="2" style="color: #ccc;text-align: center;">该订单还没有退款记录</td>
                    </tr>
                </table>

            <?php endif; ?>

        </div>
    </div>

    <?php if(count($ctq)>0 || ($only_all_able == 1 && ($freight>0 || $discount>0))): ?>
        <?php if($payInfo['hasPaidMoney'] > 0): ?>
            <div class="row">
                <div class="col-md-12">
                    <h5>退货明细</h5>
                    <table class="table table-bordered table-condensed">
                        <tr>
                            <th class="col-md-2" style="vertical-align:middle;text-align: center;">退款方式</th>
                            <td colspan="8">
                                <input type="radio" name="refund_status" value="3" <?php if(!empty($ctq)):?>checked <?php else: ?> disabled <?php endif;?>> 部分退款
                                <input type="radio" name="refund_status" value="4" data-money="<?= $payInfo['hasPaidMoney'] ?>" style="margin-left:20px" <?php if(empty($ctq)):?>checked<?php endif;?>> 全额退款
                            </td>
                        </tr>
                        <tr>
                            <th class="col-md-2" style="vertical-align:middle;text-align: center;">运费/优惠</th>
                            <td colspan="8">
                                <input type="text" class="form-control" name="freight" style="width: 200px;display:inline;" placeholder="请输入要取消的运费" onkeyup="checkFreight(this,<?=$freight?>)" <?php if($freight==0):?> readonly <?php endif;?>>元<span>(可退金额为<?=$freight?>元)</span><br/>
                                <input type="text" class="form-control" name="discount" style="width: 200px;display:inline;margin-top:10px" onkeyup="checkFav(this,<?=$discount?>)" placeholder="请输入要取消的优惠" <?php if($discount==0):?> readonly <?php endif;?>>元<span>(可退优惠为<?=$discount?>元)</span>
                            </td>
                            <input type="hidden" id="freight_able" value="<?=$freight?>">
                            <input type="hidden" id="discount_able" value="<?=$discount?>">
                        </tr>
                        <tr>
                            <th class="col-md-2" style="vertical-align:middle;text-align: center;" rowspan="<?=count($ctq)+2?>">SKU信息</th>
                            <th style="vertical-align:middle;text-align: center;">SKU</th>
                            <th style="vertical-align:middle;text-align: center;">单价</th>
                            <th style="vertical-align:middle;text-align: center;">名称</th>
                            <th style="vertical-align:middle;text-align: center;">订单数量</th>
                            <th style="vertical-align:middle;text-align: center;">收货数量</th>
                            <th style="vertical-align:middle;text-align: center;">已取消数量</th>
                            <th style="vertical-align:middle;text-align: center;">未到货数量</th>
                            <th style="vertical-align:middle;text-align: center;">退货数量</th>
                        </tr>
                        <?php
                            foreach ($orderInfo['purchaseOrderItems'] as $k => $v):
                                if(isset($ctq[$v['sku']]) && $ctq[$v['sku']] > 0):
                        ?>
                                    <tr>
                                        <td style="vertical-align:middle;text-align: center;"><?=$v['sku']?></td>
                                        <td style="vertical-align:middle;text-align: center;" class="price"><?=$v['price']?></td>
                                        <td style="vertical-align:middle;text-align: center;"><?=$v['name']?></td>
                                        <td style="vertical-align:middle;text-align: center;"><?=$v['ctq']?></td>
                                        <td style="vertical-align:middle;text-align: center;"><?= $v['shouhuo_num'] ?></td>
                                        <td style="vertical-align:middle;text-align: center;">
                                            <?php if(isset($refund_qty[$v['sku']]) && $refund_qty[$v['sku']] > 0): ?>
                                                <?=$refund_qty[$v['sku']]?>
                                            <?php else: ?>
                                                0
                                            <?php endif; ?>
                                        </td>
                                        <td style="vertical-align:middle;text-align: center;"><?=$ctq[$v['sku']] ?></td>
                                        <td style="vertical-align:middle;text-align: center;">
                                            <input type="text" class="form-control" name="refund_qty[<?= $v['sku'] ?>]" value="<?=$ctq[$v['sku']] ?>" price="<?=$v['price']?>" onkeyup="cal_cost(this)" weidaohuo_num="<?=$ctq[$v['sku']] ?>" cancel_num="<?=$refund_qty[$v['sku']]?>" sku="<?=$v['sku']?>">
                                        </td>
                                    </tr>
                        <?php
                                endif;
                            endforeach;
                        ?>
                        <tr>
                            <td colspan="8" style="color: red;font-size: 16px;font-weight: bold">
                                取消件数:<input type="text" class="form-control" id="sub-piece" name="money" value="<?=$total_piece?>" style="width: 100px;display: inline" readonly>
                                &nbsp;&nbsp;&nbsp;
                                取消金额:<input type="text" class="form-control" id="sub-money" name="money" value="<?=$total_price?>" style="width: 100px;display: inline" readonly>
                            </td>
                            <input type="hidden" value="<?=$total_price?>" id="price_able">
                        </tr>
                        <tr>
                            <th class="col-md-2" style="vertical-align:middle;text-align: center;">请填写备注</th>
                            <td colspan="8">
                                <textarea class="form-control" id="confirm_note" rows="3" name="confirm_note" placeholder="请填写备注"></textarea>
                            </td>
                        </tr>
                    </table>
                    <p style="margin:0;color:red;"><span class="glyphicon glyphicon-volume-up"></span>  财务没有付款的订单，不可以申请退款</p>
                    <p style="margin:0;color:red;"><span class="glyphicon glyphicon-volume-up"></span>  退款金额最大值不能超过可退金额</p>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">

            <?php if($payInfo['hasPaidMoney'] > 0): ?>
                <?php if(count($ctq)>0 || ($only_all_able == 1 && ($freight>0 || $discount>0))): ?>
                    <span id="refund" class="btn btn-primary">申请退款</span>
                <?php endif; ?>
            <?php else: ?>
                <?php if($orderInfo['purchas_status'] == 8 && $orderInfo['pay_status'] == 1):?>


                <?php else: ?>
                    <input type="hidden" name="refund_status" value="10">
                    <div class="form-group">
                        <label>请填写备注</label>
                        <textarea class="form-control" id="confirm_note" rows="3" name="confirm_note" placeholder="请填写备注"></textarea>
                    </div>
                    <p style="margin:0;color:red;"><span class="glyphicon glyphicon-volume-up"></span>  财务没有付款的订单，不可以申请退款</p>
                    <p style="margin:0;color:red;"><span class="glyphicon glyphicon-volume-up"></span>  退款金额最大值不能超过可退金额</p>
                    <span id="cancel" class="btn btn-danger">作废订单</span>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

    <input type="hidden" name="has_paid_money" value="<?= $payInfo['hasPaidMoney'] ?>" id="has_paid_money">
    <input type="hidden" name="order_real_money" value="<?= $orderInfo['order_real_money'] ?>">
    <input type="hidden" name="pur_number" value="<?= $orderInfo['pur_number'] ?>" id="pur_number">

</div>
<?php
ActiveForm::end();
?>

<?php
$js = <<<JS
$(function() {
    var t = 4;
    
    // 退款方式切换
    $('input[name="refund_status"]').click(function() {
        if($(this).prop('checked')) {
            t = $(this).val();
            if(t == 3) { // 部分
                //计算总件数和总金额
                var total_piece = 0;
                var total_price = 0.000;
                $("input[name^='refund_qty']").each(function(){
                    //单价
                    var price = $(this).attr('price');
                    //未到货数量
                    var refund_qty = $(this).val();
                    total_piece += parseInt(refund_qty);
                    total_price += accMul(refund_qty,price);
                    $(this).removeAttr('readonly');
                });
                //加上运费、减去优惠 计算总费用
                //var freight_able = $("#freight_able").val();
                //var discount_able = $("#discount_able").val();
                //total_price = floatAdd(total_price,freight_able);
                //total_price = floatSub(total_price,discount_able);
                
                $('#sub-money').val(total_price);
                $('#sub-money').attr('readonly', true);
                $('#sub-piece').val(total_piece);
                $('#sub-piece').attr('readonly', true);
            } else if(t == 4) { // 全额
                //计算总件数和总金额
                var total_piece = 0;
                var total_price = 0.000;
                $("input[name^='refund_qty']").each(function(){
                    //单价
                    var price = $(this).attr('price');
                    //未到货数量
                    var weidaohuo_num = $(this).attr('weidaohuo_num');
                    //已取消数量
                    var cancel_num = $(this).attr('cancel_num');
                    $(this).val(weidaohuo_num);
                    $(this).attr('readonly', true);
                    
                    total_piece += parseInt(weidaohuo_num);
                    total_price += accMul(weidaohuo_num,price);
                });
                
                //total_price = $("#price_able").val();
                $('#sub-money').val(total_price);
                $('#sub-money').attr('readonly', true);
                $('#sub-piece').val(total_piece);
                $('#sub-piece').attr('readonly', true);
            }
        } 
        
    });
   
   $('#cancel').click(function() {
      if($.trim($('#confirm_note').val()) == '') {
           layer.alert('备注不能为空');
           return false;
      }
      $('#edit-form').submit();
   });
   
   $('#refund').click(function() {
       var m = $('#sub-money').val();
       var pm = $('#has_paid_money').val(); // 最大可退金额
       var freight = $("input[name='freight']").val();//运费
       var discount = $("input[name='discount']").val();//优惠
       var freight_able = $("#freight_able").val();//最多可退运费
       var discount_able = $("#discount_able").val();//最多可退优惠
       if(freight < 0){
           layer.alert('运费不能小于0');
           return false;
       }
       /*if(freight > freight_able){
           layer.alert("运费不能超过"+freight_able+"元");
           return false;
       }*/
       if(discount < 0){
           layer.alert('优惠不能小于0');
           return false;
       }
       /*if(discount > discount_able){
           layer.alert("优惠不能超过"+discount_able+"元");
           return false;
       }*/
       
       var qty_msg = '';
       var is_write = '';
       var is_int_msg = '';
       var regex=/^\d+$/;//判断当前述是否为正整数
       $("input[name^='refund_qty']").each(function(){
            //未到货数量
            var weidaohuo_num = $(this).attr('weidaohuo_num');
            //sku
            var sku = $(this).attr('sku');
            //退货数量
            var piece = $(this).val();
            if(piece==''){
                is_write += "SKU:"+sku+"未填写退货数量;<br/>";
            }
            if(!regex.test(piece)){
                is_int_msg += "SKU:"+sku+"退货数量必须为正整数;<br/>";
            }
            
            //已取消数量
            var cancel_num = $(this).attr('cancel_num');
            var true_num = parseInt(weidaohuo_num);
            if(piece > true_num){
                qty_msg += "SKU:"+sku+"退货数量不能大于未到货数量;<br/>";
            }
       });
       if(is_write != '') {
           layer.alert(is_write);
           return false;
       }
       if(is_int_msg != '') {
           layer.alert(is_int_msg);
           return false;
       }
       if(qty_msg != '') {
           layer.alert(qty_msg);
           return false;
       }
       if(parseFloat(m) < 0) {
           layer.alert('退款金额有问题');
           return false;
       }
       if(parseFloat(m) > parseFloat(pm)) {
           layer.alert('退款金额超限');
           return false;
       }
       if($.trim($('#confirm_note').val()) == '') {
           layer.alert('备注不能为空');
           return false;
       }
       var indexLoad = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
       $('#edit-form').submit();
   });
       
/*       
       
       
       
       
       
       
       
       
       
       
       
       
       
    var checkId = $("input[name='payType']:checked").attr("id");
	excuteTag();
    //单击radio，处理对应的dom变化
	$("input[name='payType']").click(function(){
		var check_temp = $(this).attr("id");
		if(check_temp == checkId){
			return;
		}

		excuteTag();//disabled所有的checkbox和text
		var obj_tr = $(this).parent().parent().parent();//找到单击dom所在的对应tr

		obj_tr.find(":input").removeAttr("disabled");//释放该radio下对应的input的disabled

		$("#payMoney").html("0.000");
		$("input[name='PurchaseOrderPay[payMoney]']").val("");

		checkId = $(this).attr("id");
	});
	// 默认显示全额
	var obj_tr = $("#payAll").parent().parent().parent();

	obj_tr.find(":input").removeAttr("disabled");
	obj_tr.find(":input").attr("checked","checked");

	/!*
	 * 全额--SKU金额
	 *!/
	$("#purchase").on("click",function(){
		var arrayObj =getAllParams($(this))
		getAmount("1",arrayObj);
	}).trigger("click");

	/!*
	 * 全额--运费
	 *!/
	$("#freight").on("click",function(){
		var arrayObj =getAllParams($(this))
		getAmount("1",arrayObj);
	});

	$("#prepaidAmount").on("keyup",function(){
		var valueAmount = $(this).val();
		$("#payMoney").html(new Number(valueAmount).toFixed(3));
    	$("input[name='payMoney']").val(new Number(valueAmount).toFixed(3));
	});*/

});

/**
 * 校验金额是否是数字
 * @param number
 * @returns {Boolean}
 */
function testRegex(number){
	var regex=/^[0-9]+\.?[0-9]*$/;
	if(regex.test(number)==false){
		return false;
	}
	return true;
}



/**
 * 全部金额 参数设置
 * @param obj
 * @returns {Array}
 */
function getAllParams(obj){
	var arrayObj = new Array();
	arrayObj[0] = $("#po_code").val();
	arrayObj[1] = "";
	arrayObj[2] = "";
	var tempTr = $(obj).parent().parent().parent();

	var purchase = tempTr.find("input[name='purchase']");
	var freight = tempTr.find("input[name='freight']");

	if(purchase.is(":checked")){
		arrayObj[1] = "true";
	}

	if(freight.is(":checked")){
		arrayObj[2] = "true";
	}

	return arrayObj;
}
/*
 * 判断Json数据是否返回正常
 */
function isJson(obj) {
    var jsonData = typeof(obj) == "object" && Object.prototype.toString.call(obj).toLowerCase() == "[object object]" && !obj.length;
    if (jsonData && obj.reLogin) {
        alertReLoginTip("<span class='tip-warning-message'>" + obj.message + "</span>");
        return false;
    }
    return jsonData;
}
/**
 * 获取申请支付金额
 * @param mode
 * @param array
 */
function getAmount(mode,array){
	$.ajax({
        type: "POST",
        async: false,
        dataType: "json",
        url: "/purchase-order/get-purchase-amount",
        data:{mode:mode,ast:array},
        success: function (json){

        	if (!isJson(json)) {
                $("#payMoney").html("Internal error.");
            }
            if (json.status == '1') {
            	var amount = 0;
            	if($("input[name='payMoneyDis']").val() < json.amount){
            		amount = $("input[name='payMoneyDis']").val();
            	}else{
            		amount = json.amount;
            	}

            	var numberAmount = new Number(amount);
            	$("#payMoney").html(numberAmount.toFixed(3));
            	$("input[name='PurchaseOrderPay[payMoney]']").val(numberAmount.toFixed(3));
            }else{
            	$("#payMoney").html("获取采购单总额失败！");
            	$("input[name='PurchaseOrderPay[payMoney]']").val("");
            }
    	}
    });
}

function excuteTag(){
	$("input[type='checkbox'],input[name='PurchaseOrderPay[prepaid]']")
		.attr("disabled","disabled")
		.attr('checked', false);
	$("input[name='PurchaseOrderPay[prepaid]']").val("");
	$("input[name='id[]']").attr("disabled",false);
	$("input[name='id_all']").attr("disabled",false);
}

function cal_cost(obj) {
    //计算总件数、金额 
    var total_piece = 0;
    var total_price = 0.000;
    //判断当前输入值是否合法
    var self_refund_qty = $(obj).val();//当前退货数量
    var self_cancel_num = $(obj).attr('cancel_num');//已取消数量
    var self_weidaohuo_num = $(obj).attr('weidaohuo_num');//未到货数量
    //判断当前述是否为正整数
    var regex=/^\d+$/;
    if(!regex.test(self_refund_qty)){
        layer.alert('退货数量必须是正整数');
        return false;
    }
   
    var now_num = parseInt(self_weidaohuo_num);
    if(self_refund_qty > now_num){
        layer.alert('退货数量不能大于未到货数量');
        return false;
    }
    
    //计算取消件数和取消金额
    $("input[name^='refund_qty']").each(function(){
         //退货数量
        var refund_qty = $(this).val();
        //单价
        var price = $(this).attr('price');
        //未到货
        var weidaohuo_num = $(this).attr('weidaohuo_num');
        //已取消数量
        var cancel_num = $(this).attr('cancel_num');
        total_price += accMul(refund_qty,price);
        total_piece += parseInt($(this).val());
    });
    //加上运费、减去优惠 计算总费用
    var freight_able = $("input[name='freight']").val();
    var discount_able = $("input[name='discount']").val();
    total_price = floatAdd(total_price,freight_able);
    total_price = floatSub(total_price,discount_able);
                
   $("#sub-piece").val(total_piece);
   $("#sub-money").val(total_price);
}

function checkFreight(obj,price){
    var freight = $(obj).val();
    if(freight!='' && freight<0){
        layer.alert('运费必须是正整数');
        return false;
    }
    if(freight > price){
        layer.alert('运费不能超过可退额度');
        return false;
    }
        
    //计算取消金额
    total_price = 0;
    $("input[name^='refund_qty']").each(function(){
         //退货数量
        var refund_qty = $(this).val();
        //单价
        var price = $(this).attr('price');
        //未到货
        var weidaohuo_num = $(this).attr('weidaohuo_num');
        //已取消数量
        var cancel_num = $(this).attr('cancel_num');
        total_price += accMul(refund_qty,price);
    });
    total_price = floatSub(total_price,$("input[name='discount']").val());
    var count_price = floatAdd(total_price,freight);
    $("#sub-money").val(count_price);
}

function checkFav(obj,price){
    var discount = $(obj).val();
    if(discount!='' && discount<0){
        layer.alert('优惠必须是正整数');
        return false;
    }
    if(discount > price){
        layer.alert('优惠不能超过可退额度');
        return false;
    }
    //计算取消金额
    total_price = 0;
    $("input[name^='refund_qty']").each(function(){
         //退货数量
        var refund_qty = $(this).val();
        //单价
        var price = $(this).attr('price');
        //未到货
        var weidaohuo_num = $(this).attr('weidaohuo_num');
        //已取消数量
        var cancel_num = $(this).attr('cancel_num');
        total_price += accMul(refund_qty,price);
    });
    total_price = floatAdd(total_price,$("input[name='freight']").val());
    var count_price = floatSub(total_price,discount);
    $("#sub-money").val(count_price);
}

//加
function floatAdd(arg1,arg2){    
     var r1,r2,m;    
     try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
     try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}    
     m=Math.pow(10,Math.max(r1,r2));   
     return ((arg1*m+arg2*m)/m).toFixed(3);    
} 
//减
function floatSub(arg1,arg2){    
    var r1,r2,m,n;    
    try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}    
    try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}    
    m=Math.pow(10,Math.max(r1,r2));    
    //动态控制精度长度    
    n=(r1>=r2)?r1:r2;    
    return ((arg1*m-arg2*m)/m).toFixed(n);    
}
//乘
function accMul(arg1, arg2) {
    var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
    try {
        m += s1.split(".")[1].length;
    }
    catch (e) {
    }
    try {
        m += s2.split(".")[1].length;
    }
    catch (e) {
    }
    return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
}
//获取退款sku数据明细
function getSkuDetail(requisition_number , obj){
    var is_show = $(obj).attr("is_show");
    if(is_show==0){
        $.ajax({
            type: "POST",
            async: false,
            dataType: "json",
            url: "/purchase-order/get-sku-detail",
            data:{requisition_number:requisition_number},
            success: function (json){
                if(json.length>0){
                    var html = "";
                    $.each(json,function(n,value) {
                        html += "SKU: "+value.sku+"   &nbsp;&nbsp;&nbsp;&nbsp;取消数量: "+value.refund_qty+"<br/>";      
                    });
                    $("#"+requisition_number).empty().append(html);
                }
                $(obj).attr("is_show",1);
            }
        });   
    }else{
        $(obj).attr("is_show",0);
        $("#"+requisition_number).empty();
    }
}
JS;
$this->registerJs($js);
?>
