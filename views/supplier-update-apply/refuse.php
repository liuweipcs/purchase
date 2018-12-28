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
$url = \yii\helpers\Url::to(['refuse']);
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
    <?php foreach($model as $value){ ?>
        <input type="hidden" value="<?=$value->id?>" name="SupplierUpdateApply[<?=$value->id?>][id]">
    <div class="col-md-6">
        <label>sku</label>
        <input type="text" value="<?=$value->sku?>" aria-required="true" class="required form-control" readonly="readonly">
        </div>
        <div class="col-md-6">
            <label>审核不通过原因</label>
            <input type="text"  name="SupplierUpdateApply[<?=$value->id?>][refuse_reason]" class=" form-control" placeholder='必填项'>
        </div>

    <?php }?>
</div>

<div class="form-group">
    <?= Html::submitButton('提交', ['class' =>'btn btn-primary']) ?>
</div>


<?php ActiveForm::end(); ?>



