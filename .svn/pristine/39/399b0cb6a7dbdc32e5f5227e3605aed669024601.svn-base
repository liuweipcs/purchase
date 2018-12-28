<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\services\BaseServices;

?>

<div class="purchase-order-form">
    <div class="container-fluid">
    	<p style="font-size:14px">可审核需求：</p>
    	<?php foreach ($data as $v) : ?>
    	<div style="line-height:30px">
    		[需求单号:<?php echo $v['demand_number']?>] &nbsp;
    		[SKU:<?php echo $v['sku']?>]
    		[采购仓:<?php echo BaseServices::getWarehouseCode($v['purchase_warehouse'])?>]
        </div>
        <?php endforeach; ?>
    </div>
    <div style="padding-top:10px">
    	审核备注: <textarea name="note" id="note" style=""></textarea>
    </div>
    <div class="form-group" style="margin-top:20px">
        <?= Html::button('同意', ['class'=>'btn btn-success','type'=>1]) ?>
        <?= Html::button('驳回', ['class'=>'btn btn-success','type'=>2]) ?>
    </div>
</div>

<script type="text/javascript">
$(function(){
	$(".btn-success").click(function(){
		var type = $(this).attr('type');
		var note = $("#note").val();
		if (type == 2 && note == '') {
			$("#note").focus();
			layer.msg('请填写驳回原因');
			return false;
		}
		$.post('sale-audit', {ids:'<?php echo implode(',',array_column($data,'demand_number'))?>',note:note,type:type}, function (data) {
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