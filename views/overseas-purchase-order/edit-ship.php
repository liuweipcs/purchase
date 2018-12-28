<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
$this->title='修改运费';
$freight = !empty($model->freight) ? $model->freight : 0;
?>

<?php $form = ActiveForm::begin([]) ?>
<div class="my-box">

    <div class="fg">
        <label>运费：</label>
        <input type="text" name="freight" value="<?= $freight ?>">
    </div>

    <div class="fg">
        <label>备注：</label>
        <textarea name="note" cols="100" rows="4"></textarea>
    </div>

    <input type="hidden" name="pur_number" value="<?= $purNumber ?>">

    <div class="fg">
        <label></label>
        <input type="submit" value="修改">
    </div>

</div>

<?php ActiveForm::end() ?>


