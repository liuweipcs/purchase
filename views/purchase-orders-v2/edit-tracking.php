<?php
use yii\widgets\ActiveForm;

$this->title='修改物流';
?>

<div class="purchase-order-form">
    <?php $form = ActiveForm::begin([]) ?>
    <?php if($model)
    {
       foreach($model as $v)
       {
        ?>
        <div class="col-md-4"><?= $form->field($v, 'id[]')->textInput(['value'=>$v->id,'readonly'=>'readonly']) ?></div>
        <div class="col-md-4"><?= $form->field($v, 'cargo_company_id[]')->dropDownList(\app\services\BaseServices::getLogisticsCarrier(),['value'=>!empty($v->cargo_company_id)?$v->cargo_company_id:'']) ?></div>

        <div class="col-md-4"><?= $form->field($v, 'express_no[]')->textInput(['value'=>!empty($v->express_no)?$v->express_no:'','required'=>true]) ?></div>
        <?php }?>
    <?php }?>
    <div class="form-group" style="clear: both">
        <button type="submit" class="btn btn-primary">修改</button>
    </div>
    <?php ActiveForm::end() ?>
</div>