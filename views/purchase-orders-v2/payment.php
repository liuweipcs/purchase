<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
    <style>
        #purchaseHead {
            border: 1px dashed;
            height: 42px;
            line-height: 4;
            width: 100%;
        }
        .headItem {
            float: left;
            margin-left: 10px;
        }
    </style>
    <div class="purchase-order-form">
        <?php $form = ActiveForm::begin(); ?>
        <h4><?=Yii::t('app','采购付款申请')?></h4>
        <input type="hidden" name="page" value="<?=$page?>" />

        <input type="hidden" id="po_code" name="PurchaseOrderPay[po_code]" value="<?=$models->pur_number?>"/>

        <?= $form->field($model, 'create_notice')->hiddenInput(['maxlength' => true])->label('') ?>

        <div id="purchaseHead">
            <!-- 采购单总金额 -->
            <div class="headItem" style="margin-left:5px;">
                <?=Yii::t('app','采购单：')?> <span class="itemValue"><?=$models->pur_number?></span>
            </div>
            <div class="headItem">
                <?=Yii::t('app','供应商：')?><span class="itemValue" style="width:350px;"><?=$models->supplier_name?></span>
            </div>
            <div class="headItem">
                <?=Yii::t('app','币种：')?><span class="itemValue" style="border-bottom:1px solid red;color:red;"><?=$models->currency_code?></span>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>序号</th>
                <th>SKU</th>
                <th>产品名称</th>
                <th>单价</th>
                <th>确认数量</th>
                <th>收货数量</th>
                <th>上架数量</th>
            </tr>
            </thead>
            <tbody>
            <?php if(is_array($models->purchaseOrderItems)){
                $total_price=0;
                foreach($models->purchaseOrderItems as $k=>$v){
                    $total_price +=$v->ctq * $v->price;
                    ?>
                    <tr>
                        <th scope="row"><?=$k+1?></th>
                        <td><?=$v->sku?></td>
                        <td><?=$v->name?></td>
                        <td><?=$v->price?></td>
                        <td><?=$v->ctq?></td>
                        <td><?=$v->rqy?></td>
                        <td><?=$v->cty?></td>

                    </tr>
                <?php } ?>
                <tr class="table-module-b1">
                    <td class="ec-center" colspan="7" style="text-align: left;">
                        <b>申请付款总额：</b>
                        <b style="color: red"><?=sprintf("%.2f", $total_price)?></b>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div class="form-group">
            <input type="hidden" name="paytoken" value="<?=Yii::$app->session->get('paytoken')?>">
            <?= Html::submitButton( '立即申请', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

<?php
$js = <<<JS
   $(function(){
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

	/*
	 * 全额--SKU金额
	 */
	$("#purchase").on("click",function(){
		var arrayObj =getAllParams($(this));
		getAmount("1",arrayObj);
	}).trigger("click");

	/*
	 * 全额--运费
	 */
	$("#freight").on("click",function(){
		var arrayObj =getAllParams($(this));
		getAmount("1",arrayObj);
	});

	$("#prepaidAmount").on("keyup",function(){
		var valueAmount = $(this).val();
		$("#payMoney").html(new Number(valueAmount).toFixed(2));
    	$("input[name='payMoney']").val(new Number(valueAmount).toFixed(2));
	});

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
        url: "/purchase-orders-v2/get-purchase-amount",
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
            	$("#payMoney").html(numberAmount.toFixed(2));
            	$("input[name='PurchaseOrderPay[payMoney]']").val(numberAmount.toFixed(2));
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