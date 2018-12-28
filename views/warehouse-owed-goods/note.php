<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-form">

    <?php $form = ActiveForm::begin(); ?>


    <h4 style="margin-left: 18px;"><?=Yii::t('app','添加备注')?></h4>
    <p style="margin-left: 18px;">选择<span style="color: red">是</span>不用填写备注,选择<span style="color: red">否</span>请填写备注吧</p>
    <input type="hidden"  class="form-control" name="WarehouseOwedGoods[sku]" value="<?=$sku?>">
    <input type="hidden"  class="form-control" name="WarehouseOwedGoods[page]" value="<?=$page?>">
    <div class="col-md-2"><span>是否已采购</span></div>
    <label><input name="WarehouseOwedGoods[is_purchase]" type="radio"  class="is_purchase" value="1" checked/>是</label>
    <label><input name="WarehouseOwedGoods[is_purchase]" type="radio"  class="is_purchase" value="2" />否 </label>
    <div class="col-md-12"><?= $form->field($model, 'note')->textarea(['rows'=>3,'cols'=>10,'placeholder'=>'难道你不说点什么吗?']) ?></div>

    <div class="form-group col-md-2">
        <?= Html::submitButton($model->isNewRecord ? '提交' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<<JS


        $("#warehouseowedgoods-note").attr("required",false);
        $(".is_purchase").change(function(){

            var name =$(this).val();

            if(name==1)
            {
                $("#warehouseowedgoods-note").attr("required",false);

            } else {

             $("#warehouseowedgoods-note").attr("required",true);
            }
        });


JS;
$this->registerJs($js);
?>