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
<div class="row">
    <div class="col-md-6"><?= $form->field($model, 'supplier_settlement_code')->input('number',['max'=>20,'min'=>1,'readonly'=>$model->isNewRecord ? false :true])->label('结算方式编码') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'supplier_settlement_name')->input('text')->label('结算方式名称') ?></div>
</div>
<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', '创建') : Yii::t('app', '更新'), ['class' => $model->isNewRecord ? 'btn btn-success create' : 'btn btn-primary create']) ?>
</div>


<?php ActiveForm::end(); ?>
