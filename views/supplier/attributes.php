<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

// use kartik\tabs\TabsX;
// use kartik\file\FileInput;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\services\BaseServices;
use yii\web\JsExpression;

$model->id = $id;
?>

<?php $form = ActiveForm::begin([
        'id' => 'form-id',
        'enableAjaxValidation' => true,
        'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<h3 class="">修改供应商属性</h3>
<div class="row">

    <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>

    <?= $form->field($model_buyer, 'type')->radioList([1 => '国内仓', 2 => '海外仓', 3 => 'FBA'])->label('部门') ?>

    <div class="col col-md-6" style="padding-left: 0px;padding-bottom: 30px;">
        <label class="control-label" for="supplierbuyer-buyer">采购员</label>
        <select class="form-control" name="SupplierBuyer[buyer]" id="supplierbuyer-buyer">
            <?php $data = BaseServices::getEveryOne('', 'name'); ?>
            <option value=''>请输入采购员 ...</option>
            <?php if (!empty($data)) { ?>
                <?php foreach ($data as $key => $value) { ?>
                    <option value='<?php echo $key; ?>'><?php echo $value ?></option>
                <?php } ?>
            <?php } ?>
        </select>
    </div>


</div>

<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>


<?php ActiveForm::end(); ?>



