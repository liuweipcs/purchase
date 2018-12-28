<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrder */
/* @var $form yii\widgets\ActiveForm */
$this->title='修改物流';

$shipList = \app\services\BaseServices::getLogisticsCarrier();

?>

<h4>编辑跟踪记录</h4>

<div class="my-box">

    <?php $form = ActiveForm::begin([]) ?>


    <?php if(empty($model)): ?>

        <div class="fg">
            <label>订单号：</label>
            <input type="text" name="pur_number" value="<?= $pur_number ?>" readonly>
        </div>
        <div class="fg">
            <label>快递公司：</label>
            <select name="cargo_company_id" style="width: 200px">
                <?php foreach($shipList as $v): ?>
                    <option value="<?= $v ?>"><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fg">
            <label>快递号：</label>
            <input type="text" name="express_no" value="" style="width: 200px">
        </div>

    <?php else: ?>

        <?php foreach($model as $v): ?>

    <div class="my-box" style="border: 1px solid red;">

        <div class="fg">
            <label>ID</label>
            <input type="text" name="PurchaseOrderShip[id][]" value="<?= $v->id ?>" readonly>
        </div>

        <div class="fg">
            <label>快递公司：</label>
            <select name="PurchaseOrderShip[cargo_company_id][]" style="width: 200px">
                <?php
                foreach($shipList as $item):
                    if($v->cargo_company_id == $item):
                        ?>
                        <option value="<?= $item ?>" selected><?= $item ?></option>
                    <?php else: ?>
                        <option value="<?= $item ?>"><?= $item ?></option>
                    <?php endif; endforeach; ?>
            </select>
        </div>

        <div class="fg">
            <label>快递号：</label>
            <input type="text" name="PurchaseOrderShip[express_no][]" value="<?= $v->express_no ?>" style="width: 200px">
        </div>

    </div>

    <?php endforeach; ?>


    <?php endif; ?>


    <div class="fg">
        <label></label>
        <input type="submit" value="提交">
    </div>

    <?php ActiveForm::end() ?>


</div>
