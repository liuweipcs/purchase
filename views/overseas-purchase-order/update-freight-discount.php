<?php
use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin([
        'id' => 'fm',
    ]
); ?>

<h4>修改订单运费与优惠额</h4>
<div class="my-box" style="border: 1px solid red;">
<p>1.付过订金，优惠不能改。</p>
<p>2.付过尾款，运费不能改。</p>
</div>


<?php if($payFreight): ?>

<div class="fg">
    <label>运费</label>
    原：<input type="text" name="old_freight" value="<?= !empty($freight) ? $freight->freight : 0; ?>" readonly>
    新：<input type="text" name="new_freight" value="<?= !empty($freight) ? $freight->freight : 0; ?>" id="freight">
</div>

<?php endif; ?>

<?php if($payDiscount): ?>

<div class="fg">
    <label>优惠额</label>
    原：<input type="text" name="old_discount" value="<?= !empty($freight) ? $freight->discount : 0; ?>" readonly>
    新：<input type="text" name="new_discount" value="<?= !empty($freight) ? $freight->discount : 0; ?>" id="discount">
</div>

<?php endif; ?>

<input type="hidden" name="payFreight" value="<?= $payFreight ? 1 : 0; ?>" id="ff">
<input type="hidden" name="payDiscount" value="<?= $payDiscount ? 1 : 0; ?>" id="dd">
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
