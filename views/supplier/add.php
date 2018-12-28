<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

$this->title = Yii::t('app', '添加 {modelClass}: ', [
    'modelClass' => '到库存表',
]) . $model['stockin_id'];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Stockins'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model['stockin_id'], 'url' => ['view', 'id' => $model['stockin_id']]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

?>
<div class="stockin-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>


    <div class="list-group pull-left col-sm-2">
        <h3 class="fa-hourglass-3">基本信息</h3>
        <div class="list-group-item">
            <?= $form->field($model, 'order_id')->textInput(['readonly'=>'readonly']) ?>

        </div>
    </div>
    <div class="list-group  col-sm-10">
        <h3 class="fa-hourglass-3">入库详情</h3>
        <?php foreach ($model->stockinDetail as $k=>$v){?>
        <div class="col-sm-2 list-group-item">
            <div class="form-group field-stockinDetail-sku_name required">
                <label for="stockinDetail-sku_name">sku名字</label>
                <input type="text" id="stockinDetail-sku_name" class="form-control" name="stockinDetail[<?=$k?>][]" value="<?=$v->sku_name?>" readonly>
                <div class="help-block"></div>
            </div>
            <div class="form-group field-stockinDetail-sku required">
                <label for="stockinDetail-sku">sku</label>
                <input type="text" id="stockinDetail-sku" class="form-control" name="stockinDetail[<?=$k?>][]" value="<?=$v->sku?>" readonly>

                <div class="help-block"></div>
            </div>
            <div class="form-group field-stockinDetail-goods_qty required">
                <label for="stockinDetail-goods_qty">产品数量</label>
                <input type="text" id="stockinDetail-goods_qty" class="form-control" name="stockinDetail[<?=$k?>][]" value="<?=$v->goods_qty?>" readonly>

                <div class="help-block"></div>
            </div>
            <div class="form-group field-stockinDetail-shipment_id required">
                <label for="stockinDetail-shipment_id">shipment_id</label>
                <input type="text" id="stockinDetail-shipment_id" class="form-control" name="stockinDetail[<?=$k?>][]" value="<?=$v->shipment_id?>" readonly>

                <div class="help-block"></div>
            </div>
            <div class="form-group field-stockinDetail-remark required">
                <label for="stockinDetail-remark">储位</label>
                <input type="text" id="stockinDetail-remark" class="form-control" name="stockinDetail[<?=$k?>][]" value="" required="required">
                <div class="help-block"></div>
            </div>
        </div>
        <?php }?>

    </div>


    <div class="form-group">
        <?= Html::submitButton($model['isNewRecord'] ? Yii::t('app', '创建') : Yii::t('app', '添加'), ['class' => $model['isNewRecord'] ? 'btn btn-success' : 'btn btn-primary'])?>
    </div>


    <?php ActiveForm::end(); ?>




</div>
