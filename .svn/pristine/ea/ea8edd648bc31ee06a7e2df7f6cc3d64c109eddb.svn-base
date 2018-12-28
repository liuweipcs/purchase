<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\select2\Select2;
use app\models\OverseasPurchaseOrderSearch;

?>
<style></style>
<form id="invoice-form">
<div class="purchase-order-form">
    <div class="container-fluid">
	<table class="my-table">
		<tr>
			<th>需求单号</th>
			<th>SKU</th>
			<th>采购数量</th>
			<th>已开票数量</th>
			<th>开票数量</th>
			<th>发票编号</th>
		</tr>
    	<?php foreach ($data as $v) : ?>
    	<tr>
    		<td><?php echo $v['demand_number']?></td>
    		<td><?php echo $v['sku']?></td>
    		<td><?php echo $v['purchase_quantity']?></td>
    		<td><?php echo $has_qty = OverseasPurchaseOrderSearch::getInvoiceQty($v['demand_number'], 'qty')?></td>
    		<td><input type="number" name="qty[<?php echo $v['demand_number']?>]" value="0" max="<?php echo $v['purchase_quantity'] - $has_qty;?>" /></td>
    		<td><input type="text" name="invoice_code[<?php echo $v['demand_number']?>]" value="" required /></td>
    	</tr>
        <?php endforeach; ?>
    </table>
    </div>
    <div class="form-group" style="margin-top:20px;padding-left:10px">
        <?= Html::button('提交', ['class'=>'btn btn-success']) ?>
    </div>
</div>
</form>

<script type="text/javascript">
$(function(){
	$(".btn-success").click(function(){
		$.post('invoice', $("#invoice-form").serialize(), function (data) {
            if (data.code == 1) {
            	layer.msg('操作成功');
        		$('#create-modal').modal('hide');
        		window.location.reload();
            } else {
            	layer.msg(data.message);
				return false;
            }
        },'json');
	})
})
</script>