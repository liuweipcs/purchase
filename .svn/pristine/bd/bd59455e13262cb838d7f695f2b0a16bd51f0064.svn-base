<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\LogisticsImportSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="logistics-import-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="col-md-1"><?= $form->field($model, 'logistics_num')->textInput(['placeholder'=>'请输入物流单号']) ?></div>
    <!-- <div class="col-md-1"><?=$form->field($model, "push_status")->dropDownList(\app\services\SupplierServices::getSettlementMethod(),['class' => 'form-control pay_method','prompt' => '全部'])?></div> -->
    <div class="col-md-1"><?=$form->field($model, "push_status")->dropDownList([''=>'全部',0=>'未推送',1=>'推送成功',3=>'推送失败'])->label('是否推送到仓库')?></div>

<!--     <div class="form-group col-md-2" style="margin-top: 24px;">
  <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
  <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
</div>  -->
    
    <div class="form-group col-md-2" style="margin-top: 24px;">
      <?= Html::submitButton(Yii::t('app', '搜索'), ['class' => 'btn btn-primary']) ?>
      <?= Html::resetButton(Yii::t('app', '重置'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
