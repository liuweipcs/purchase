<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="box box-success">
    <div class="box-body">
        <?=$this->render('_search', ['model' => $model,'is_warehouse'=>$is_warehouse, 'params'=>$params]); ?>
    </div>
    <div class="box-footer">
    	<?= Html::button('导出Excel', ['class' => 'btn btn-success export-csv']); ?>
    </div>
</div>



<?php if($is_warehouse == 1): ?>
	<div class="btn-group" style="margin-bottom: 10px;">
	    <span class="btn btn-danger" disabled="disabled">入库明细表</span>
	    <a href="?is_warehouse=2" class="btn btn-default">付款明细</a>
	</div>
	<?php echo $this->render('warehouse-details', ['data'=> $data, 'pagination'=>$pagination]); ?>
<?php else: ?>
	<div class="btn-group" style="margin-bottom: 10px;">
	    <a href="?is_warehouse=1" class="btn btn-default">入库明细表</a>
	    <span class="btn btn-danger" disabled="disabled">付款明细</span>
	</div>
	<?php echo $this->render('payment-details', ['data'=> $data, 'pagination'=>$pagination]); ?>
<?php endif;?>

<?php

$exportCsvUrl = Url::toRoute('export-csv');
$arrival='请选择需要标记到货日期的采购单';

$js = <<<JS
$(function () {
	// 导出
    $('.export-csv').click(function() {
        window.location.href='{$exportCsvUrl}?is_warehouse='+ $is_warehouse;
    });

});
JS;
$this->registerJs($js);
?>