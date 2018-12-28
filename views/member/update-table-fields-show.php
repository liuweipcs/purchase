<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

?>

<div class="purchase-order-form">
    <?php $form = ActiveForm::begin(); ?>
    <p>在输入框中填入数字， 为0的列不会显示， 其他按数字大小排序，可以是小数</p>
    <input type="hidden"  class="form-control" name="table" value="<?php echo $data['table']?>">
    <input type="hidden"  class="form-control" name="user_id" value="<?php echo $user_id?>">
    
    <ul style="margin-top:10px">
        <li>
            <span style="display:block;width:150px;float:left">字段</span>
            <span style="display:block;width:100px;float:left">
                排序
            </span>
            <span style="display:block;width:100px;float:left">
                是否显示
            </span>
        </li>
    <?php
    unset($data['table']);
    $num = 0;
    foreach ($data as $key => $val) :
        $num ++;
        ?>
    <li style="clear:both">
        <span style="display:block;width:150px;float:left"><?php echo $val;?></span>
        <span style="display:block;width:100px;float:left">
            <input class="sort" type="text" value="<?php echo isset($fields[$key]['sort'])?$fields[$key]['sort']:$num; ?>" name="fields_sort[<?php echo $key?>]"
                   style="padding-left:2px;width:50px" />
        </span>
        <span style="display:block;width:100px;float:left">
            <input class="sort" type="checkbox" value="1" name="fields_show[<?php echo $key?>]"
                   style="padding-left:2px;width:50px" <?php if(isset($fields[$key]['show']) and $fields[$key]['show'] == 0){ echo '';}else{ echo 'checked';}?> />
        </span>
    </li>
    <?php endforeach; ?>
    </ul>
    <br/><br/>
    <div class="form-group" style="text-align: center">
        <?= Html::submitButton('提交', ['class'=>'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">
$(function(){
	
})
</script>