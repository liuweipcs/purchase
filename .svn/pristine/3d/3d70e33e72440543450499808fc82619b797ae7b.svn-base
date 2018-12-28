<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Stockin */

$this->title = Yii::t('app', '拉取erp供应商');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', '供应商列表'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stockin-create">
    <?php $form = ActiveForm::begin(); ?>
    <div class="form-group">
    <div class="col-md-6">
        <label class="control-label"> 供应商名称</label>
        <input name="supplierName" class="form-control">
    </div>
    </div>
    <div class="form-group">
        <label class="control-label"></label>
        <?= Html::submitButton(Yii::t('app', '抓取'), ['class' =>  'btn btn-success create']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
