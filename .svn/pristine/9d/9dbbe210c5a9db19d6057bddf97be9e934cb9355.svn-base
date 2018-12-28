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
<h4>采购单: <?php echo $pur_number; ?></h4>
<?php $form = ActiveForm::begin(['id' => 'split-purchase']); ?>
<input type="hidden" name="split_purchase_form" value="1" />
<input type="hidden" name="pur_number" value="<?php echo $pur_number; ?>" />
<div class="my-box">
    <table class="my-table">
        <tr>
        	<th>需求单号</th>
        	<th>状态</th>
        	<th>sku</th>
        	<th>供应商</th>
        	<th>商品名称</th>
        	<th>数量</th>
        	<th>操作</th>
        </tr>
        <?php 
            foreach ($data as $item) : 
        ?>
        <tbody id="tbody1">
        <tr>
        	<td><?php echo $item['demand_number'];?></td>
        	<td><?php echo PurchaseOrderServices::getOverseasOrderStatus($item['demand_status']);?></td>
        	<td><?php echo $item['sku']?></td>
        	<td><?php echo PurchaseOrderServices::getSupplierName($item['supplier_code'])?></td>
        	<td><?php echo $item['product_name']?></td>
        	<td><?php echo $item['purchase_quantity']?></td>
        	<td>
        	<?php if ($item['demand_status'] == 1) : ?>
        	<a onclick="ordersplit(this)" href="javascript:;">拆走</a>
        	<?php endif; ?>
        	</td>
        </tr>
        </tbody>
        <?php endforeach; ?>
        <tr><td colspan="6">新的采购单</td></tr>
        <tr>
        	<th>需求单号</th>
        	<th>状态</th>
        	<th>sku</th>
        	<th>供应商</th>
        	<th>商品名称</th>
        	<th>数量</th>
        	<th>操作</th>
        </tr>
        <tbody id="tbody2"></tbody>
    </table>
</div>
<div class="my-box" style="clear:both">
    <button class="btn btn-success" type="button" id="sub-btn">提交</button>
</div>

<?php ActiveForm::end(); ?>
	
<script type="text/javascript">
function ordersplit(obj) {
    var html = '<tr>';
    var demand_number = '';
    $(obj).parent().parent().find("td").each(function(i,n){
        if (i < 6) {
            html += '<td>'+n.innerHTML+'</td>';
        }
        if (i == 0) {
            demand_number = n.innerHTML;
        }
    })
    html += '<td><a href="javascript:;" onclick="splitback(this)">退回</a>';
    html += '<input type="hidden" name="new_demand_number[]" value="'+demand_number+'" /></td>';
    html += '</tr>';
    $("#tbody2").append(html);
    $(obj).parent().parent().remove();
}

function splitback(obj) {
    var html = '<tr>';
    $(obj).parent().parent().find("td").each(function(i,n){
        if (i < 6) {
            html += '<td>'+n.innerHTML+'</td>';
        }
    })
    html += '<td><a href="javascript:;" onclick="ordersplit(this)">拆走</a>';
    $("#tbody1").append(html);
    $(obj).parent().parent().remove();
}

$(function(){
	$(".btn-success").click(function(){
		$.post('split-purchase', $("#split-purchase").serialize(), function (data) {
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