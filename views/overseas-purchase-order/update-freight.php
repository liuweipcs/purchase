<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin([
        'id' => 'fm',
    ]
); ?>

<h4>修改订单运费</h4>

<div class="fg">
    <label>运费</label>
    原：<input type="text" name="old_freight" value="<?= !empty($freight) ? $freight->freight : 0; ?>" readonly>
    新：<input type="text" name="new_freight" value="<?= !empty($freight) ? $freight->freight : 0; ?>" id="freight">
</div>

<input type="hidden" name="cpn" value="<?= $cpn ?>">
<input type="hidden" name="opn" value="<?= $opn ?>">

<div class="fg">
    <label>备注</label>
    <textarea name="note" rows="3" cols="100"></textarea>
</div>
<div class="fg">
    <label></label>
    <button type="submit" id="submit">提交</button>
</div>

<?php ActiveForm::end(); ?>
