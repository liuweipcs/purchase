<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\services\BaseServices;

?>

<style>
.form-table td{border:1px solid #e3e3e3; text-align:center;line-height:24px}
</style>

<div class="purchase-order-form">
    
    <?php foreach ($data as $v) : ?>
    <div class="container-fluid" id="div-<?php echo $v['check_data']['id']?>" style="margin-bottom:10px">
    	<div style="line-height:30px">
    		[需求单号:<?php echo $v['demand_number']?>] &nbsp;
    		[SKU:<?php echo $v['sku']?>]
    		[采购仓:<?php echo BaseServices::getWarehouseCode($v['purchase_warehouse'])?>]
        </div>
    	<table class="form-table" style="width:100%;">
    		<tr>
    			<td>修改字段</td><td>修改前</td><td>修改后</td>
    		</tr>
    		<?php foreach ($v['check_data']['data'] as $field=>$v2) : ?>
    		<tr>
    			<td><?php echo $v2[0]?></td>
    			<td><?php echo $v2[1]?></td>
    			<td><?php echo $v2[2]?></td>
    		</tr>
    		<?php endforeach; ?>
    		<tr>
    			<td colspan="3" style="text-align:left;padding-left:10px">提交备注：<?php echo $v['check_data']['remark']?></td>
    		</tr>
    		<tr>
    			<td colspan="3" style="text-align:left;padding:10px">
    				审核备注:
    				<textarea name="note" id="note_<?php echo $v['check_data']['id']?>" style=""></textarea>
    			</td>
    		</tr>
    	</table>
        <div class="form-group" style="margin-top:10px">
            <?= Html::button('同意', ['class'=>'btn btn-success','type'=>1,'data-id'=>$v['check_data']['id']]) ?>
            <?= Html::button('驳回', ['class'=>'btn btn-success','type'=>2,'data-id'=>$v['check_data']['id']]) ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script type="text/javascript">
$(function(){
	$(".btn-success").click(function(){
		var id = $(this).attr('data-id');
		var type = $(this).attr('type');
		var note = $("#note_"+id).val();
		if (type == 2 && note == '') {
			$("#note_"+id).focus();
			layer.msg('请填写驳回原因');
			return false;
		}
		$.post('info-audit', {id:id,note:note,type:type}, function (data) {
            if (data.code == 1) {
            	layer.msg('操作成功');
            	$("#div-"+id).remove();
            	if ($(".container-fluid").length == 0) {
            		$('#create-modal').modal('hide');
            		window.location.reload();
                }
            } else {
            	layer.msg(data.message);
				return false;
            }
        },'json');
	})
})
</script>