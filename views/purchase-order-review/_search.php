<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\services\PurchaseOrderServices;
use app\services\BaseServices;

/* @var $this yii\web\View */
/* @var $model app\models\PurchaseOrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>



    <div class="col-md-1"><?= $form->field($model, 'pur_number') ?></div>
<!--    <div class="col-md-1">--><?php //$form->field($model, 'supplier_code')->dropDownList(PurchaseOrderServices::getSupplier(),['prompt' => 'please choose']) ?><!--</div>-->





    <div  class="form-group col-md-3" style="margin-top: 24px;">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('重置', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
