<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<div class="purchase-order-form">
    <?php $form = ActiveForm::begin(); ?>
    <p>在输入框中填入数字， 为0的列不会显示， 其他按数字大小排序，可以是小数</p>
    <input type="hidden"  class="form-control" name="table" value="<?php echo $data['table']?>">
    
    <ul style="margin-top:10px">
    
    <?php unset($data['table']); $sort = 1; foreach ($fields as $key) : if (!isset($data[$key])) continue;?>
    <li style="clear:both">
    	<span style="display:block;width:150px;float:left">
    	<?php echo $data[$key];?>
    	</span>
    	<span style="display:block;width:150px;float:left">
    	<input class="sort" type="text" value="<?php echo $sort;?>" name="fields[<?php echo $key?>]" style="padding-left:2px;width:50px" />
    	</span>
    </li>
    <?php $sort++; unset($data[$key]); endforeach; ?>
    
    <?php foreach ($data as $key=>$val) : ?>
    <li style="clear:both">
    	<span style="display:block;width:150px;float:left">
    	<?php echo $val;?>
    	</span>
    	<span style="display:block;width:150px;float:left">
    	<input class="sort" type="text" value="0" name="fields[<?php echo $key?>]" style="padding-left:2px;width:50px" />
    	</span>
    </li>
    <?php endforeach; ?>
	</ul>
    <div class="form-group" style="padding-left:30px">
        <?= Html::submitButton('提交', ['class'=>'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">
$(function(){
	
})
</script>