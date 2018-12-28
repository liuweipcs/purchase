<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\SupplierServices;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use kartik\date\DatePicker;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */
?>



<?php $form = ActiveForm::begin(["options" => ["enctype" => "multipart/form-data"]]); ?>

    <?php foreach ($model as $data){?>
        <div style="border-style:hidden hidden dotted hidden ;border-bottom-color: red ">
            <div class="row">
    <div class="col-md-4">
        <label class="control-label">供应商名称</label>
        <input class="form-control" value=<?= $data->supplier ? $data->supplier->supplier_name : ''?> readonly>
    </div>
    <div class="col-md-2">
        <label class="control-label">原账期</label>
        <input class="form-control" value=<?= $data->old_settlement ? SupplierServices::getSettlementMethod($data->old_settlement) : ''?> readonly>
    </div>
    <div class="col-md-2">
        <label class="control-label">现账期</label>
        <input class="form-control" value=<?= $data->new_settlement ? SupplierServices::getSettlementMethod($data->new_settlement) : ''?> readonly>
    </div>
    <div class="col-md-2"><?= $form->field($data, 'means_upload[]')->radioList([0=>'否',1=>'是'],['value'=>$data->means_upload,'name'=>"SupplierSettlementLog[$data->id][means_upload]"])->label('是否提交资料') ?></div>
    <div class="col-md-2"><?= $form->field($data, 'is_exec[]')->radioList([0=>'否',1=>'是'],['value'=>$data->is_exec,'name'=>"SupplierSettlementLog[$data->id][is_exec]"])->label('是否执行') ?></div>
    <div class="col-md-4"><?= $form->field($data, 'pay_time[]')->input('text',['value'=>$data->pay_time,'name'=>"SupplierSettlementLog[$data->id][pay_time]"])->label('付款时间') ?></div>
    <div class="col-md-8"><?= $form->field($data, 'note[]')->input('text',['value'=>$data->note,'name'=>"SupplierSettlementLog[$data->id][note]"])->label('备注') ?></div>
        </div>
        </div>
    <?php }?>

<div class="form-group">
    <?= Html::submitButton( Yii::t('app', '更新'), ['class' => 'btn btn-primary create']) ?>
</div>


<?php ActiveForm::end(); ?>
