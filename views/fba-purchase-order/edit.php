<?php
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
?>
<?php ActiveForm::begin(['id'=>'edit-form']);?>
<h5>编辑采购单</h5>
<div class="container-fluid">
    <?php echo $this->render('_public',['orderInfo' => $orderInfo, 'payInfo' => $payInfo]); ?>
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
                        <td colspan="4">
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
                <table class="table table-bordered table-condensed" style="background-color: #ccc;">
                    <tr>
                        <th>退款次数</th>
                        <th>已退金额</th>
                        <th>可退金额</th>
                    </tr>
                    <tr>
                        <td><em><?= $refundInfo['refund_num'] ?></em>（次）</td>
                        <td><em><?= $refundInfo['has_refund_money'].' '.$refundInfo['currency'] ?> </em></td>
                        <td><em><?= $payInfo['hasPaidMoney'] - $refundInfo['has_refund_money'].' '.$refundInfo['currency'] ?></em></td>
                    </tr>
                    <tr>
                        <th>退款单状态</th>
                        <td colspan="4">
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

    <div class="row">
        <div class="col-md-12">

            <?php if($payInfo['hasPaidMoney'] > 0): ?>

                <div class="form-group">
                    <label>退款方式</label><br/>
                    <input type="radio" name="refund_status" value="3" checked> 部分退款
                    <input type="radio" name="refund_status" value="4" data-money="<?= $payInfo['hasPaidMoney'] ?>"> 全额退款
                </div>

                <div class="form-group">
                    <label>请输入金额</label>
                    <input type="text" class="form-control" id="sub-money" name="money" value="<?= $payInfo['hasPaidMoney'] ?>">
                </div>

            <?php endif; ?>

            <div class="form-group">
                <label>请填写备注</label>
                <textarea class="form-control" id="confirm_note" rows="3" name="confirm_note" placeholder="请填写备注"></textarea>
            </div>

            <p style="margin:0;color:red;"><span class="glyphicon glyphicon-volume-up"></span>  财务没有付款的订单，不可以申请退款</p>
            <p style="margin:0;color:red;"><span class="glyphicon glyphicon-volume-up"></span>  退款金额最大值不能超过可退金额</p>

        </div>
    </div>

    <div class="row">
        <div class="col-md-12">

            <?php if($payInfo['hasPaidMoney'] > 0): ?>

                <span id="refund" class="btn btn-primary">申请退款</span>

            <?php else: ?>

                <input type="hidden" name="refund_status" value="10">

                <span id="cancel" class="btn btn-danger">作废订单</span>

            <?php endif; ?>

        </div>
    </div>


    <input type="hidden" name="order_real_money" value="<?= $orderInfo['order_real_money'] ?>" id="order_real_money">
    <input type="hidden" name="can_refund_money" value="<?= $payInfo['hasPaidMoney'] - $refundInfo['has_refund_money'] ?>" id="can_refund_money">
    <input type="hidden" name="pur_number" value="<?= $orderInfo['pur_number'] ?>" id="pur_number">


</div>
<?php
ActiveForm::end();
?>

<?php
$js = <<<JS
$(function() {
    
    var t = 3;
    
    // 退款方式切换
    $('input[name="refund_status"]').click(function() {
        
        if($(this).prop('checked')) {
            t = $(this).val();
            if(t == 3) { // 部分
                $('#sub-money').val('');
                $('#sub-money').attr('readonly', false);
            } else if(t == 4) { // 全额
                $('#sub-money').val($(this).attr('data-money'));
                $('#sub-money').attr('readonly', true);
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
       var pm = $('#can_refund_money').val(); // 最大可退金额
       var om = $('#order_real_money').val(); // 订单实际金额
       if(!testRegex(m)) {
           layer.alert('金额只能包含数字');
           return false;
       }
       if(parseFloat(m) <= 0) {
           layer.alert('退款金额有问题');
           return false;
       }
       
       if(t == 3) {
           if(parseFloat(m) > parseFloat(pm)) {
               layer.alert('退款金额超限');
               return false;
           }
       } else if(t == 4) {
           if(parseFloat(m) > parseFloat(om)) {
               layer.alert('退款金额超限');
               return false;
           }
       }
       if($.trim($('#confirm_note').val()) == '') {
           layer.alert('备注不能为空');
           return false;
       }
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




JS;
$this->registerJs($js);
?>
