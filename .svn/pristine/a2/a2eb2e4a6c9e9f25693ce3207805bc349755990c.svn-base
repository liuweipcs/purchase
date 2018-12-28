<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\config\Vhelper;
use kartik\select2\Select2;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\models\ProductTaxRate;
use app\models\PurchaseOrderItems;
use app\services\PurchaseOrderServices;
use app\models\SupplierPaymentAccount;
use app\models\PurchaseOrderCancelSub;
$this->title = '海外仓采购合同-申请付款';
$this->params['breadcrumbs'][] = '海外仓';
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
<?php $form = ActiveForm::begin(['id' => 'compact-payment']); ?>
<div id="div1">
    <input type="hidden" id="compact_number" value="<?=$compact_number?>">
    <input type="hidden" id="pur_number" value="<?=$model->pur_number?>">
    <?php if ($model->source == 1) : ?>
    <h4><?= $compact_number ?> 申请付款</h4>
    <?= Html::hiddenInput('compact_number',$compact_number) ?>
    <a class="btn btn-info" id="source" href="/purchase-compact/print-compact?id=<?php echo $compact_model->id?>" target="_blank">查看采购订单合同</a>
    <button class="btn btn-success" type="button" id="sub-btn-deposit">按比例请订金</button>
    <?php else : ?>
    <h4><?= $model->pur_number ?> 申请付款</h4>
    <?php endif; ?>
    
    <div class="my-box">
        <table class="my-table">
            <tr>
                <th colspan="6">基本信息</th>
            </tr>
            <tr>
                <td><strong>供应商名称</strong></td>
                <td><?= $model->supplier_name ?></td>
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
                <td><strong>结算方式</strong></td>
                <td><?= !empty($model->account_type) ? SupplierServices::getSettlementMethod($model->account_type) : ''; ?></td>
                <td><strong>结算比例</strong></td>
                <td>
                <?php 
                    $settlement_ratio = $pay_model->settlement_ratio;
                    echo $settlement_ratio;
                    $settlement_ratio = explode('+', $settlement_ratio);
                ?>
                </td>
            </tr>
            <tr>
                <td><strong>支付方式</strong></td>
                <td><?= !empty($model->pay_type) ? SupplierServices::getDefaultPaymentMethod($model->pay_type) : ''; ?></td>
            	<td>运费支付</td>
            	<td><?php echo $pay_model->freight_payer == 1 ? '甲方支付' : '乙方支付';?></td>
            </tr>
        </table>
    </div>
    
    <div class="my-box">
        <table class="my-table">
            <tr>
            	<th>采购单号</th>
            	<th>商品信息</th>
            	<th>单价</th>
            	<th>数量</th>
            	<th>采购金额</th>
            	<th>已请金额</th>
            	<th>运费</th>
            	<th>优惠额</th>
            	<th>请款方式</th>
            	<th>结算比例</th>
            	<th>请款金额</th>
            </tr>
            <?php 
                $totalprice = 0; $sku_total = 0; 

                //每个需求取消的数量
                $cancelCtq = [];
                $orderCancelSubInfo = PurchaseOrderCancelSub::find()
                    ->alias('pocs')
                    ->joinWith('purchaseOrderCancel')
                    ->where(['in', 'pocs.pur_number', $pur_numbers])
                    ->andWhere(['audit_status'=>2])
                    ->asArray()->all();
                if (!empty($orderCancelSubInfo)) {
                   foreach ($orderCancelSubInfo as $sk => $sv) {

                        if ( !empty($cancelCtq[$sv['demand_number']]) ) {
                            $cancelCtq[$sv['demand_number']] += $sv['cancel_ctq'];
                        } else {
                            $cancelCtq[$sv['demand_number']] = $sv['cancel_ctq'];
                        }
                   }
                }



                foreach ($items as $item) : 
                    if (empty($item->price)) 
                    {
                        continue;
                    }
                    $price = $item->price;
                    //产品总额-取消的金额
                    $cancel_ctq = !empty($cancelCtq[$item->demand_number])?$cancelCtq[$item->demand_number]:0;
                    $totalprice += $price*($item->purchase_quantity-$cancel_ctq);

                    $sku_total += $item->purchase_quantity;

                    $sku_total_price = $price*$item->purchase_quantity;// SKU总金额
                    $settlement_ratio_price = PurchaseOrderServices::divideAmountByPercent($settlement_ratio,$sku_total_price) ;// 按比例分割后的金额
            ?>
            <input type="hidden" name="id[]" value="<?php echo $item->demand_number;?>" />
            <input type="hidden" name="price[<?php echo $item->demand_number;?>]" value="<?php echo $price;?>" />
            <tr class="item" id="item-<?php echo $item->demand_number;?>"
            	data-demand-number="<?php echo $item->demand_number;?>" 
            	data-quantity="<?php echo $item->purchase_quantity?>"
            	data-item-totalprice="<?php echo $item->purchase_quantity*$price?>"
            	data-has-prce="<?php echo isset($has_amount[$item->demand_number]) ? $has_amount[$item->demand_number] : 0?>">
            	<td><?php echo $demand_maps[$item->demand_number];?></td>
            	<td>
            		SKU:<?php echo $item->sku?><br>
            		<?php echo $item->product_name?>
            	</td>
            	<td><?php echo $price; ?></td>
            	<td>
            	订单数量 : <?php echo $item->purchase_quantity?><br>
                            取消数量 : <?php echo $item->cancel_cty;?><br>
                            收货数量 : <?php echo $item->rqy?><br>
                            未到货数量 : <?php echo $item->purchase_quantity - $item->cty - $item->cancel_cty;?><br>
                            入库数量 : <?php echo $item->cty?>
            	</td>
            	<td><?php echo $price*$item->purchase_quantity;?></td>
            	<td>
            		<?php echo isset($has_amount[$item->demand_number]) ? $has_amount[$item->demand_number] : 0?>
            	</td>
            	<td>
            		<input id="freight-<?php echo $item->demand_number;?>" type="text" name="freight[<?php echo $item->demand_number;?>]" value="0" style="width:60px" readonly />
            	</td>
            	<td>
            		<input id="discount-<?php echo $item->demand_number;?>" type="text" name="discount[<?php echo $item->demand_number;?>]" value="0" style="width:60px" readonly />
            	</td>
            	<td>
            		<select class="price_type" id="price-type-<?php echo $item->demand_number;?>" data-demand-number="<?php echo $item->demand_number;?>" name="price_type[<?php echo $item->demand_number;?>]">
            			<option value="1">比例请款</option>
            			<option value="2">手动请款</option>
            		</select>
            	</td>
            	<td>
                	<select class="pay_ratio" id="pay-ratio-<?php echo $item->demand_number;?>" data-demand-number="<?php echo $item->demand_number;?>" name="pay_ratio[<?php echo $item->demand_number;?>]">
            		<option value="0">请选择</option>
                        <?php foreach($settlement_ratio_price as $ratio => $ratio_price){?>
                            <option value="<?php echo $ratio;?>" data-ratio-price="<?php echo $ratio_price;?>"><?php echo $ratio.'%/'.$ratio_price;?></option>
                        <?php } ?>
        			</select>
            	</td>
            	<td>
            		<input class="pay_amount" id="pay-amount-<?php echo $item->demand_number;?>" type="text" name="pay_amount[<?php echo $item->demand_number;?>]" value="0" style="width:60px" readonly />
            	</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="my-box">
    	<div class="col-md-1">
            <label>产品总额</label>
            <input type="text" id="profucttotalprice" class="form-control" value="<?php echo $totalprice;?>" readonly>
        </div>
        <div class="col-md-1">
            <label>已申请金额</label>
            <input type="text" class="form-control" value="<?php echo $pay_amount_total;?>" readonly>
        </div>
        <div class="col-md-1">
            <label>取消金额</label>
            <input type="text" class="form-control cancel_total_price" value="<?php echo $cancel_total_price;?>" readonly>
        </div>
        <div class="col-md-1">
            <label>待申请金额</label>
            <input type="text" id="remaining" class="form-control" value="<?php echo $totalprice - $pay_amount_total;?>" readonly>
        </div>
        <div class="col-md-1">
            <label>总运费</label>
            <input type="number" id="freight" name="order_freight" class="form-control" value="0" <?php if ($pay_model->freight_payer == 2) {echo 'readonly';}?> >
        </div>
        <div class="col-md-1">
            <label>总优惠</label>
            <input type="number" id="discount" name="order_discount" class="form-control" value="0">
        </div>
        <div class="col-md-1">
            <label>请款总金额</label>
            <input type="number" id="pay_price" name="pay_price" class="form-control" value="0" readonly>
        </div>
        <div class="col-md-2">
            <label>账号</label>
            <?php echo Html::dropDownList("purchase_account", '', BaseServices::getAlibaba(), ['class'=>'form-control','prompt'=>'请选择']);?>
        </div>
        <div class="col-md-2">
            <label>拍单号</label>
            <input type="text" name="pai_number" class="form-control" value="">
        </div>
    </div>
    <div class="my-box" style="clear:both">
    	备注:<textarea name="create_notice" rows="3" class="form-control" style="width:600px"></textarea>
    </div>
    <div class="my-box" style="clear:both">
        <button class="btn btn-success" type="button" id="sub-btn"><?php echo $model->source == 1 ? '去填写付款申请书' : '提交审核';?></button>
    </div>
</div>
<?php if ($model->source == 1) : ?>
<?php
$payAccountType = $model->is_drawback ==2 ? 1 :2;//退税对公不退税对私

$supplierAccount = SupplierPaymentAccount::find()
    ->where(['supplier_code' => $model->supplier_code,'status'=>1])
    ->andWhere(['account_type'=>$payAccountType])
    ->one();
$account = $account_name = $payment_platform_branch = '';
if($supplierAccount) {
    $account = $supplierAccount->account;
    $account_name = $supplierAccount->account_name;
    $payment_platform_branch = $supplierAccount->payment_platform_branch;
}
?>
<div id="div2" style="display:none">
	<div style="width: 756px;background-color: #fff;">
        <span style="display: none" id="pay-info" account="<?=$account?>" name="<?=$account_name?>" branch="<?=$payment_platform_branch?>" ></span>
        <table class="table table-bordered" style="margin-bottom: 0;">
            <tr>
                <th colspan="8" style="text-align: center;font-weight: bold;">付款申请书</th>
            </tr>
            <tr>
                <th colspan="2" id="company_name"></th>
                <th colspan="4"><?= date('Y年m月d日', time()) ?></th>
                <th colspan="2">合同号：<?= $compact_number ?></th>
            </tr>
            <tr>
                <td>收款单位</td>
                <td id="account_name" supplier_name="<?=$model->supplier_name?>">

                </td>
                <input type="hidden" name="payee_account_name" value="" />
                <td colspan="6">付款原因</td>
            </tr>
            <tr>
                <td>账号</td>
                <td id='account'></td>
                <input type="hidden" name="account" value="" />
                <td rowspan="5" colspan="6" style="width: 50%;vertical-align: top;">
                <textarea name="payment_reason" class="form-control" rows="6"><?= implode(' ',$pur_numbers) ?></textarea>
                </td>
            </tr>
            <tr>
                <td>开户行</td>
                <td id="payment_platform_branch"></td>
                <input type="hidden" name="payment_platform_branch" value="" />
            </tr>
            <tr>
                <td>金额</td>
                <td id="payment-amount2"></td>
            </tr>
            <tr>
                <td>总金额</td>
                <td id="payment-amount"></td>
            </tr>
            <tr>
                <td>审批</td>
                <td>总经办</td>
            </tr>
        </table>
        <table class="table table-bordered">
            <tr>
                <td>财务总监</td>
                <td style="width: 113px;"></td>
                <td>记账</td>
                <td style="width: 113px;"></td>
                <td>采购经理</td>
                <td style="width: 113px;"></td>
                <td>制单</td>
                <td style="width: 113px;"></td>
            </tr>
        </table>
    </div>
    <div class="my-box">
        <button type="button" class="btn btn-success" id="apply-form" >提交审核</button> &nbsp;
        <a href="javascript:;" onclick='javascript:$("#div1").show();$("#div2").hide();'>返回上一步</a>
    </div>
</div>
<?php endif; ?>
<?php ActiveForm::end(); ?>
<?php
$damount = round(intval($settlement_ratio[0])*$totalprice/100,3);
$ratio = intval($settlement_ratio[0]);
$js = <<<JS
var sku_total = $sku_total;
var source = $model->source;
var deposit = $damount;
var settlement_ratio = $ratio;
var is_drawback = $model->is_drawback;
$(function(){
    //点击后自动分摊订金金额 deposit
    $('#sub-btn-deposit').click(function() {
         var pay_price = 0;
         var items =  $(".item").length -1;
        $(".item").each(function(i){
             var demand_number = $(this).attr("data-demand-number");
            //判断是不是最后一个元素
            if(items == i){
                $("#price-type-"+demand_number).val(1);
                $("#pay-ratio-"+demand_number).val(settlement_ratio);
                var real_item_totalprice = deposit - pay_price;
               if($("#source").length>0){
                    real_item_totalprice = changeTwoDecimal_f(real_item_totalprice,1000);
                }else{
                    real_item_totalprice = changeTwoDecimal_f(real_item_totalprice,100);
                }
                $("#pay-amount-"+demand_number).val(real_item_totalprice);
            }else{
                $("#price-type-"+demand_number).val(1);
                $("#pay-ratio-"+demand_number).val(settlement_ratio);
                console.log(settlement_ratio);
                var item_totalprice = parseFloat($("#item-"+demand_number).attr('data-item-totalprice'));
                var real_item_totalprice = item_totalprice*settlement_ratio/100;
                if($("#source").length>0){
                    real_item_totalprice = changeTwoDecimal_f(real_item_totalprice,1000);
                }else{
                    real_item_totalprice = changeTwoDecimal_f(real_item_totalprice,100);
                }
                $("#pay-amount-"+demand_number).val(real_item_totalprice);
            }
            pay_price += parseFloat(real_item_totalprice);
        });
        pay_price += parseFloat($("#freight").val());
        pay_price -= parseFloat($("#discount").val());
        if (pay_price <= 0) {
            $("#discount").val(0);
            $("#discount").change();
            $("#discount").select();
            layer.msg("优惠金额不能超过总费用");
            return false;
        }
        if($("#source").length>0){
            pay_price = changeTwoDecimal_f(pay_price,1000);
        }else{
            pay_price = changeTwoDecimal_f(pay_price,100);
        }
        if($("#source").length>0){
            deposit = changeTwoDecimal_f(deposit,1000);
        }else{
            deposit = changeTwoDecimal_f(deposit,100);
        }
        $("#pay_price").val(deposit);
    });
   
    $('.price_type').change(function() {
        var demand_number = $(this).attr("data-demand-number");
        if ($(this).val() == "1") {
            $("#pay-amount-"+demand_number).attr("readonly", true);
            $("#pay-ratio-"+demand_number).attr("disabled", false);
        } else {
            $("#pay-amount-"+demand_number).attr("readonly", false);
            $("#pay-ratio-"+demand_number).val(0);
            $("#pay-ratio-"+demand_number).attr("disabled", true);
            $("#pay-amount-"+demand_number).val(0);
            $("#pay-amount-"+demand_number).select();
        }
        totalprice();
    });

    $('.pay_ratio').change(function() {
        totalprice();
    });

    $("#freight").change(function(){
        var freight = parseFloat($(this).val());
        if (isNaN(freight) || freight < 0) {
            $(this).val(0);
            freight = 0;
        }
        freight =freight.toFixed(2);
        var totalFreight =  Math.round(freight*100);
        var remain = totalFreight;
        $(this).val(freight);
        var itemNumber = $(".item").length;
        $(".item").each(function(i){
            var demand_number = $(this).attr("data-demand-number");
            var item_freight = (parseInt($(this).attr("data-quantity"))/sku_total)*totalFreight;
            item_freight = Math.round(item_freight);
            var displayFreight = 0.00;
            if (i == itemNumber - 1)
                displayFreight = remain;
            else 
               displayFreight = item_freight;
            remain -= item_freight;
            $("#freight-"+demand_number).val(displayFreight / 100);
        })
        totalprice();
    })

    $("#discount").change(function(){
        var discount = parseFloat($(this).val());
        if (isNaN(discount) || discount < 0) {
            $(this).val(0);
            discount = 0;
        }
        
        discount =discount.toFixed(2);
        var totalDiscount =  Math.round(discount*100);
        var remain = totalDiscount;
        $(this).val(discount);
        var itemNumber = $(".item").length;
        $(".item").each(function(i){
            var demand_number = $(this).attr("data-demand-number");
            var item_discount = (parseInt($(this).attr("data-quantity"))/sku_total)*totalDiscount;
            item_discount = Math.round(item_discount);
            var displayDiscount = 0.00;
            if (i == itemNumber - 1)
                displayDiscount = remain;
            else 
               displayDiscount = item_discount;
            remain -= item_discount;
            $("#discount-"+demand_number).val(displayDiscount / 100);
        })
       
        totalprice();
        
    })
    
   
    $(".pay_amount").blur(function(){
        var value = parseFloat($(this).val());
        if (isNaN(discount) || discount < 0) {
            $(this).val(0);
            discount = 0;
        }
        $(this).val(value.toFixed(2));
        totalprice();
    })
    var getPayName=function() {
        var loading = layer.load(6 , {shade : [0.5 , '#BFE0FA']});
        $.ajax({
            url:'get-pay-account',
            data:$('#compact-payment').serialize(),
            type:'post',
            success:function(data) {
                var response = $.parseJSON(data);
                var supplier_name = $('#account_name').attr('supplier_name');
                $('#account_name').text(supplier_name+response.account_name);
                $('#company_name').text(response.name);
                $('[name="payee_account_name"]').val(supplier_name+response.account_name);
                $('#account').text(response.account);
                $('[name="account"]').val(response.account);
                $('#payment_platform_branch').text(response.payment_platform_branch);
                $('[name="payment_platform_branch"]').val(response.payment_platform_branch);
                layer.close(loading);
                if(response.account==''){
                    layer.msg('付款信息为空');
                }
            }
        });
    }

    $('#sub-btn').click(function() {
        var pay_price = parseFloat( $("#pay_price").val(), 3);
        var cancel_total_price = $('.cancel_total_price').val();
        var pay_price = $("#pay_price").val();
        var freight = parseFloat( $("#freight").val(), 3);
        var compact_number = $("#compact_number").val();
        var pur_number = $("#pur_number").val();
        
        if (pay_price==freight) {
            payment_amount = pay_price;
        } else {
            payment_amount = pay_price-cancel_total_price;
        }

        if($("#source").length>0){
            pay_price = changeTwoDecimal_f(pay_price,1000);
        }else{
            pay_price = changeTwoDecimal_f(pay_price,100);
        }
        
        if(pay_price == 0) {
            layer.msg('请款金额不能为0');
            return false;
        }

        // 验证是否单独请运费
        if(source == 1){
            if(is_drawback == 1 && payment_amount == freight){
                layer.msg('非退税合同：不能单独请运费');
                return false;
            }else if(is_drawback == 2 && freight > 0 && payment_amount != freight){// 退税合同只能单独请运费
                layer.msg('退税合同：只能单独请运费');
                return false;
            }
        }else{
            if(payment_amount == freight){
                layer.msg('网采单：不能单独请运费');
                return false;
            }
        }
        
        var flag = 1; 
        if(freight > 0){// 验证是否请过运费，是否能再请运费
            if(source == 1 && is_drawback == 2){// 退税合同（提交数据时验证）
                $.ajax({
                    url:'verify-freight',
                    data:{compact_number:compact_number,pur_number:pur_number},
                    type:'post',
                    async:false,
                    success:function(data) {
                        var response = $.parseJSON(data);
                        var response = $.parseJSON(data);
                        if(response.code != 'no'){
                            flag = 0;
                            layer.msg(response.message);
                            return false;
                        }
                    }
                });
            }
        }
        
        if(flag == 1){
            if (source == 1) {
                $("#payment-amount").html(payment_amount);
                $.post('get-rmb', {price:payment_amount}, function (data) {
                    $("#payment-amount2").html(data);
                });
                getPayName();
                $("#div1").hide();
                $("#div2").show();
            } else {
                $('#compact-payment').submit();
            }
        }
    });
    
    $('#apply-form').on('click',function() {
        var account = $('#pay-info').attr('account');
        var account_name = $('#pay-info').attr('name');
        var payment_platform_branch = $('#pay-info').attr('branch');
        console.log(account);
        console.log(account_name);
        console.log(payment_platform_branch);
        var alertMessage='';
      if(account==''){
          alertMessage = alertMessage+'付款申请书中账户不能为空！<br/>';
      }
      if(account_name==''){
          alertMessage = alertMessage+'付款申请书中账户名不能为空！<br/>';
      }
      if(payment_platform_branch==''){
          alertMessage = alertMessage+'付款申请书中银行全称不能为空！';
      }
      if(alertMessage!=''){
          layer.alert(alertMessage);
          return false;
      }else {
          $('#compact-payment').submit();
      }
    });
    
});

function totalprice() {
    var pay_price = 0;
    $(".item").each(function(){
        var demand_number = $(this).attr("data-demand-number");
        if ($("#price-type-"+demand_number).val() == "1") {
            // var pay_ratio = parseInt($("#pay-ratio-"+demand_number).val());
            // var item_totalprice = parseFloat($("#item-"+demand_number).attr('data-item-totalprice'));
            // var real_item_totalprice = item_totalprice*pay_ratio/100;
            // if($("#source").length>0){
            //     real_item_totalprice = changeTwoDecimal_f(real_item_totalprice,1000);
            // }else{
            //     real_item_totalprice = changeTwoDecimal_f(real_item_totalprice,100);
            // }
           
            var real_item_totalprice = parseFloat($("#pay-ratio-"+demand_number).find("option:selected").attr('data-ratio-price'));
            if (isNaN(real_item_totalprice)) {
                real_item_totalprice = 0;
            }
            $("#pay-amount-"+demand_number).val(real_item_totalprice);

        } else {
            var real_item_totalprice = parseFloat($("#pay-amount-"+demand_number).val());
            if (isNaN(real_item_totalprice)) {
                real_item_totalprice = 0;
                $("#pay-amount-"+demand_number).val(0.00);
            }
        }
        pay_price += parseFloat(real_item_totalprice);
    })
    pay_price += parseFloat($("#freight").val());
    pay_price -= parseFloat($("#discount").val());
    if (pay_price <= 0) {
        $("#discount").val(0);
        $("#discount").change();
        $("#discount").select();
        layer.msg("优惠金额不能超过总费用");
        return false;
    }
    if($("#source").length>0){
        pay_price = changeTwoDecimal_f(pay_price,1000);
    }else{
        pay_price = changeTwoDecimal_f(pay_price,100);
    }
    $("#pay_price").val(pay_price);
}

function changeTwoDecimal_f(x,n)
{
    var f_x = parseFloat(x);
    if (isNaN(f_x)) {
        alert('function:changeTwoDecimal->parameter error');
        return false;
    }
    f_x = Math.round(f_x*n)/n;
    var s_x = f_x.toString();
    var pos_decimal = s_x.indexOf('.');
    if (pos_decimal < 0) {
        pos_decimal = s_x.length;
        s_x += '.';
    }
    var nub = 2;
    if($("#source").length>0){
        nub = 3;
    }
    while (s_x.length <= pos_decimal + nub) {
        s_x += '0';
    }
    return s_x;
}


JS;
$this->registerJs($js);
?>
