<?php
use yii\widgets\ActiveForm;
?>

<?php ActiveForm::begin(['id' => 'handler-form']); ?>

<div class="my-box">

    <div class="fg">
        <p>SKU：<?= $model->sku ?></p>
        <p>单号：<?= $model->pur_number ?></p>
    </div>

    <div class="fg">

    <textarea rows="3" cols="100" name="gj_note" placeholder="请输入备注" class="form-control"><?= $model->gj_note ?></textarea>

    </div>

    <input type="hidden" name="id" value="<?= $model->id ?>">

    <div class="fg">

    <button type="submit" class="btn btn-success">提交</button>

    </div>

</div>

<?php ActiveForm::end(); ?>

