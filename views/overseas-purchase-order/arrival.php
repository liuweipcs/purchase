<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-form" >

    <?php $form = ActiveForm::begin([
            'id' => 'form-id',
       ]
    ); ?>


    <h4><?=Yii::t('app','标记到货日期')?></h4>
    <input type="hidden" name="PurchaseOrder[order_id]" value="<?=$id?>" />
    <?php $model->arrivaltype=2?>
    <div class="col-md-12"><?= $form->field($model, 'arrivaltype')->radioList(['1'=>'预计','2'=>'今日'],['class'=>'arrivaltype'])->label('到货点') ?></div>
    <div class="col-md-12 date_eta" style="display: none"><?php echo '<label>预计到货时间</label>';
        echo DatePicker::widget([
            'name' => 'PurchaseOrder[date_eta]',
            'options' => ['placeholder' => ''],
            //注意，该方法更新的时候你需要指定value值
            'value' => date('Y-m-d',time()),
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-mm-dd',
                'todayHighlight' => true
            ]
        ]);?></div>
    <div class="col-md-12"><?= $form->field($model, 'arrival_note')->textarea(['rows'=>3,'cols'=>10,'placeholder'=>"请填写具有跟踪意义的说明。如：因供应商发货不及时，采购产品未能按预计到货日期到达。重新确认到货日期为今日到（或者2014-05-03）"])->label('到货备注') ?></div>

    <div class="form-group clearfix">
        <?= Html::submitButton($model->isNewRecord ? '提交' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<<JS

        $(document).on('click', '.arrivaltype', function () {
            var val = $(this).find('input:radio[name="PurchaseOrder[arrivaltype]"]:checked').val();
            if (val ==1){
                $('.date_eta').show();
            } else{

                $('.date_eta').hide();
            }
        });



JS;
$this->registerJs($js);
?>