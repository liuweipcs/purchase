<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;

/* @var $this yii\web\View */
/* @var $model app\models\TodayListSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]);
    $userList = array_merge(['请选择'], BaseServices::getEveryOne()); ?>
    <div class="col-md-1">
        <?= $form->field($model, 'developer_id')->dropDownList($userList) ?>
    </div>
    <div class="col-md-1">
        <?= $form->field($model, 'buyer_id')->dropDownList($userList) ?>
    </div>
    <div class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <a class="btn btn-default" href="index">重置</a>
    </div>
    <?php ActiveForm::end(); ?>
</div>
