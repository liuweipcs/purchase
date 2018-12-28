<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\tabs\TabsX;
use kartik\file\FileInput;
use yii\helpers\Url;
use app\services\SupplierGoodsServices;
use app\services\SupplierServices;
use app\services\BaseServices;
use kartik\select2\Select2;
use yii\web\JsExpression;
$url = \yii\helpers\Url::to(['integratno']);
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>



<?php $form = ActiveForm::begin([
        //'id' => 'form-id',
        //'enableAjaxValidation' => true,
        //'validationUrl' => Url::toRoute(['validate-form']),
    ]
); ?>
<div class="row">

    <div class="col-md-4">
        <label>整合失败原因：</label>
        <?= Html::input('text','SupplierUpdateApply[fail_reason]')?>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton('提交', ['class' =>'btn btn-primary']) ?>
</div>


<?php ActiveForm::end(); ?>



