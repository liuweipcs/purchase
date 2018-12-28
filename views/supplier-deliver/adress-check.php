<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
use \yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="row">
    <?php $form = ActiveForm::begin(['enableAjaxValidation'=>false]); ?>
    <?=Html::hiddenInput('SupplierAdress[supplier_code]',$model->supplier_code)?>
    <div>
        <label>地址变化原因</label>
    <?= Html::textInput('SupplierAdress[change_reason]','',['class'=>'form-control'])?>
    </div>
    <div>
        <?= Html::submitButton(Yii::t('app', '提交'), ['class' => 'btn btn-success']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>



