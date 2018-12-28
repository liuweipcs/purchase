<?php
use yii\helpers\Html;
use app\config\Vhelper;
use yii\widgets\ActiveForm;

?>

<div class="purchase-order-form" >
    <?php $form = ActiveForm::begin([
            'id' => 'form-id',
        ]
    ); ?>

    <!--配置补货策略-->
    <div id="step1" style="padding-left: 20px;">

        <div class="row">
            <div class="col-md-2">1.大单配置：</div><div class="col-md-2">保留最大值</div>
            <div class="col-md-2"><?= $form->field($model, 'reserved_max')->input('text',['style'=>'width:120px;height:25px;'])->label(false) ?></div><div class="col-md-1">个</div>
        </div>

        <?php foreach($model->purchaseTacticsDailySales as $key => $value){
            ?>
            <div class="row">
                <div class="col-md-3"><?php if($key == 0){ echo '2.日均销量：';}?></div>
                <div class="col-md-1">比值</div>
                <div class="col-md-2"><?= $form->field($model, 'days_15[]')->input('text',['style'=>'width:80px;height:25px;','value' => $value->day_sales ])->label(false) ?></div>
                <div class="col-md-2">销量平均值：</div>
                <div class="col-md-2"><?= $form->field($model, 'days_15[]')->input('text',['style'=>'width:120px;height:25px;','value' => $value->day_value ])->label(false) ?></div><div class="col-md-1">天</div>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col-md-3">3.销量标准差取值范围：</div>
            <div class="col-md-2"><?= $form->field($model, 'sales_sd_value_range')->input('text',['style'=>'width:120px;height:25px;'])->label(false) ?></div><div class="col-md-1">天</div>
        </div>
        <div class="row">
            <div class="col-md-3">4.权均交期均值取值范围：</div>
            <div class="col-md-2"><?= $form->field($model, 'lead_time_value_range')->input('text',['style'=>'width:120px;height:25px;'])->label(false) ?></div><div class="col-md-1">次</div>
        </div>
        <div class="row">
            <div class="col-md-3">5.提前期标准差取值范围：</div>
            <div class="col-md-2"><?= $form->field($model, 'weight_avg_period_value_range')->input('text',['style'=>'width:120px;height:25px;'])->label(false) ?></div><div class="col-md-1">次</div>
        </div>
    </div>

    <hr>

    <!--创建逻辑-->
    <div id="step2" style="padding-left: 20px;">
        创建逻辑
        <?php
        foreach($model->purchaseTacticsSuggest as $suggest){
            if($suggest->type == 1){ // 定期备货?>
                <div class="row">
                    <div class="col-md-2" style="text-align: right">定期备货：</div>
                    <div class="col-md-2"><span style="float: right">备货周期：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'stockup_days')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->stockup_days.' 天'])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">服务系数：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'service_coefficient')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->service_coefficient])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">增加量：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'incr_days')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->incr_days.' 天'])->label(false) ?></div>
                </div>
                <?php }elseif($suggest->type == 2){ // 定量备货?>
                <div class="row">
                    <div class="col-md-2" style="text-align: right" >定量备货：</div>
                    <div class="col-md-2"><span style="float: right">备货天数：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'stockup_days')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->stockup_days.' 天'])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">服务系数：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'service_coefficient')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->service_coefficient])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">增加量：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'incr_days')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->incr_days .' 天'])->label(false) ?></div>
                </div>
                <?php }else{ // 最大最小备货 ?>
                <div class="row">
                    <div class="col-md-2" style="text-align: right">最大最小备货：</div>
                    <div class="col-md-2"><span style="float: right">最大值：&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'maximum')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->maximum.' 天'])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">最小值：&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'minimum')->input('text',['style'=>'width:80px;height:25px;text-align:right;','value' => $suggest->minimum.' 天'])->label(false) ?></div>
                </div>
                <?php
                }
        }
        ?>

    </div>

    <hr>

    <!--仓库配置-->
    <div id="step3" style="padding-left: 20px;">
        适用仓库：
        <div class="row" style="margin-left: 20px;margin-right: 20px;">
            <div class="col-md-12">
                <?php foreach($model->purchaseTacticsWarehouse  as $ware_value){
                    echo $warehouseList[$ware_value->warehouse_code]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                }?>
                <br/>
                <br/>
            </div>
        </div>
        生效时间：
        <div class="row" style="margin-left: 20px;margin-right: 20px;">
            <div class="col-md-12">
                <label class="control-label">选择时间：</label>
                <span><?php echo substr($model->start_time,0,10) ?></span>
                &nbsp;&nbsp;<b style="color: red">到</b>&nbsp;&nbsp;<span><?php echo substr($model->end_time,0,10) ?></span>
                <br/>
            </div>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php

$js = <<<JS

$(function(){
    $('input[type="text"]').attr('disabled','disabled');
});

JS;
$this->registerJs($js);
?>
