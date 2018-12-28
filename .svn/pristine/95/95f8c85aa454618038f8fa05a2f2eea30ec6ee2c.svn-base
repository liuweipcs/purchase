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
$url = \yii\helpers\Url::to(['/supplier/search-supplier']);
/* @var $this yii\web\View */
/* @var $model app\models\Supplier */
/* @var $form yii\widgets\ActiveForm */

?>



<?php $form = ActiveForm::begin([
    ]
); ?>
<div class="row">
    <?= Html::hiddenInput('ProductSourceStatus[sku]',$skus)?>
    <div class="col-md-8"><?= $form->field($model, 'sourcing_status')->dropDownList([1=>'正常',2=>'停产',3=>'断货'],['required'=>true,'prompt'=>'请选择货源状态'])->label('货源状态') ?></div>
</div>

<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '提交') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>


<?php ActiveForm::end(); ?>



