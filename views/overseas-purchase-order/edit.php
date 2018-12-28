<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\WarehouseResults;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
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
        /* width: 180px; */
        margin-left: 10px;
    }
</style>
<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(
    ); ?>
    <h4><?=Yii::t('app','申请退款')?></h4>
    <table class="table table-bordered">

        <thead>
        <tr>
            <th>id</th>
            <th>采购单号</th>
            <th>产品sku</th>
            <th>采购数量</th>
            <th>单价</th>
            <th>到货数量</th>
            <th>不良品数量</th>
            <th>收货人</th>
            <th>收货时间</th>
        </tr>
        </thead>
        <tbody>
        <?php

        //不良
        $nogoods=0;
        //采购单价
        $purchase_price=0;
        if($PurchaseOrderItems){
            foreach($PurchaseOrderItems as $v){
                $results = WarehouseResults::getResults($v->pur_number,$v->sku,'instock_user,instock_date,nogoods');
                $purchase_price +=$v->ctq * $v->price;

                $nogoods +=!empty($results->nogoods)?$results->nogoods * $v->price:'0';

                ?>
                <tr>
                    <td><?=$v->id?></td>
                    <td><?=$v->pur_number?></td>
                    <td><?=$v->sku?></td>
                    <td><?=$v->ctq?></td>
                    <td><?=$v->price?></td>
                    <td><?=$v->rqy?></td>
                    <td><?=!empty($results->nogoods)?$results->nogoods:''?></td>
                    <td><?=!empty($results->instock_user)?$results->instock_user:''?></td>
                    <td><?=!empty($results->instock_date)?$results->instock_date:''?></td>
                </tr>

            <?php }?>
            <tr class="table-module-b1">
                <td class="ec-center" colspan="6" style="text-align: left;"><b>汇总：</b></td>

                <td ><b><?=$nogoods?></b></td>
                <td ><b></b></td>
                <td ><b>总应收：<?=number_format($purchase_price,3).'&nbsp;&nbsp;'.$model['currency_code']?></b></td>
            </tr>
        <?php }?>

        </tbody>

    </table>
    <span style="color: red">1:选择全额退款将要经理审核。2:部分退款将进入财务退款模块。3:没申请付款的你可以作废,在途将被减掉</span><br/>
    <br/>
    <span ><?=$PurchaseNote?></span>
    <table class="table table-hover">
        <input type="hidden" name="PurchaseOrder[pur_number]"   value="<?=$pur_number?>" />
        <tbody>
        <tr>
            <?php
            $arr =['5','6'];
            if(in_array($model->pay_status,$arr)){
                ?>
                <td >
                    <label><input type="radio" name="PurchaseOrder[refund_status]"   checked="checked" value="4">全额退款</label>
                    <label><input type="text"  name="PurchaseOrder[money1]" placeholder="请输入退款金额+运费"></label>
                </td>
                <td >
                    <label><input type="radio" name="PurchaseOrder[refund_status]"  value="3">部分退款</label>
                    <label><input type="text"  name="PurchaseOrder[money2]" placeholder="请输入退款金额+运费"></label>
                </td>
            <?php } else { ?>
                <td >
                    <label><input type="radio" name="PurchaseOrder[refund_status]"   checked="checked" value="10">作废</label>
                </td>
            <?php }?>
        </tr>
        </tbody>

    </table>
    <?= $form->field($model, 'confirm_note')->textarea(['rows'=>3,'cols'=>10,'required'=>true,'placeholder'=>'这个需要填写原因的'])->label('备注') ?>

    <div class="form-group">
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
		var arrayObj =getAllParams($(this))
		getAmount("1",arrayObj);
	}).trigger("click");

	/*
	 * 全额--运费
	 */
	$("#freight").on("click",function(){
		var arrayObj =getAllParams($(this))
		getAmount("1",arrayObj);
	});

	$("#prepaidAmount").on("keyup",function(){
		var valueAmount = $(this).val();
		$("#payMoney").html(new Number(valueAmount).toFixed(3));
    	$("input[name='payMoney']").val(new Number(valueAmount).toFixed(3));
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
