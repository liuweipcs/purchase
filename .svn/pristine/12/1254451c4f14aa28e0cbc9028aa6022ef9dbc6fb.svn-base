<?php
use yii\helpers\Html;
use app\config\Vhelper;

?>
<div class="row">
    <div class="col-md-2">1.大单配置：</div><div class="col-md-2">保留最大值</div>
    <div class="col-md-2"><?= $form->field($model, 'reserved_max')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input form-control'])->label(false) ?></div><div class="col-md-1">个</div>
</div>



<div  id="daily-sales-list">
    <?php if($model->purchaseTacticsDailySales) {
        foreach ($model->purchaseTacticsDailySales as $key => $value) {
            ?>
            <div class="row">
                <div class="col-md-2"><?php if ($key == 0) { echo '2.日均销量：';} ?></div>
                <div class="col-md-1">比值</div>
                <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text', ['style' => 'width:80px;height:25px;' , 'value' => $value->day_sales,'class' => 'float_int_input form-control'])->label(false) ?></div>
                <div class="col-md-2">销量平均值：</div>
                <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text', ['style' => 'width:120px;height:25px;', 'value' => $value->day_value,'class' => 'int_input form-control'])->label(false) ?></div>
                <div class="col-md-1">天</div>
                <?php if ($key == 0) { ?><div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-plus" id="plus-daily"></span></div>
                <?php } else { ?><div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-minus" id="minus-daily"></span></div><?php } ?>
            </div>
        <?php }
    } else{ ?>
        <div class="row">
            <div class="col-md-2">2.日均销量：</div>
            <div class="col-md-1">比值</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text',['style'=>'width:80px;height:25px;','class' => 'float_int_input form-control'])->label(false) ?></div>
            <div class="col-md-2">销量平均值：</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input form-control'])->label(false) ?></div><div class="col-md-1">天</div>
            <div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-plus" id="plus-daily"></span></div>
        </div>
        <div class="row" >
            <div class="col-md-2"></div>
            <div class="col-md-1">比值</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text',['style'=>'width:80px;height:25px;','class' => 'float_int_input form-control'])->label(false) ?></div>
            <div class="col-md-2">销量平均值：</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input form-control'])->label(false) ?></div><div class="col-md-1">天</div>
            <div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-minus" id="minus-daily"></span></div>
        </div>
    <?php
    } ?>
</div>
<div id="plus-daily-sales" style="display: none;">
    <div class="row" >
        <div class="col-md-2"></div>
        <div class="col-md-1">比值</div>
        <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text',['style'=>'width:80px;height:25px;','class' => 'float_int_input form-control'])->label(false) ?></div>
        <div class="col-md-2">销量平均值：</div>
        <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input form-control'])->label(false) ?></div><div class="col-md-1">天</div>
        <div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-minus" id="minus-daily"></span></div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">3.销量标准差取值范围：</div>
    <div class="col-md-2"><?= $form->field($model, 'sales_sd_value_range')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input form-control'])->label(false) ?></div><div class="col-md-1">天</div>
</div>
<div class="row">
    <div class="col-md-3">4.权均交期均值取值范围：</div>
    <div class="col-md-2"><?= $form->field($model, 'lead_time_value_range')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input form-control'])->label(false) ?></div><div class="col-md-1">次</div>
</div>
<div class="row">
    <div class="col-md-3">5.提前期标准差取值范围：</div>
    <div class="col-md-2"><?= $form->field($model, 'weight_avg_period_value_range')->input('text',['style'=>'width:120px;height:25px;','class' => ' form-control'])->label(false) ?></div><div class="col-md-1">次</div>
</div>

<?php

$js = <<<JS

$("#plus-daily").click(function(){
    var html = $("#plus-daily-sales").html();
    $("#daily-sales-list").append($(html));
});
$(document).on("click","#minus-daily",function(){
    $(this).parent().parent().remove();
});


JS;
$this->registerJs($js);
